<?php
$esEdicion = $usuario !== null;
$titulo = $esEdicion ? 'Editar usuario' : 'Nuevo usuario';
$v = function($k, $def='') use ($usuario) {
    return htmlspecialchars($usuario[$k] ?? $def);
};
$selRol = function($r) use ($usuario) {
    return (($usuario['rol'] ?? '') === $r) ? 'selected' : '';
};
?>

<div class="card">
    <h2 style="margin:0 0 1rem; color:var(--vino);">
        <?= $esEdicion ? '✏️ Editar usuario' : '➕ Nuevo usuario' ?>
    </h2>

    <form method="POST" action="/usuarios/guardar">
        <?= Csrf::campo() ?>
        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?= (int)$usuario['id_usuario'] ?>">
        <?php endif; ?>

        <div class="row">
            <div class="col" style="flex:1 1 200px;">
                <label class="form-label" for="num_credencial">Núm. credencial</label>
                <input type="text" id="num_credencial" name="num_credencial" class="form-control"
                       value="<?= $v('num_credencial') ?>"
                       placeholder="Se autogenera si lo dejas vacío">
            </div>
            <div class="col" style="flex:1 1 220px;">
                <label class="form-label" for="nombre_usuario">Nombre de usuario</label>
                <input type="text" id="nombre_usuario" name="nombre_usuario" class="form-control"
                       value="<?= $v('nombre_usuario') ?>">
            </div>
        </div>

        <div class="row mt-2">
            <div class="col" style="flex:1 1 320px;">
                <label class="form-label" for="correo_electronico">Correo electrónico *</label>
                <input type="email" id="correo_electronico" name="correo_electronico" class="form-control"
                       required value="<?= $v('correo_electronico') ?>">
            </div>
            <div class="col" style="flex:1 1 320px;">
                <label class="form-label" for="nombre_completo">Nombre completo *</label>
                <input type="text" id="nombre_completo" name="nombre_completo" class="form-control"
                       required value="<?= $v('nombre_completo') ?>">
            </div>
        </div>

        <div class="row mt-2">
            <div class="col" style="flex:1 1 200px;">
                <label class="form-label" for="rol">Rol *</label>
                <select id="rol" name="rol" class="form-select" required>
                    <option value="admin"  <?= $selRol('admin')  ?>>Admin</option>
                    <option value="editor" <?= $selRol('editor') ?>>Editor</option>
                    <option value="lector" <?= $selRol('lector') ?>>Lector</option>
                </select>
            </div>
            <div class="col" style="flex:1 1 300px;">
                <label class="form-label" for="password">
                    Contraseña <?= $esEdicion ? '<small class="text-muted">(dejar en blanco para no cambiar)</small>' : '*' ?>
                </label>
                <input type="password" id="password" name="password" class="form-control"
                       <?= $esEdicion ? '' : 'required' ?>
                       autocomplete="new-password">
            </div>
        </div>

        <div class="d-flex gap-1 mt-2">
            <button type="submit" class="btn btn-primary">💾 Guardar</button>
            <a href="/usuarios" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>