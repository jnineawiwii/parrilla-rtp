<?php
class Notif {
    /**
     * Crea una notificación.
     * $titulo = tipo corto ('mensaje', 'sistema', 'recordatorio_hoy', etc.)
     * $mensaje = texto completo que ve el usuario
     */
    public static function crear(int $uid, string $titulo, string $mensaje, ?int $idPub = null): void {
        Notificacion::crear($uid, $titulo, $mensaje, $idPub);
    }

    public static function noLeidas(int $uid): int {
        return Notificacion::noLeidas($uid);
    }
}