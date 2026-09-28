<?php $user = Auth::user(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="/css/paleta.css">
    <link rel="stylesheet" href="/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    <script src="/js/app.js" defer></script>
    <script src="/js/notificaciones.js" defer></script>
    <?php if (!empty($cargarGraficas)): ?>
        <script src="/js/graficas.js" defer></script>
    <?php endif; ?>
    <?php if (!empty($cargarMensajes)): ?>
        <script src="/js/mensajes.js" defer></script>
    <?php endif; ?>
</head>
<body>
<?php if ($user): ?>
<header class="topbar">
    <div class="brand">
        <a href="/parrilla"><?= APP_NAME ?></a>
    </div>
    <nav class="topnav">
        <a href="/parrilla">Parrilla</a>
        <?php if (Auth::can('metricas.ver')): ?>
            <a href="/graficas">Gráficas</a>
        <?php endif; ?>
        <?php if (Auth::can('usuarios.ver')): ?>
            <a href="/usuarios">Usuarios</a>
        <?php endif; ?>
        <a href="/mensajes">Mensajes</a>

        <div class="dropdown-notif">
            <a href="#" id="campanita">🔔 <span id="notif-count" class="badge">0</span></a>
            <div class="dropdown-content" id="notif-list">
                <p class="text-muted">Sin notificaciones</p>
            </div>
        </div>

        <span class="user-info">
            <?= htmlspecialchars($user['nombre']) ?>
            <em>(<?= $user['rol'] ?>)</em>
        </span>
        <form action="/logout" method="POST" style="display:inline">
            <?= Csrf::campo() ?>
            <button type="submit" class="btn-link">Salir</button>
        </form>
    </nav>
</header>
<?php endif; ?>
<main class="container">
<?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-ok" role="alert"><?= htmlspecialchars($_SESSION['flash_ok']) ?></div>
    <?php unset($_SESSION['flash_ok']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-err" role="alert"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>