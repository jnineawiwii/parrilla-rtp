<?php $titulo = 'Notificaciones'; ?>

<div class="card">
    <div class="d-flex" style="justify-content:space-between; align-items:center;">
        <h2 style="margin:0; color:var(--vino);">🔔 Notificaciones</h2>
        <form action="/notificaciones/marcar-todas" method="POST">
            <?= Csrf::campo() ?>
            <button type="submit" class="btn btn-sm btn-outline">Marcar todas como leídas</button>
        </form>
    </div>
</div>

<div class="card" style="padding:0;">
    <?php if (empty($items)): ?>
        <p class="text-muted" style="text-align:center; padding:2rem;">
            No tienes notificaciones.
        </p>
    <?php else: ?>
        <?php foreach ($items as $n): ?>
            <a href="/notificaciones/abrir/<?= $n['id'] ?>"
               style="display:block; text-decoration:none; color:inherit;
                      padding:1rem; border-bottom:1px solid #eee;
                      <?= $n['leido'] ? '' : 'background:#fff7e0;' ?>">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
                    <div>
                        <div style="font-weight:600; <?= $n['leido'] ? 'color:var(--gris-mid);' : 'color:var(--vino);' ?>">
                            <?= htmlspecialchars(ucfirst($n['tipo_notificacion'] ?? 'Notificación')) ?>
                        </div>
                        <div class="text-muted" style="font-size:.9rem; margin-top:.2rem;">
                            <?= htmlspecialchars($n['mensaje']) ?>
                        </div>
                    </div>
                    <div class="text-muted" style="font-size:.75rem; white-space:nowrap;">
                        <?= date('d/m/Y H:i', strtotime($n['created_at'])) ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>