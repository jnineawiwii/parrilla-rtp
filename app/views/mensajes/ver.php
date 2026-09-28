<?php
$titulo = 'Mensaje';
$uid = (int)Auth::user()['id'];
$esMio = ((int)$mensaje['id_remitente'] === $uid);
$otroId = $esMio ? $mensaje['id_destinatario'] : $mensaje['id_remitente'];
$otroNombre = $esMio ? $mensaje['destinatario_nombre'] : $mensaje['remitente_nombre'];
?>

<div class="card">
    <div class="d-flex gap-1">
        <a href="/mensajes" class="btn btn-sm btn-outline">← Volver a mensajes</a>
        <a href="/mensajes?con=<?= (int)$otroId ?>" class="btn btn-sm btn-primary">
            💬 Ir a conversación con <?= htmlspecialchars($otroNombre) ?>
        </a>
    </div>

    <h2 style="margin:.8rem 0 0; color:var(--vino);">
        ✉️ <?= htmlspecialchars($mensaje['asunto'] ?? '(Sin asunto)') ?>
    </h2>
    <p class="text-muted" style="font-size:.85rem; margin-top:.3rem;">
        De: <strong><?= htmlspecialchars($mensaje['remitente_nombre']) ?></strong>
        &lt;<?= htmlspecialchars($mensaje['remitente_correo']) ?>&gt;<br>
        Para: <strong><?= htmlspecialchars($mensaje['destinatario_nombre']) ?></strong>
        &lt;<?= htmlspecialchars($mensaje['destinatario_correo']) ?>&gt;
    </p>

    <div style="white-space:pre-wrap; background:#fafafa; padding:1rem; border-radius:6px; margin-top:1rem;">
        <?= htmlspecialchars($mensaje['contenido']) ?>
    </div>

    <?php if (!empty($mensaje['adjunto_ruta'])): ?>
        <div style="margin-top:1rem; padding:1rem; border:1px dashed #ccc; border-radius:6px; background:#fffdf5;">
            <strong>📎 Archivo adjunto:</strong>
            <a href="<?= htmlspecialchars($mensaje['adjunto_ruta']) ?>"
               download="<?= htmlspecialchars($mensaje['adjunto_nombre']) ?>"
               target="_blank" rel="noopener"
               style="color:var(--vino); text-decoration:none; margin-left:.4rem;">
                <?= htmlspecialchars($mensaje['adjunto_nombre']) ?>
            </a>
            <span class="text-muted" style="font-size:.8rem; margin-left:.4rem;">
                (<?= number_format((int)$mensaje['adjunto_tamanio'] / 1024, 1) ?> KB)
            </span>
        </div>
    <?php endif; ?>
</div>