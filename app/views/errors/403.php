<?php
http_response_code(403);
$titulo = 'Acceso denegado';
require APP_ROOT . '/app/views/layouts/header.php';
?>
<div class="card" style="text-align:center; padding:3rem;">
    <h1 style="color:var(--rojo-mod7); font-size:4rem; margin:0;">403</h1>
    <h2 style="color:var(--vino); margin:.5rem 0 1rem;">Acceso denegado</h2>
    <p class="text-muted">No tienes permisos para ver esta sección.</p>
    <a href="/parrilla" class="btn btn-primary mt-2">Volver al inicio</a>
</div>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>