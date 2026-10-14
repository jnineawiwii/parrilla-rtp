<?php
$esEdicion = $registro !== null;
$titulo    = $esEdicion ? 'Editar publicación' : 'Nueva publicación';
$redesSeleccionadas = $redesSeleccionadas ?? [];

$v = function($k, $def='') use ($registro) {
    return htmlspecialchars($registro[$k] ?? $def);
};
$sel = function($k, $id) use ($registro) {
    return ((string)($registro[$k] ?? '') === (string)$id) ? 'selected' : '';
};

// Definición de campos por red (solo los relevantes)
$redesDefinicion = [
    'facebook' => [
        'nombre' => 'Facebook',
        'campos' => [
            'reacciones'  => 'Reacciones',
            'comentarios' => 'Comentarios',
            'interaccion' => 'Interacción',
            'compartidos' => 'Compartidos',
        ],
    ],
    'instagram' => [
        'nombre' => 'Instagram',
        'campos' => [
            'me_gusta'    => 'Me gusta',
            'comentarios' => 'Comentarios',
            'compartidos' => 'Compartidos',
        ],
    ],
    'x' => [
        'nombre' => 'X (Twitter)',
        'campos' => [
            'me_gusta'    => 'Me gusta',
            'comentarios' => 'Comentarios',
            'compartidos' => 'Compartidos',
        ],
    ],
    'youtube' => [
        'nombre' => 'YouTube',
        'campos' => [
            'me_gusta'    => 'Likes',
            'comentarios' => 'Comentarios',
            'vistas'      => 'Vistas',
        ],
    ],
];

// Cargar métricas guardadas si es edición
$metricasGuardadas = [];
if ($esEdicion) {
    foreach (Metrica::porPublicacion((int)$registro['id_publicacion']) as $m) {
        $metricasGuardadas[$m['red']] = $m;
    }
}
?>

<div class="card">
    <h2 style="margin:0 0 1rem; color:var(--vino);">
        <?= $esEdicion ? '✏️ Editar publicación' : '➕ Nueva publicación' ?>
    </h2>

    <form id="formParrilla" method="POST" action="/parrilla/guardar" enctype="multipart/form-data">
        <?= Csrf::campo() ?>
        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?= (int)$registro['id_publicacion'] ?>">
        <?php endif; ?>

        <!-- DATOS BÁSICOS -->
        <fieldset class="border rounded p-3 mb-2">
            <legend class="fs-6" style="color:var(--vino); font-weight:600;">📅 Datos básicos</legend>

            <div class="row">
                <div class="col" style="flex:1 1 160px;">
                    <label class="form-label" for="fecha">Fecha *</label>
                    <?php
                    $fechaVal = '';
                    $horaVal  = '';
                    if (!empty($registro['fecha_publicacion'])) {
                        $ts = strtotime($registro['fecha_publicacion']);
                        $fechaVal = date('Y-m-d', $ts);
                        $horaVal  = date('H:i', $ts);
                    }
                    ?>
                    <input type="date" id="fecha" name="fecha" class="form-control"
                           required value="<?= $fechaVal ?>">
                </div>
                <div class="col" style="flex:1 1 120px;">
                    <label class="form-label" for="hora">Hora</label>
                    <input type="time" id="hora" name="hora" class="form-control"
                           value="<?= $horaVal ?>">
                </div>
                <div class="col" style="flex:1 1 140px;">
                    <label class="form-label" for="dia_semana">Día</label>
                    <select id="dia_semana" name="dia_semana" class="form-select">
                        <option value="">—</option>
                        <?php foreach (['lunes','martes','miércoles','jueves','viernes','sábado','domingo'] as $d): ?>
                            <option value="<?= $d ?>" <?= $sel('dia_semana',$d) ?>><?= ucfirst($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col" style="flex:1 1 160px;">
                    <label class="form-label" for="estado">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <?php foreach (['Borrador','Programado','Aprobado','Publicado','Cancelado'] as $e): ?>
                            <option value="<?= $e ?>" <?= $sel('estado',$e) ?>><?= $e ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <!-- CLASIFICACIÓN -->
        <fieldset class="border rounded p-3 mb-2">
            <legend class="fs-6" style="color:var(--vino); font-weight:600;">📌 Clasificación</legend>

            <div class="row">
                <div class="col" style="flex:1 1 220px;">
                    <label class="form-label" for="campana_id">Campaña</label>
                    <select id="campana_id" name="campana_id" class="form-select">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($campanas as $c): ?>
                            <option value="<?= $c['id_campana'] ?>" <?= $sel('id_campana', $c['id_campana']) ?>>
                                <?= htmlspecialchars($c['nombre_campana']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col" style="flex:1 1 220px;">
                    <label class="form-label" for="tema_id">Tema</label>
                    <select id="tema_id" name="tema_id" class="form-select">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($temas as $t): ?>
                            <option value="<?= $t['id_tema'] ?>" <?= $sel('id_tema', $t['id_tema']) ?>>
                                <?= htmlspecialchars($t['nombre_tema']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col" style="flex:1 1 220px;">
                    <label class="form-label" for="subtema_id">Subtema</label>
                    <select id="subtema_id" name="subtema_id" class="form-select">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($subtemas as $s): ?>
                            <option value="<?= $s['id_subtema'] ?>" <?= $sel('id_subtema', $s['id_subtema']) ?>>
                                <?= htmlspecialchars($s['nombre_subtema']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col" style="flex:1 1 220px;">
                    <label class="form-label" for="formato_id">Formato</label>
                    <select id="formato_id" name="formato_id" class="form-select">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($formatos as $f): ?>
                            <option value="<?= $f['id_formato'] ?>" <?= $sel('id_formato_contenido', $f['id_formato']) ?>>
                                <?= htmlspecialchars($f['nombre_formato']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <!-- CONTENIDO -->
        <fieldset class="border rounded p-3 mb-2">
            <legend class="fs-6" style="color:var(--vino); font-weight:600;">📝 Contenido</legend>

            <label class="form-label" for="titulo">Título *</label>
            <input type="text" id="titulo" name="titulo" class="form-control" required
                   maxlength="200" value="<?= $v('titulo') ?>">

            <label class="form-label mt-2" for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="3"><?= $v('descripcion') ?></textarea>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:.8rem;">
                <label class="form-label" for="copy_in" style="margin:0;">Copy / Texto publicitario</label>
                <?php if (Auth::can('parrilla.editar')): ?>
                    <button type="button" id="btnGenerarIA" class="btn btn-sm btn-outline">
                        ✨ Generar con IA
                    </button>
                <?php endif; ?>
            </div>
            <textarea id="copy_in" name="copy_in" class="form-control" rows="5"><?= $v('copy') ?></textarea>
        </fieldset>

        <!-- REDES SOCIALES (NUEVO) -->
        <fieldset class="border rounded p-3 mb-2">
            <legend class="fs-6" style="color:var(--vino); font-weight:600;">🌐 Redes sociales</legend>
            <p class="text-muted" style="font-size:.85rem; margin:.25rem 0 .5rem;">
                Marca en qué redes se publicará o se publicó este contenido.
                Solo se mostrarán las pestañas de métricas de las redes marcadas.
            </p>

            <div class="d-flex" style="gap:.75rem; flex-wrap:wrap;">
                <?php foreach ($redesDefinicion as $key => $info):
                    $color    = Redes::color($key);
                    $checked  = in_array($key, $redesSeleccionadas, true);
                ?>
                    <label class="red-check"
                           data-color="<?= $color ?>"
                           style="display:flex; align-items:center; gap:.4rem; cursor:pointer;
                                  padding:.45rem .8rem; border:2px solid <?= $color ?>;
                                  border-radius:6px; font-weight:600; color:<?= $color ?>;
                                  background:<?= $checked ? $color.'1A' : '#fff' ?>;
                                  transition:.15s; user-select:none;">
                        <input type="checkbox" name="redes[]" value="<?= $key ?>"
                               <?= $checked ? 'checked' : '' ?>
                               data-red="<?= $key ?>"
                               style="accent-color:<?= $color ?>; width:16px; height:16px; margin:0;">
                        <?= htmlspecialchars($info['nombre']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <!-- CONTENIDO MULTIMEDIA -->
        <fieldset class="border rounded p-3 mb-2">
            <legend class="fs-6" style="color:var(--vino); font-weight:600;">🎬 Producción</legend>

            <div class="row">
                <div class="col" style="flex:1 1 260px;">
                    <label class="form-label" for="encargado_produccion">Responsable / Producción</label>
                    <select id="encargado_produccion" name="encargado_produccion" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($responsables as $u): ?>
                            <option value="<?= $u['id_usuario'] ?>" <?= $sel('id_usuario_responsable', $u['id_usuario']) ?>>
                                <?= htmlspecialchars($u['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col" style="flex:1 1 260px;">
                    <label class="form-label" for="encargado_edicion">Postproducción</label>
                    <select id="encargado_edicion" name="encargado_edicion" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($responsables as $u): ?>
                            <option value="<?= $u['id_usuario'] ?>" <?= $sel('id_usuario_postproduccion', $u['id_usuario']) ?>>
                                <?= htmlspecialchars($u['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col" style="flex:1 1 300px;">
                    <label class="form-label" for="archivo_material">Archivo para publicar (imagen o video)</label>
                    <input type="file" id="archivo_material" name="archivo_material" class="form-control"
                           accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm">
                    <small class="text-muted">JPG, PNG, WebP, GIF, MP4 o WebM. Máximo 50 MB.</small>
                    <div id="materialPreview" class="material-preview" aria-live="polite">
                        <?php if (!empty($registro['ruta_archivo']) && in_array($registro['tipo_archivo'] ?? '', ['imagen', 'video'], true)): ?>
                            <?php if ($registro['tipo_archivo'] === 'imagen'): ?>
                                <img src="<?= htmlspecialchars($registro['ruta_archivo']) ?>" alt="Vista previa del archivo actual">
                            <?php else: ?>
                                <video src="<?= htmlspecialchars($registro['ruta_archivo']) ?>" controls preload="metadata"></video>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col" style="flex:1 1 300px;">
                    <label class="form-label" for="link_material">Enlace externo (opcional)</label>
                    <input type="text" id="link_material" name="link_material" class="form-control"
                           value="<?= $v('url_material') ?>">
                    <small class="text-muted">Si adjuntas un archivo, se usará ese medio en la parrilla.</small>
                </div>
            </div>

            <label class="form-label mt-2" for="idea">Notas internas</label>
            <textarea id="idea" name="idea" class="form-control" rows="2"><?= $v('notas') ?></textarea>
        </fieldset>

        <!-- MÉTRICAS (solo admin/editor) -->
        <?php if (Auth::can('metricas.editar')): ?>
        <fieldset class="border rounded p-3 mb-2">
            <legend class="fs-6" style="color:var(--vino); font-weight:600;">📊 Métricas por red</legend>
            <p class="text-muted" id="sinRedesMsg" style="font-size:.85rem; display:none;">
                Marca al menos una red social arriba para capturar sus métricas.
            </p>

            <?php foreach ($redesDefinicion as $red => $info):
                $mg = $metricasGuardadas[$red] ?? [];
            ?>
                <details data-red="<?= $red ?>"
                         style="margin-bottom:.5rem; border:1px solid #eee; border-radius:6px; padding:.5rem;"
                         <?= !empty($mg) ? 'open' : '' ?>>
                    <summary style="cursor:pointer; font-weight:600; color:<?= Redes::color($red) ?>;">
                        <?= htmlspecialchars($info['nombre']) ?>
                    </summary>
                    <div class="row mt-1">
                        <?php foreach ($info['campos'] as $k => $lbl): ?>
                            <div class="col" style="flex:1 1 140px;">
                                <label class="form-label" style="font-size:.75rem;"><?= $lbl ?></label>
                                <input type="number" min="0" class="form-control"
                                       name="metrica[<?= $red ?>][<?= $k ?>]"
                                       value="<?= (int)($mg[$k] ?? 0) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </fieldset>
        <?php endif; ?>

        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary">💾 Guardar</button>
            <a href="/parrilla" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Preview de archivo multimedia ---------- */
    const mediaInput   = document.getElementById('archivo_material');
    const mediaPreview = document.getElementById('materialPreview');
    let previewUrl     = null;

    if (mediaInput && mediaPreview) {
        mediaInput.addEventListener('change', function () {
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            mediaPreview.replaceChildren();
            const file = mediaInput.files[0];
            if (!file) return;

            previewUrl = URL.createObjectURL(file);
            const element = file.type.startsWith('image/')
                ? document.createElement('img')
                : document.createElement('video');
            element.src = previewUrl;
            element.alt = file.type.startsWith('image/') ? 'Vista previa del archivo seleccionado' : '';
            if (element.tagName === 'VIDEO') {
                element.controls = true;
                element.preload  = 'metadata';
            }
            mediaPreview.append(element);
        });
    }

    /* ---------- Sincronizar redes ↔ pestañas de métricas ---------- */
    const checkboxes = document.querySelectorAll('input[name="redes[]"]');
    const detalles   = document.querySelectorAll('details[data-red]');
    const sinRedes   = document.getElementById('sinRedesMsg');

    function sincronizarRedes() {
        const marcadas = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        // Mostrar/ocultar pestañas de métricas
        detalles.forEach(det => {
            const visible = marcadas.includes(det.dataset.red);
            det.style.display = visible ? '' : 'none';
            if (!visible) det.open = false;
        });

        // Mensaje si no hay redes
        if (sinRedes) {
            const hayDetalles = Array.from(detalles).some(d => marcadas.includes(d.dataset.red));
            sinRedes.style.display = marcadas.length === 0 ? 'block' : 'none';
        }

        // Feedback visual en chips
        document.querySelectorAll('.red-check').forEach(label => {
            const input = label.querySelector('input');
            const color = label.dataset.color;
            label.style.background = input.checked ? color + '1A' : '#fff';
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', sincronizarRedes));
    sincronizarRedes();

    /* ---------- Botón IA ---------- */
    const btnIA = document.getElementById('btnGenerarIA');
    if (btnIA) {
        btnIA.addEventListener('click', async function () {
            const textoOrig = btnIA.textContent;
            btnIA.disabled  = true;
            btnIA.textContent = '✨ Generando…';
            try {
                const fd = new FormData(document.getElementById('formParrilla'));
                const r  = await fetch('/api/ia/copy', { method: 'POST', body: fd });
                const j  = await r.json();
                if (j.ok) {
                    document.getElementById('copy_in').value = j.copy;
                } else {
                    alert('Error: ' + (j.error || 'No se pudo generar el copy'));
                }
            } catch (e) {
                alert('Error de red: ' + e.message);
            } finally {
                btnIA.disabled    = false;
                btnIA.textContent = textoOrig;
            }
        });
    }
});
</script>