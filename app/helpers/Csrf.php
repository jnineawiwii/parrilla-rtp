<?php
class Csrf {
    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function campo(): string {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    public static function validar(?string $token): void {
        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            die('Token CSRF inválido.');
        }
    }
}