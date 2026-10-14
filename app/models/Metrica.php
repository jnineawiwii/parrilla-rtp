<?php
/**
 * Modelo de métricas por red social.
 * Campos relevantes: reacciones, me_gusta, comentarios, interaccion, compartidos, vistas.
 */
class Metrica {

    /* =========================================================
     *  LECTURA BÁSICA
     * ========================================================= */

    public static function porPublicacion(int $idPub): array {
        $st = db()->prepare("
            SELECT * FROM gestion_metricas
            WHERE id_publicacion = ?
            ORDER BY red
        ");
        $st->execute([$idPub]);
        return $st->fetchAll();
    }

    public static function publicacionesConMetricas(int $anio): array {
        $st = db()->prepare("
            SELECT DISTINCT p.id_publicacion, p.titulo, p.fecha_publicacion
            FROM gestion_publicaciones p
            JOIN gestion_metricas m ON m.id_publicacion = p.id_publicacion
            WHERE EXTRACT(YEAR FROM p.fecha_publicacion) = :anio
            ORDER BY p.fecha_publicacion DESC, p.id_publicacion DESC
        ");
        $st->execute([':anio' => $anio]);
        return $st->fetchAll();
    }

    public static function detallePublicacion(int $idPub): array {
        $st = db()->prepare("
            SELECT p.id_publicacion, p.titulo, m.*
            FROM gestion_publicaciones p
            JOIN gestion_metricas m ON m.id_publicacion = p.id_publicacion
            WHERE p.id_publicacion = :id
            ORDER BY m.red
        ");
        $st->execute([':id' => $idPub]);
        return $st->fetchAll();
    }

    /* =========================================================
     *  ESCRITURA
     * ========================================================= */

    public static function guardar(int $idPub, string $red, array $d): void {
        $red = strtolower($red);
        $c   = fn(string $k) => (int)($d[$k] ?? 0);

        // En Facebook "reacciones" es el total; en otras redes usamos me_gusta.
        $reacciones = $red === 'facebook' ? $c('reacciones') : $c('me_gusta');

        $st = db()->prepare("
            INSERT INTO gestion_metricas
                (id_publicacion, red, reacciones, me_gusta,
                 comentarios, interaccion, compartidos, vistas, fecha_captura)
            VALUES
                (:pub, :red, :reac, :mg,
                 :com, :inter, :comp, :vis, CURRENT_DATE)
            ON CONFLICT (id_publicacion, red) DO UPDATE SET
                reacciones    = EXCLUDED.reacciones,
                me_gusta      = EXCLUDED.me_gusta,
                comentarios   = EXCLUDED.comentarios,
                interaccion   = EXCLUDED.interaccion,
                compartidos   = EXCLUDED.compartidos,
                vistas        = EXCLUDED.vistas,
                fecha_captura = CURRENT_DATE
        ");

        $st->execute([
            ':pub'   => $idPub,
            ':red'   => $red,
            ':reac'  => $reacciones,
            ':mg'    => $c('me_gusta'),
            ':com'   => $c('comentarios'),
            ':inter' => $c('interaccion'),
            ':comp'  => $c('compartidos'),
            ':vis'   => $c('vistas'),
        ]);
    }

    /* =========================================================
     *  FILTROS DINÁMICOS (para gráficas)
     * ========================================================= */

    /**
     * Construye la cláusula WHERE según los filtros.
     * $f puede traer: red, desde, hasta, tema_id.
     */
    private static function buildWhere(array $f, array &$params): string {
        $w = [];

        if (!empty($f['red']) && in_array($f['red'], Redes::todas(), true)) {
            $w[] = 'm.red = :red';
            $params[':red'] = $f['red'];
        }

        if (!empty($f['desde'])) {
            $w[] = 'p.fecha_publicacion >= :desde';
            $params[':desde'] = $f['desde'] . ' 00:00:00';
        }
        if (!empty($f['hasta'])) {
            $w[] = 'p.fecha_publicacion <= :hasta';
            $params[':hasta'] = $f['hasta'] . ' 23:59:59';
        }

        if (!empty($f['tema_id'])) {
            $w[] = 'p.id_tema = :tema';
            $params[':tema'] = (int)$f['tema_id'];
        }

        return $w ? ('WHERE ' . implode(' AND ', $w)) : '';
    }

    /** Serie mensual (12 meses). Ideal cuando el periodo es "anio". */
    public static function serieMensualFiltrada(array $f): array {
        $params = [];
        $where  = self::buildWhere($f, $params);

        $sql = "
            SELECT
                EXTRACT(MONTH FROM p.fecha_publicacion)::int AS mes,
                m.red,
                SUM(m.interaccion)  AS interaccion,
                SUM(m.reacciones)   AS reacciones,
                SUM(m.me_gusta)     AS me_gusta,
                SUM(m.comentarios)  AS comentarios,
                SUM(m.compartidos)  AS compartidos,
                SUM(m.vistas)       AS vistas
            FROM gestion_metricas m
            JOIN gestion_publicaciones p ON p.id_publicacion = m.id_publicacion
            $where
            GROUP BY mes, m.red
            ORDER BY mes, m.red
        ";
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** Serie diaria. Ideal para 15 días, día o rango. */
    public static function serieDiariaFiltrada(array $f): array {
        $params = [];
        $where  = self::buildWhere($f, $params);

        $sql = "
            SELECT
                TO_CHAR(p.fecha_publicacion, 'YYYY-MM-DD') AS dia,
                m.red,
                SUM(m.interaccion)  AS interaccion,
                SUM(m.reacciones)   AS reacciones,
                SUM(m.me_gusta)     AS me_gusta,
                SUM(m.comentarios)  AS comentarios,
                SUM(m.compartidos)  AS compartidos,
                SUM(m.vistas)       AS vistas
            FROM gestion_metricas m
            JOIN gestion_publicaciones p ON p.id_publicacion = m.id_publicacion
            $where
            GROUP BY dia, m.red
            ORDER BY dia, m.red
        ";
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** Resumen total por red en el rango (para pastel y totales). */
    public static function resumenPorRedFiltrado(array $f): array {
        $params = [];
        $where  = self::buildWhere($f, $params);

        $sql = "
            SELECT
                m.red,
                SUM(m.reacciones)  AS reacciones,
                SUM(m.me_gusta)    AS me_gusta,
                SUM(m.comentarios) AS comentarios,
                SUM(m.interaccion) AS interaccion,
                SUM(m.compartidos) AS compartidos,
                SUM(m.vistas)      AS vistas
            FROM gestion_metricas m
            JOIN gestion_publicaciones p ON p.id_publicacion = m.id_publicacion
            $where
            GROUP BY m.red
            ORDER BY m.red
        ";
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** Top N publicaciones por interacción total. */
    public static function topPublicaciones(array $f, int $limite = 10): array {
        $params = [];
        $where  = self::buildWhere($f, $params);
        $limite = max(1, min(50, $limite));

        $sql = "
            SELECT
                p.id_publicacion,
                p.titulo,
                p.fecha_publicacion,
                SUM(m.interaccion) AS total
            FROM gestion_metricas m
            JOIN gestion_publicaciones p ON p.id_publicacion = m.id_publicacion
            $where
            GROUP BY p.id_publicacion, p.titulo, p.fecha_publicacion
            ORDER BY total DESC
            LIMIT $limite
        ";
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** Distribución de publicaciones por campaña. */
    public static function porCampana(array $f): array {
        $params = [];
        $where  = self::buildWhere($f, $params);

        // ⚠️ Ajusta nombre de tabla/columna si tu esquema difiere
        $sql = "
            SELECT
                COALESCE(c.nombre_campana, 'Sin campaña') AS campana,
                COUNT(DISTINCT p.id_publicacion)          AS publicaciones
            FROM gestion_publicaciones p
            LEFT JOIN gestion_campanas c ON c.id_campana = p.id_campana
            $where
            GROUP BY campana
            ORDER BY publicaciones DESC
        ";
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
}