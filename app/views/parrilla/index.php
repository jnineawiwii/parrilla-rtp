<?php
$titulo = 'Parrilla de contenidos';
$puedeCrear  = Auth::can('parrilla.crear');
$puedeBorrar = Auth::can('parrilla.borrar');
?>

<div class="card">
    <div class="d-flex" style="justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
        <h2 style="margin:0; color:var(--vino);">Parrilla de contenidos</h2>
        <?php if ($puedeCrear): ?>
            <a href="/parrilla/crear" class="btn btn-primary">➕ Nueva publicación</a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <form method="GET" action="/parrilla" class="row" style="align-items:flex-end;">
        <div class="col">
            <label class="form-label" for="q">Buscar</label>
            <input type="text" id="q" name="q" class="form-control"
                   value="<?= htmlspecialchars($filtros['q'] ?? '') ?>"
                   placeholder="Título o descripción…">
        </div>
        <div class="col">
            <label class="form-label" for="mes">Mes</label>
            <select id="mes" name="mes" class="form-select">
                <option value="">— Todos —</option>
                <?php
                $meses = ['1'=>'Enero','2'=>'Febrero','3'=>'Marzo','4'=>'Abril','5'=>'Mayo','6'=>'Junio',
                          '7'=>'Julio','8'=>'Agosto','9'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
                foreach ($meses as $n => $nom):
                    $sel = ((string)$filtros['mes'] === (string)$n) ? 'selected' : '';
                ?>
                    <option value="<?= $n ?>" <?= $sel ?>><?= $nom ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <label class="form-label" for="campana">Campaña</label>
            <select id="campana" name="campana" class="form-select">
                <option value="">— Todas —</option>
                <?php foreach ($campanas as $c): ?>
                    <option value="<?= $c['id_campana'] ?>"
                        <?= ((string)$filtros['campana'] === (string)$c['id_campana']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre_campana']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <label class="form-label" for="estado">Estado</label>
            <select id="estado" name="estado" class="form-select">
                <option value="">— Todos —</option>
                <?php foreach (['Borrador','Programado','Aprobado','Publicado','Cancelado'] as $e): ?>
                    <option value="<?= $e ?>" <?= ($filtros['estado']===$e)?'selected':'' ?>><?= $e ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col" style="flex:0 0 auto;">
            <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
            <a href="/parrilla" class="btn btn-outline">Limpiar</a>
        </div>
    </form>
</div>

<div class="card" style="padding:0; overflow-x:auto;">
    <?php if (empty($registros)): ?>
        <p class="text-muted" style="padding:2rem; text-align:center;">
            No hay publicaciones que coincidan con los filtros.
        </p>
    <?php else: ?>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Archivo</th>
                    <th>Título / Descripción</th>
                    <th>Copy</th>
                    <th>Campaña</th>
                    <th>Tema / Subtema</th>
                    <th>Formato</th>
                    <th>Responsable</th>
                    <th>Producción / Post</th>
                    <th>Estado (Avance)</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td>
                            <?= $r['fecha_publicacion']
                                ? date('d/m/Y H:i', strtotime($r['fecha_publicacion']))
                                : '—' ?>
                        </td>
                        <td>
                            <?php if (!empty($r['ruta_archivo']) && ($r['tipo_archivo'] ?? '') === 'imagen'): ?>
                                <a href="<?= htmlspecialchars($r['ruta_archivo']) ?>" target="_blank" rel="noopener noreferrer"
                                   aria-label="Abrir imagen de <?= htmlspecialchars($r['titulo']) ?>">
                                    <img class="parrilla-media-thumb" src="<?= htmlspecialchars($r['ruta_archivo']) ?>"
                                         alt="Imagen de <?= htmlspecialchars($r['titulo']) ?>" loading="lazy">
                                </a>
                            <?php elseif (!empty($r['ruta_archivo']) && ($r['tipo_archivo'] ?? '') === 'video'): ?>
                                <a class="parrilla-video-link" href="<?= htmlspecialchars($r['ruta_archivo']) ?>" target="_blank" rel="noopener noreferrer">
                                    ▶ Ver video
                                </a>
                            <?php elseif (!empty($r['url_material'])): ?>
                                <a href="<?= htmlspecialchars($r['url_material']) ?>" target="_blank" rel="noopener noreferrer">Abrir enlace</a>
                            <?php elseif (!empty($r['ruta_archivo'])): ?>
                                <a href="<?= htmlspecialchars($r['ruta_archivo']) ?>" target="_blank" rel="noopener noreferrer">Abrir material</a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <a href="/parrilla/ver/<?= $r['id_publicacion'] ?>"
                               style="color:var(--vino); font-weight:600; text-decoration:none;">
                                <?= htmlspecialchars($r['titulo']) ?>
                            </a>
                            <?php if (!empty($r['descripcion'])): ?>
                                <div class="text-muted" style="font-size:.75rem; margin-top:.2rem; max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= htmlspecialchars($r['descripcion']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($r['copy'])): ?>
                                <div title="<?= htmlspecialchars($r['copy']) ?>"
                                     style="font-size:.8rem; max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:help;">
                                    <?= htmlspecialchars($r['copy']) ?>
                                </div>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['campana_nombre']): ?>
                                <span class="chip"
                                      style="background:<?= htmlspecialchars($r['campana_color'] ?? '#55585A') ?>; color:#fff;">
                                    <?= htmlspecialchars(str_replace('_',' ', $r['campana_nombre'])) ?>
                                </span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <div style="font-size:.85rem;"><strong><?= htmlspecialchars($r['tema_nombre'] ?? '—') ?></strong></div>
                            <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($r['subtema_nombre'] ?? '—') ?></div>
                        </td>
                        <td><?= htmlspecialchars($r['formato_nombre'] ?? '—') ?></td>
                        <td>
                            <div style="font-size:.85rem;"><?= htmlspecialchars($r['responsable_nombre'] ?? '—') ?></div>
                        </td>
                        <td>
                            <div style="font-size:.75rem;" class="text-muted">Prod: <?= htmlspecialchars($r['produccion_nombre'] ?? '—') ?></div>
                            <div style="font-size:.75rem;" class="text-muted">Post: <?= htmlspecialchars($r['postproduccion_nombre'] ?? '—') ?></div>
                        </td>
                        <td>
                            <?php
                            $colorEstado = [
                                'Borrador'   => 'var(--gris-mid)',
                                'Programado' => 'var(--azul)',
                                'Aprobado'   => 'var(--celeste)',
                                'Publicado'  => 'var(--verde)',
                                'Cancelado'  => 'var(--rojo-mod7)',
                            ][$r['estado']] ?? 'var(--gris-mid)';
                            ?>
                            <span class="chip" style="background:<?= $colorEstado ?>; color:#fff;">
                                <?= htmlspecialchars($r['estado']) ?>
                            </span>
                            <?php if (!empty($r['notas'])): ?>
                                <div class="text-muted" style="font-size:.7rem; margin-top:.2rem;" title="<?= htmlspecialchars($r['notas']) ?>">
                                    📝 <?= htmlspecialchars(mb_substr($r['notas'], 0, 30)) ?>...
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a href="/parrilla/ver/<?= $r['id_publicacion'] ?>" class="btn btn-sm btn-outline">👁 Ver</a>
                            
                            <?php if (Auth::can('parrilla.aprobar') && !in_array($r['estado'], ['Aprobado','Publicado'])): ?>
                                <form action="/parrilla/aprobar/<?= $r['id_publicacion'] ?>" method="POST"
                                      style="display:inline"
                                      onsubmit="return confirm('¿Aprobar esta publicación?');">
                                    <?= Csrf::campo() ?>
                                    <button type="submit" class="btn btn-sm" title="Aprobar"
                                            style="background:var(--verde); color:#fff;">✅</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if (Auth::can('parrilla.editar')): ?>
                                <a href="/parrilla/editar/<?= $r['id_publicacion'] ?>" class="btn btn-sm btn-outline">✏️ Editar</a>
                            <?php endif; ?>
                            
                            <?php if ($puedeBorrar): ?>
                                <form action="/parrilla/eliminar/<?= $r['id_publicacion'] ?>" method="POST"
                                      style="display:inline"
                                      onsubmit="return confirm('¿Eliminar esta publicación?');">
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

<?php
$hayAnterior = $pag > 1;
$haySiguiente = count($registros) === 25;
if ($hayAnterior || $haySiguiente):
    $qs = $_GET; unset($qs['pag']);
    $base = '/parrilla?' . http_build_query($qs);
?>
<div class="d-flex gap-1 mt-2" style="justify-content:center;">
    <?php if ($hayAnterior): ?>
        <a href="<?= $base . '&pag=' . ($pag-1) ?>" class="btn btn-outline">← Anterior</a>
    <?php endif; ?>
    <span class="btn" style="background:#fff; cursor:default;">Página <?= $pag ?></span>
    <?php if ($haySiguiente): ?>
        <a href="<?= $base . '&pag=' . ($pag+1) ?>" class="btn btn-outline">Siguiente →</a>
    <?php endif; ?>
</div>
<?php endif; ?>