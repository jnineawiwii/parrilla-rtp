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

    public static function homeUrl(): string {
        return self::can('metricas.ver') && !self::can('parrilla.ver')
            ? '/graficas'
            : '/parrilla';
    }

    public static function can(string $accion): bool {
        $rol = self::role();
        if (!$rol) return false;
        $matriz = [
            'parrilla.ver'         => ['admin','editor'],
            'parrilla.crear'       => ['admin','editor'],
            'parrilla.editar'      => ['admin','editor'],
            'parrilla.borrar'      => ['admin'],
            'metricas.ver'         => ['admin','lector'],
            'metricas.editar'      => ['admin','editor'],
            'usuarios.ver'         => ['admin'],
            'usuarios.editar'      => ['admin'],
            'notificaciones.ver'   => ['admin','editor'],
            'mensajes.usar'        => ['admin','editor'],
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
            if (self::can('metricas.ver') && !self::can('parrilla.ver')) {
                Response::redirect('/graficas');
            }
            http_response_code(403);
            require APP_ROOT . '/app/views/errors/403.php';
            exit;
        }
    }
}