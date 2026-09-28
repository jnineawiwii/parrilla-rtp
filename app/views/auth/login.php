<?php $titulo = 'Iniciar sesión'; ?>
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
        <h1>🚌 <?= htmlspecialchars(APP_NAME) ?></h1>

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

        <label class="form-label mt-1" for="password">Contraseña</label>
        <input type="password" id="password" name="password" class="form-control"
               required autocomplete="current-password"
               placeholder="••••••••">

        <button type="submit" class="btn btn-primary mt-2" style="width:100%; padding:.7rem;">
            Entrar
        </button>

        <p class="text-muted mt-2" style="text-align:center; font-size:.8rem;">
            Red de Transporte de Pasajeros — CDMX
        </p>
    </form>
</div>
</body>
</html>