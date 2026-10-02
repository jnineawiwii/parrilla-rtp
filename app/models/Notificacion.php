<?php
class Notificacion {

    /**
     * Devuelve las notificaciones visibles de un usuario.
     * Excluye las notificaciones de recordatorio cuya publicación
     * ya pasó de fecha o ya está publicada/cancelada.
     */
    public static function deUsuario(int $uid, int $limite = 30): array {
        $st = db()->prepare("
            SELECT
                n.id_notificacion         AS id,
                n.id_publicacion,
                n.id_usuario,
                n.mensaje,
                n.tipo_notificacion       AS titulo,
                n.leido                   AS leida,
                n.creado_en               AS created_at
            FROM gestion_notificaciones n
            LEFT JOIN gestion_publicaciones p
                   ON p.id_publicacion = n.id_publicacion
            WHERE n.id_usuario = ?
              AND (
                    -- Notificaciones que no son recordatorio: siempre visibles
                    n.tipo_notificacion NOT IN ('recordatorio_hoy','recordatorio_manana')
                    OR (
                        -- Recordatorios: solo si la publicación aún no ha pasado
                        n.tipo_notificacion IN ('recordatorio_hoy','recordatorio_manana')
                        AND p.fecha_publicacion::date >= CURRENT_DATE
                        AND p.estado NOT IN ('Publicado','Cancelado')
                    )
                  )
            ORDER BY n.creado_en DESC
            LIMIT ?
        ");
        $st->bindValue(1, $uid, PDO::PARAM_INT);
        $st->bindValue(2, $limite, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function marcarLeida(int $id, int $uid): void {
        db()->prepare("
            UPDATE gestion_notificaciones
            SET leido = TRUE
            WHERE id_notificacion = ? AND id_usuario = ?
        ")->execute([$id, $uid]);
    }

    public static function marcarTodasLeidas(int $uid): void {
        db()->prepare("
            UPDATE gestion_notificaciones
            SET leido = TRUE
            WHERE id_usuario = ?
        ")->execute([$uid]);
    }

    public static function crear(int $uid, string $tipo, string $mensaje, ?int $idPub = null): void {
        db()->prepare("
            INSERT INTO gestion_notificaciones
                (id_usuario, id_publicacion, mensaje, tipo_notificacion)
            VALUES (?, ?, ?, ?)
        ")->execute([$uid, $idPub, $mensaje, $tipo]);
    }

    /**
     * Cuenta SOLO las notificaciones visibles (no vencidas).
     */
    public static function noLeidas(int $uid): int {
        $st = db()->prepare("
            SELECT COUNT(*)
            FROM gestion_notificaciones n
            LEFT JOIN gestion_publicaciones p
                   ON p.id_publicacion = n.id_publicacion
            WHERE n.id_usuario = ?
              AND n.leido = FALSE
              AND (
                    n.tipo_notificacion NOT IN ('recordatorio_hoy','recordatorio_manana')
                    OR (
                        n.tipo_notificacion IN ('recordatorio_hoy','recordatorio_manana')
                        AND p.fecha_publicacion::date >= CURRENT_DATE
                        AND p.estado NOT IN ('Publicado','Cancelado')
                    )
                  )
        ");
        $st->execute([$uid]);
        return (int)$st->fetchColumn();
    }
}