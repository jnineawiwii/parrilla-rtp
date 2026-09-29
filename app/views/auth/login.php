<?php $titulo = 'Iniciar sesión'; ?>
<?php $logoDisponible = is_file(APP_ROOT . '/public/logo-rtp.png'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo) ?> — <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="/css/paleta.css">
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<div class="login-wrap">
    <form method="POST" action="/login" class="login-card">
        <header class="login-header">
            <div class="login-logo" aria-label="Logo RTP">
                <?php if ($logoDisponible): ?>
                    <img src="/logo-rtp.png" alt="Logo RTP">
                <?php else: ?>
                    <span aria-hidden="true">rtp</span>
                <?php endif; ?>
            </div>
            <h1>RTP</h1>
            <p class="login-subtitle">Sistema de Gestión de Contenidos</p>
            <p class="login-agency">Red de Transporte de Pasajeros CDMX</p>
        </header>

        <section class="login-body">
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-err" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?= Csrf::campo() ?>

            <label class="form-label" for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" class="form-control"
                   required autofocus autocomplete="username"
                   placeholder="usuario@rtp.cdmx.gob.mx">

            <label class="form-label login-password-label" for="password">Contraseña</label>
            <input type="password" id="password" name="password" class="form-control"
                   required autocomplete="current-password"
                   placeholder="••••••••">

            <button type="submit" class="btn btn-primary login-submit">
                <span aria-hidden="true">&#10140;</span> Iniciar Sesión
            </button>

            <p class="login-restricted">Acceso restringido al personal autorizado</p>
        </section>

        <footer class="login-footer">
            <strong>RTP</strong> Sistema Seguro <span aria-hidden="true">|</span>
            <?= date('h:i a') ?> <span aria-hidden="true">|</span> v.2.0
        </footer>
    </form>
</div>
</body>
</html>