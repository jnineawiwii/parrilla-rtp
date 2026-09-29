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
                  coment_positivos, coment_negativos, coment_neutros,
                  fb_me_gusta, fb_me_encanta, fb_me_entristece,
                  fb_me_sorprende, fb_me_enoja, fb_me_importa)
            VALUES
                (:pub, :red, :alc, :imp, :mg, :com, :comp, :inter, :rep,
                  :pos, :neg, :neu, :fb_like, :fb_love, :fb_sad,
                  :fb_wow, :fb_angry, :fb_care)
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
                fb_me_gusta      = EXCLUDED.fb_me_gusta,
                fb_me_encanta    = EXCLUDED.fb_me_encanta,
                fb_me_entristece = EXCLUDED.fb_me_entristece,
                fb_me_sorprende  = EXCLUDED.fb_me_sorprende,
                fb_me_enoja      = EXCLUDED.fb_me_enoja,
                fb_me_importa    = EXCLUDED.fb_me_importa,
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
            ':fb_like'  => $red === 'facebook' ? (int)($d['fb_me_gusta'] ?? 0) : 0,
            ':fb_love'  => $red === 'facebook' ? (int)($d['fb_me_encanta'] ?? 0) : 0,
            ':fb_sad'   => $red === 'facebook' ? (int)($d['fb_me_entristece'] ?? 0) : 0,
            ':fb_wow'   => $red === 'facebook' ? (int)($d['fb_me_sorprende'] ?? 0) : 0,
            ':fb_angry' => $red === 'facebook' ? (int)($d['fb_me_enoja'] ?? 0) : 0,
            ':fb_care'  => $red === 'facebook' ? (int)($d['fb_me_importa'] ?? 0) : 0,
        ]);
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