<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/database.php';

// Autoload simple de clases (sin composer, opcional)
spl_autoload_register(function ($clase) {
    foreach ([
        APP_ROOT . '/app/controllers/' . $clase . '.php',
        APP_ROOT . '/app/models/'      . $clase . '.php',
        APP_ROOT . '/app/helpers/'     . $clase . '.php',
    ] as $archivo) {
        if (file_exists($archivo)) { require $archivo; return; }
    }
});

// Detectar ruta y método
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$metodo = $_SERVER['REQUEST_METHOD'];

// Rutas
$rutas = [
    ['GET',  '#^/login$#',              ['AuthController','mostrarLogin']],
    ['POST', '#^/login$#',              ['AuthController','login']],
    ['POST', '#^/logout$#',             ['AuthController','logout']],

    ['GET',  '#^/$#',                   ['ParrillaController','index']],
    ['GET',  '#^/parrilla$#',           ['ParrillaController','index']],
    ['GET',  '#^/parrilla/crear$#',     ['ParrillaController','crear']],
    ['POST', '#^/parrilla/guardar$#',   ['ParrillaController','guardar']],
    ['GET',  '#^/parrilla/editar/(\d+)$#', ['ParrillaController','editar']],
    ['GET',  '#^/parrilla/ver/(\d+)$#',    ['ParrillaController','ver']],
    ['POST', '#^/parrilla/eliminar/(\d+)$#', ['ParrillaController','eliminar']],
    ['POST', '#^/parrilla/aprobar/(\d+)$#',  ['ParrillaController','aprobar']],
    ['POST', '#^/parrilla/rechazar/(\d+)$#', ['ParrillaController','rechazar']],
    
    ['GET',  '#^/usuarios$#',           ['UsuarioController','index']],
    ['GET',  '#^/usuarios/crear$#',     ['UsuarioController','crear']],
    ['GET',  '#^/usuarios/editar/(\d+)$#', ['UsuarioController','editar']],
    ['POST', '#^/usuarios/guardar$#',   ['UsuarioController','guardar']],
    ['POST', '#^/usuarios/eliminar/(\d+)$#', ['UsuarioController','eliminar']],

    ['GET',  '#^/mensajes$#',           ['MensajeController','index']],
    ['GET',  '#^/mensajes/ver/(\d+)$#', ['MensajeController','ver']],
    ['POST', '#^/mensajes/crear$#',     ['MensajeController','crear']],
    ['POST', '#^/mensajes/enviar$#',    ['MensajeController','enviar']],

    ['GET',  '#^/notificaciones$#',           ['NotificacionController','index']],
    ['GET',  '#^/notificaciones/abrir/(\d+)$#', ['NotificacionController','abrir']],
    ['POST', '#^/notificaciones/marcar-todas$#', ['NotificacionController','marcarTodas']],
    ['GET',  '#^/api/notificaciones$#',         ['NotificacionController','api']],

    ['GET',  '#^/graficas$#',           ['GraficaController','index']],
    ['GET',  '#^/api/graficas$#',       ['GraficaController','datos']],

    ['POST', '#^/api/ia/copy$#',        ['ApiController','generarCopy']],
];

// Buscar coincidencia
$manejada = false;
foreach ($rutas as [$m, $regex, $handler]) {
    if ($metodo !== $m) continue;
    if (preg_match($regex, $uri, $matches)) {
        $manejada = true;
        array_shift($matches); // quitar match completo
        [$clase, $fn] = $handler;
        (new $clase)->{$fn}(...$matches);
        break;
    }
}

if (!$manejada) {
    http_response_code(404);
    require APP_ROOT . '/app/views/errors/404.php';
}