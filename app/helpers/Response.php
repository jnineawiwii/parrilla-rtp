<?php
class Response {
    public static function json($data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    public static function view(string $ruta, array $vars = []): void {
        extract($vars);
        $archivo = APP_ROOT . "/app/views/$ruta.php";
        if (!file_exists($archivo)) {
            http_response_code(500);
            die("Vista no encontrada: $ruta");
        }
        require APP_ROOT . '/app/views/layouts/header.php';
        require $archivo;
        require APP_ROOT . '/app/views/layouts/footer.php';
    }
}