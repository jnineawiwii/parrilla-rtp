<?php
class Metrica {

    public static function porPublicacion(int $idPub): array {
        $st = db()->prepare("
            SELECT * FROM gestion_metricas
            WHERE id_publicacion = ?
            ORDER BY red
        ");
        $st->execute([$idPub]);
        return $st->fetchAll();
    }

    public static function guardar(int $idPub, string $red, array $d): void {
        $st = db()->prepare("
            INSERT INTO gestion_metricas
                (id_publicacion, red, alcance, impresiones, me_gusta, comentarios,
                 compartidos, interaccion, reproducciones,
                 coment_positivos, coment_negativos, coment_neutros)
            VALUES
                (:pub, :red, :alc, :imp, :mg, :com, :comp, :inter, :rep,
                 :pos, :neg, :neu)
            ON CONFLICT (id_publicacion, red) DO UPDATE SET
                alcance          = EXCLUDED.alcance,
                impresiones      = EXCLUDED.impresiones,
                me_gusta         = EXCLUDED.me_gusta,
                comentarios      = EXCLUDED.comentarios,
                compartidos      = EXCLUDED.compartidos,
                interaccion      = EXCLUDED.interaccion,
                reproducciones   = EXCLUDED.reproducciones,
                coment_positivos = EXCLUDED.coment_positivos,
                coment_negativos = EXCLUDED.coment_negativos,
                coment_neutros   = EXCLUDED.coment_neutros,
                fecha_captura    = CURRENT_DATE
        ");
        $st->execute([
            ':pub'   => $idPub,
            ':red'   => $red,
            ':alc'   => (int)($d['alcance']          ?? 0),
            ':imp'   => (int)($d['impresiones']      ?? 0),
            ':mg'    => (int)($d['me_gusta']         ?? 0),
            ':com'   => (int)($d['comentarios']      ?? 0),
            ':comp'  => (int)($d['compartidos']      ?? 0),
            ':inter' => (int)($d['interaccion']      ?? 0),
            ':rep'   => (int)($d['reproducciones']   ?? 0),
            ':pos'   => (int)($d['coment_positivos'] ?? 0),
            ':neg'   => (int)($d['coment_negativos'] ?? 0),
            ':neu'   => (int)($d['coment_neutros']   ?? 0),
        ]);
    }

    /** Suma total por red en un año (para gráfica de pastel y edificio) */
    public static function resumenPorRed(int $anio): array {
        $st = db()->prepare("
            SELECT
                m.red,
                SUM(m.alcance)           AS alcance,
                SUM(m.impresiones)       AS impresiones,
                SUM(m.me_gusta)          AS me_gusta,
                SUM(m.comentarios)       AS comentarios,
                SUM(m.compartidos)       AS compartidos,
                SUM(m.interaccion)       AS interaccion,
                SUM(m.coment_positivos)  AS positivos,
                SUM(m.coment_negativos)  AS negativos
            FROM gestion_metricas m
            JOIN gestion_publicaciones p ON p.id_publicacion = m.id_publicacion
            WHERE EXTRACT(YEAR FROM p.fecha_publicacion) = :anio
            GROUP BY m.red
            ORDER BY m.red
        ");
        $st->execute([':anio' => $anio]);
        return $st->fetchAll();
    }

    /** Serie mensual por red (para barras de edificio apiladas) */
    public static function serieMensual(int $anio): array {
        $st = db()->prepare("
            SELECT
                EXTRACT(MONTH FROM p.fecha_publicacion)::int AS mes,
                m.red,
                SUM(m.interaccion) AS interaccion,
                SUM(m.alcance)     AS alcance
            FROM gestion_metricas m
            JOIN gestion_publicaciones p ON p.id_publicacion = m.id_publicacion
            WHERE EXTRACT(YEAR FROM p.fecha_publicacion) = :anio
            GROUP BY mes, m.red
            ORDER BY mes, m.red
        ");
        $st->execute([':anio' => $anio]);
        return $st->fetchAll();
    }
}