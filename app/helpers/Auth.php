<?php
class Auth {
    public static function login(array $u): void {
        $_SESSION['usuario_id'] = (int)$u['id'];
        $_SESSION['usuario']    = [
            'id'     => (int)$u['id'],
            'nombre' => $u['nombre'],
            'email'  => $u['email'],
            'rol'    => $u['rol'],
        ];
    }

    public static function logout(): void {
        session_destroy();
    }

    public static function check(): bool {
        return isset($_SESSION['usuario_id']);
    }

    public static function user(): ?array {
        return $_SESSION['usuario'] ?? null;
    }

    public static function role(): ?string {
        return self::user()['rol'] ?? null;
    }

    public static function can(string $accion): bool {
        $rol = self::role();
        if (!$rol) return false;
        $matriz = [
            'parrilla.ver'         => ['admin','editor','lector'],
            'parrilla.crear'       => ['admin','editor'],
            'parrilla.editar'      => ['admin','editor'],
            'parrilla.borrar'      => ['admin'],
            'metricas.ver'         => ['admin'],
            'metricas.editar'      => ['admin','editor'],
            'usuarios.ver'         => ['admin'],
            'usuarios.editar'      => ['admin'],
            'notificaciones.ver'   => ['admin','editor','lector'],
            'mensajes.usar'        => ['admin','editor','lector'],
            'parrilla.aprobar'     => ['admin'],
            'parrilla.rechazar'    => ['admin'],
        ];
        return in_array($rol, $matriz[$accion] ?? [], true);
    }

    public static function require(string $accion): void {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
        if (!self::can($accion)) {
            http_response_code(403);
            require APP_ROOT . '/app/views/errors/403.php';
            exit;
        }
    }
}