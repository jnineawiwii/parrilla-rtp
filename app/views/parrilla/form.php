<?php
$esEdicion = $registro !== null;
$titulo = $esEdicion ? 'Editar publicación' : 'Nueva publicación';

$v = function($k, $def='') use ($registro) {
    return htmlspecialchars($registro[$k] ?? $def);
};
$sel = function($k, $id) use ($registro) {
    return ((string)($registro[$k] ?? '') === (string)$id) ? 'selected' : '';
};
?>

<div class="card">
    <h2 style="margin:0 0 1rem; color:var(--vino);">
        <?= $esEdicion ? '✏️ Editar publicación' : '➕ Nueva publicación' ?>
    </h2>

    <form id="formParrilla" method="POST" action="/parrilla/guardar">
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
            <textarea id="copy_in" name="copy_in" class="form-control" rows="5"><?= $v('texto_publicitario') ?></textarea>
        </fieldset>

        <!-- PRODUCCIÓN -->
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
                    <label class="form-label" for="link_material">Ruta / Link del material</label>
                    <input type="text" id="link_material" name="link_material" class="form-control"
                           value="<?= $v('ruta_archivo') ?>">
                </div>
            </div>

            <label class="form-label mt-2" for="idea">Notas internas</label>
            <textarea id="idea" name="idea" class="form-control" rows="2"><?= $v('notas') ?></textarea>
        </fieldset>

        <!-- MÉTRICAS (solo admin/editor) -->
        <?php if (Auth::can('metricas.editar')): ?>
        <fieldset class="border rounded p-3 mb-2">
            <legend class="fs-6" style="color:var(--vino); font-weight:600;">📊 Métricas por red</legend>
            <p class="text-muted" style="font-size:.85rem;">Llena solo las redes donde se publicó. Se guardan en la BD y alimentan las gráficas.</p>

            <?php
            // Cargar valores guardados si es edición
            $metricasGuardadas = [];
            if ($esEdicion) {
                foreach (Metrica::porPublicacion((int)$registro['id_publicacion']) as $m) {
                    $metricasGuardadas[$m['red']] = $m;
                }
            }
            $redes = [
                'facebook'  => 'Facebook',
                'instagram' => 'Instagram',
                'x'         => 'X (Twitter)',
                'youtube'   => 'YouTube',
            ];
            $campos = [
                'alcance'=>'Alcance','impresiones'=>'Impresiones','me_gusta'=>'Me gusta',
                'comentarios'=>'Comentarios','compartidos'=>'Compartidos','interaccion'=>'Interacción',
                'reproducciones'=>'Reproducciones','coment_positivos'=>'Coment. +',
                'coment_negativos'=>'Coment. −','coment_neutros'=>'Coment. neutros',
            ];
            foreach ($redes as $red => $nom):
                $mg = $metricasGuardadas[$red] ?? [];
            ?>
                <details style="margin-bottom:.5rem; border:1px solid #eee; border-radius:6px; padding:.5rem;"
                         <?= !empty($mg) ? 'open' : '' ?>>
                    <summary style="cursor:pointer; font-weight:600; color:var(--vino);"><?= $nom ?></summary>
                    <div class="row mt-1">
                        <?php foreach ($campos as $k => $lbl): ?>
                            <div class="col" style="flex:1 1 130px;">
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
    const btnIA = document.getElementById('btnGenerarIA');
    if (!btnIA) return;
    btnIA.addEventListener('click', async function () {
        const textoOrig = btnIA.textContent;
        btnIA.disabled = true;
        btnIA.textContent = '✨ Generando…';
        try {
            const fd = new FormData(document.getElementById('formParrilla'));
            const r = await fetch('/api/ia/copy', { method: 'POST', body: fd });
            const j = await r.json();
            if (j.ok) {
                document.getElementById('copy_in').value = j.copy;
            } else {
                alert('Error: ' + (j.error || 'No se pudo generar el copy'));
            }
        } catch (e) {
            alert('Error de red: ' + e.message);
        } finally {
            btnIA.disabled = false;
            btnIA.textContent = textoOrig;
        }
    });
});
</script>