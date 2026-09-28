<?php
http_response_code(404);
$titulo = 'Página no encontrada';
// Si estamos logueados mostramos header, si no, mostramos uno simple
if (class_exists('Auth') && Auth::check()) {
    require APP_ROOT . '/app/views/layouts/header.php';
    $conLayout = true;
} else {
    $conLayout = false;
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>404</title>';
    echo '<link rel="stylesheet" href="/css/paleta.css"><link rel="stylesheet" href="/css/estilos.css">';
    echo '</head><body>';
}
?>
<div class="card" style="text-align:center; padding:3rem; max-width:520px; margin:3rem auto;">
    <h1 style="color:var(--rojo-mod7); font-size:4rem; margin:0;">404</h1>
    <h2 style="color:var(--vino); margin:.5rem 0 1rem;">Página no encontrada</h2>
    <p class="text-muted">La página que buscas no existe o fue movida.</p>
    <a href="/parrilla" class="btn btn-primary mt-2">Volver al inicio</a>
</div>
<?php
if ($conLayout) {
    require APP_ROOT . '/app/views/layouts/footer.php';
} else {
    echo '</body></html>';
}