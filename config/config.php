<?php
/**
 * Configuración general del sistema
 */

// Cargar variables de entorno (.env)
function cargarEnv(string $ruta): void {
    if (!file_exists($ruta)) return;
    foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        if (str_starts_with(trim($linea), '#')) continue;
        if (!str_contains($linea, '=')) continue;
        [$k, $v] = explode('=', $linea, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if (!getenv($k)) putenv("$k=$v");
        $_ENV[$k] = $v;
    }
}
cargarEnv(__DIR__ . '/../.env');

// Zona horaria y errores
date_default_timezone_set('America/Mexico_City');
if (getenv('APP_DEBUG') === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Constantes
define('APP_NAME', getenv('APP_NAME') ?: 'Parrilla RTP');
define('APP_URL',  getenv('APP_URL')  ?: 'http://localhost:8000');
define('APP_ROOT', dirname(__DIR__));

// Sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}