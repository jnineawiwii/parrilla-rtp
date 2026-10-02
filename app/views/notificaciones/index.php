<?php
$titulo = 'Notificaciones';

function etiquetaNotif($tipo) {
    return [
        'recordatorio_hoy'    => ['📅 Publicación HOY',    '#E5074C'],
        'recordatorio_manana' => ['⏰ Publicación MAÑANA', '#F08217'],
        'mensaje'             => ['💬 Mensaje',            '#266CB4'],
        'sistema'             => ['⚙️ Sistema',            '#8F4889'],
    ][$tipo] ?? ['🔔 Notificación', '#A6184B'];
}
?>

<div class="card">
    <div class="d-flex" style="justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
        <h2 style="margin:0; color:var(--vino);">🔔 Notificaciones</h2>
        <form action="/notificaciones/marcar-todas" method="POST">
            <?= Csrf::campo() ?>
            <button type="submit" class="btn btn-sm btn-outline">Marcar todas como leídas</button>
        </form>
    </div>
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <?php if (empty($items)): ?>
        <div style="padding:3rem; text-align:center; color:var(--gris-mid);">
            <div style="font-size:3rem; opacity:.3;">🔔</div>
            <p>No tienes notificaciones.</p>
        </div>
    <?php else: ?>
        <?php foreach ($items as $n):
            $tipo  = $n['titulo'] ?? 'sistema';
            $leida = !empty($n['leida']);
            [$etiqueta, $color] = etiquetaNotif($tipo);
        ?>
            <a href="/notificaciones/abrir/<?= (int)$n['id'] ?>"
               class="notif-item <?= $leida ? 'leida' : 'no-leida' ?>">
                <span class="barra-lateral" style="background:<?= $color ?>;"></span>
                <div class="d-flex" style="justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap;">
                    <div style="flex:1; min-width:0;">
                        <div class="etiqueta" style="color:<?= $color ?>;">
                            <?= htmlspecialchars($etiqueta) ?>
                        </div>
                        <div class="mensaje"><?= htmlspecialchars($n['mensaje']) ?></div>
                        <?php if (!$leida): ?>
                            <span class="badge-nueva">NUEVA</span>
                        <?php endif; ?>
                    </div>
                    <div class="fecha">
                        <?= date('d/m/Y H:i', strtotime($n['created_at'])) ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>