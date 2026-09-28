<?php
class Notif {
    public static function crear(int $uid, string $titulo, string $mensaje, ?int $idPub = null): void {
        $st = db()->prepare("
            INSERT INTO gestion_notificaciones
                (id_usuario, id_publicacion, mensaje, tipo_notificacion)
            VALUES (?, ?, ?, ?)
        ");
        $st->execute([$uid, $idPub, $mensaje, $titulo]);
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