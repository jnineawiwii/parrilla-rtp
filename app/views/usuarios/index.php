<?php $titulo = 'Usuarios'; ?>

<div class="card">
    <div class="d-flex" style="justify-content:space-between; align-items:center;">
        <h2 style="margin:0; color:var(--vino);">👥 Usuarios</h2>
        <a href="/usuarios/crear" class="btn btn-primary">➕ Nuevo usuario</a>
    </div>
</div>

<div class="card" style="padding:0; overflow-x:auto;">
    <?php if (empty($usuarios)): ?>
        <p class="text-muted" style="padding:2rem; text-align:center;">No hay usuarios registrados.</p>
    <?php else: ?>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Credencial</th>
                    <th>Usuario</th>
                    <th>Nombre completo</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['num_credencial'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($u['nombre_usuario']) ?></td>
                        <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                        <td><?= htmlspecialchars($u['correo_electronico']) ?></td>
                        <td>
                            <?php
                            $colorRol = [
                                'admin'  => 'var(--rojo-mod7)',
                                'editor' => 'var(--azul)',
                                'lector' => 'var(--gris-mid)',
                            ][$u['rol']] ?? 'var(--gris-mid)';
                            ?>
                            <span class="chip" style="background:<?= $colorRol ?>; color:#fff;">
                                <?= htmlspecialchars($u['rol']) ?>
                            </span>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a href="/usuarios/editar/<?= $u['id_usuario'] ?>" class="btn btn-sm btn-outline">✏️ Editar</a>
                            <?php if ((int)$u['id_usuario'] !== (int)Auth::user()['id']): ?>
                                <form action="/usuarios/eliminar/<?= $u['id_usuario'] ?>" method="POST"
                                      style="display:inline"
                                      onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars($u['nombre_completo']) ?>?');">
                                    <?= Csrf::campo() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">🗑</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>