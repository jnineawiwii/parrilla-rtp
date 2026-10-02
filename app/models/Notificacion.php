<?php
class Notificacion {
    /**
     * Devuelve las notificaciones de un usuario con nombres
     * de columnas "amigables" para las vistas (id, titulo, mensaje, leida, created_at)
     */
    public static function deUsuario(int $uid, int $limite = 30): array {
        $st = db()->prepare("
            SELECT
                id_notificacion         AS id,
                id_publicacion,
                id_usuario,
                mensaje,
                tipo_notificacion       AS titulo,
                leido                   AS leida,
                creado_en               AS created_at
            FROM gestion_notificaciones
            WHERE id_usuario = ?
            ORDER BY creado_en DESC
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

    public static function noLeidas(int $uid): int {
        $st = db()->prepare("
            SELECT COUNT(*) FROM gestion_notificaciones
            WHERE id_usuario = ? AND leido = FALSE
        ");
        $st->execute([$uid]);
        return (int)$st->fetchColumn();
    }
}