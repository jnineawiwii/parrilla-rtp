<?php
$titulo = 'Detalle: ' . $registro['titulo'];
$r = $registro;
?>

<div class="card">
    <div class="d-flex" style="justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:.5rem;">
        <div>
            <h2 style="margin:0 0 .3rem; color:var(--vino);"><?= htmlspecialchars($r['titulo']) ?></h2>
            <div class="text-muted" style="font-size:.85rem;">
                📅 <?= $r['fecha_publicacion']
                        ? date('d/m/Y H:i', strtotime($r['fecha_publicacion']))
                        : 'Sin fecha' ?>
                <?php if ($r['dia_semana']): ?> · <?= htmlspecialchars(ucfirst($r['dia_semana'])) ?><?php endif; ?>
            </div>
        </div>
      <div class="d-flex gap-1" style="flex-wrap:wrap;">
    <a href="/parrilla" class="btn btn-sm btn-outline">← Regresar</a>

    <?php if (Auth::can('parrilla.editar')): ?>
        <a href="/parrilla/editar/<?= (int)$r['id_publicacion'] ?>" class="btn btn-sm btn-primary">✏️ Editar</a>
    <?php endif; ?>

    <?php if (Auth::can('parrilla.aprobar') && $r['estado'] !== 'Aprobado' && $r['estado'] !== 'Publicado'): ?>
        <form action="/parrilla/aprobar/<?= (int)$r['id_publicacion'] ?>" method="POST" style="display:inline">
            <?= Csrf::campo() ?>
            <button type="submit" class="btn btn-sm"
                    style="background:var(--verde); color:#fff;">
                ✅ Aprobar
            </button>
        </form>
    <?php endif; ?>

    <?php if (Auth::can('parrilla.rechazar') && $r['estado'] !== 'Cancelado'): ?>
        <button type="button" class="btn btn-sm"
                style="background:var(--rojo-mod7); color:#fff;"
                onclick="document.getElementById('modalRechazo').style.display='flex';">
            ❌ Rechazar
        </button>
    <?php endif; ?>

    <?php if (Auth::can('parrilla.borrar')): ?>
        <form action="/parrilla/eliminar/<?= (int)$r['id_publicacion'] ?>" method="POST"
              onsubmit="return confirm('¿Eliminar esta publicación?');"
              style="display:inline">
            <?= Csrf::campo() ?>
            <button type="submit" class="btn btn-sm btn-danger">🗑 Eliminar</button>
        </form>
    <?php endif; ?>
</div>

<?php if (Auth::can('parrilla.rechazar')): ?>
<!-- Modal para motivo de rechazo -->
<div id="modalRechazo"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5);
            z-index:999; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:10px; padding:1.5rem; max-width:480px; width:100%;">
        <h3 style="margin:0 0 .8rem; color:var(--rojo-mod7);">❌ Rechazar publicación</h3>
        <form action="/parrilla/rechazar/<?= (int)$r['id_publicacion'] ?>" method="POST">
            <?= Csrf::campo() ?>
            <label class="form-label" for="motivo">Motivo del rechazo</label>
            <textarea id="motivo" name="motivo" class="form-control" rows="3"
                      placeholder="Ej: Faltan datos, la imagen no cumple los lineamientos…"></textarea>
            <div class="d-flex gap-1 mt-2" style="justify-content:flex-end;">
                <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('modalRechazo').style.display='none';">
                    Cancelar
                </button>
                <button type="submit" class="btn"
                        style="background:var(--rojo-mod7); color:#fff;">
                    Confirmar rechazo
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
    </div>
</div>

<div class="card">
    <h3 style="margin:0 0 .8rem; font-size:1rem; color:var(--vino);">📌 Clasificación</h3>
    <table style="width:100%; font-size:.9rem;">
        <tr>
            <td style="padding:.3rem; color:var(--gris-mid); width:200px;">Campaña</td>
            <td style="padding:.3rem;">
                <?php if ($r['campana_nombre']): ?>
                    <span class="chip" style="background:<?= htmlspecialchars($r['campana_color'] ?? '#55585A') ?>; color:#fff;">
                        <?= htmlspecialchars(str_replace('_',' ', $r['campana_nombre'])) ?>
                    </span>
                <?php else: ?>—<?php endif; ?>
            </td>
        </tr>
        <tr>
            <td style="padding:.3rem; color:var(--gris-mid);">Tema</td>
            <td style="padding:.3rem;"><?= htmlspecialchars($r['tema_nombre'] ?? '—') ?></td>
        </tr>
        <tr>
            <td style="padding:.3rem; color:var(--gris-mid);">Subtema</td>
            <td style="padding:.3rem;"><?= htmlspecialchars($r['subtema_nombre'] ?? '—') ?></td>
        </tr>
        <tr>
            <td style="padding:.3rem; color:var(--gris-mid);">Formato</td>
            <td style="padding:.3rem;"><?= htmlspecialchars($r['formato_nombre'] ?? '—') ?></td>
        </tr>
        <tr>
            <td style="padding:.3rem; color:var(--gris-mid);">Estado</td>
            <td style="padding:.3rem;"><strong><?= htmlspecialchars($r['estado']) ?></strong></td>
        </tr>
    </table>
</div>

<?php if ($r['descripcion'] || $r['texto_publicitario'] || $r['notas']): ?>
<div class="card">
    <h3 style="margin:0 0 .8rem; font-size:1rem; color:var(--vino);">📝 Contenido</h3>
    <?php if ($r['descripcion']): ?>
        <p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($r['descripcion'])) ?></p>
    <?php endif; ?>
    <?php if ($r['texto_publicitario']): ?>
        <p style="margin-top:.8rem;"><strong>Copy:</strong></p>
        <p style="white-space:pre-wrap; background:#fafafa; padding:1rem; border-radius:6px;">
            <?= htmlspecialchars($r['texto_publicitario']) ?>
        </p>
    <?php endif; ?>
    <?php if ($r['notas']): ?>
        <p style="margin-top:.8rem;"><strong>Notas:</strong><br><?= nl2br(htmlspecialchars($r['notas'])) ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($r['ruta_archivo']): ?>
<div class="card">
    <h3 style="margin:0 0 .8rem; font-size:1rem; color:var(--vino);">📁 Material</h3>
    <p><?= htmlspecialchars($r['ruta_archivo']) ?></p>
</div>
<?php endif; ?>

<?php if (Auth::can('metricas.ver') && !empty($metricas)): ?>
<div class="card">
    <h3 style="margin:0 0 .8rem; font-size:1rem; color:var(--vino);">📊 Métricas</h3>
    <table class="tabla" style="font-size:.85rem;">
        <thead>
            <tr>
                <th>Red</th><th>Alcance</th><th>Impresiones</th><th>Me gusta</th>
                <th>Comentarios</th><th>Compartidos</th><th>Interacción</th>
                <th>Coment. +</th><th>Coment. −</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($metricas as $m): ?>
                <tr>
                    <td><strong><?= htmlspecialchars(ucfirst($m['red'])) ?></strong></td>
                    <td><?= number_format((int)$m['alcance']) ?></td>
                    <td><?= number_format((int)$m['impresiones']) ?></td>
                    <td><?= number_format((int)$m['me_gusta']) ?></td>
                    <td><?= number_format((int)$m['comentarios']) ?></td>
                    <td><?= number_format((int)$m['compartidos']) ?></td>
                    <td><?= number_format((int)$m['interaccion']) ?></td>
                    <td style="color:var(--verde);"><?= number_format((int)$m['coment_positivos']) ?></td>
                    <td style="color:var(--rojo-mod7);"><?= number_format((int)$m['coment_negativos']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>