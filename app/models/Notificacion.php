<?php
class Notificacion {
    public static function deUsuario(int $uid, int $limite = 30): array {
        $st = db()->prepare("
            SELECT id_notificacion AS id,
                   id_publicacion,
                   id_usuario,
                   mensaje,
                   tipo_notificacion,
                   leido,
                   creado_en AS created_at
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
        $st = db()->prepare("
            UPDATE gestion_notificaciones
            SET leido = TRUE
            WHERE id_notificacion = ? AND id_usuario = ?
        ");
        $st->execute([$id, $uid]);
    }

    public static function marcarTodasLeidas(int $uid): void {
        db()->prepare("
            UPDATE gestion_notificaciones SET leido = TRUE WHERE id_usuario = ?
        ")->execute([$uid]);
    }

    public static function crear(int $uid, string $tipo, string $mensaje, ?int $idPub = null): void {
        db()->prepare("
            INSERT INTO gestion_notificaciones
                (id_usuario, id_publicacion, mensaje, tipo_notificacion)
            VALUES (?, ?, ?, ?)
        ")->execute([$uid, $idPub, $mensaje, $tipo]);
    }
}