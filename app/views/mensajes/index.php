<?php
$titulo = 'Mensajes';
$uid = (int)Auth::user()['id'];

// Determinar qué se está mostrando
$conId = (int)($_GET['con'] ?? 0);      // ID del usuario con quien conversar
$tab   = $_GET['tab'] ?? 'conversacion'; // 'conversacion' | 'recibidos' | 'enviados'

// Si no hay usuario seleccionado, tomar el primero de la lista (que no sea yo)
$usuariosOtros = array_values(array_filter($usuarios, fn($u) => (int)$u['id_usuario'] !== $uid));
if ($conId === 0 && !empty($usuariosOtros) && $tab === 'conversacion') {
    $conId = (int)$usuariosOtros[0]['id_usuario'];
}

// Usuario actual con quien se conversa
$usuarioActual = null;
foreach ($usuarios as $u) {
    if ((int)$u['id_usuario'] === $conId) { $usuarioActual = $u; break; }
}

// Mensajes de la conversación actual (enviados y recibidos entre los dos)
$conversacion = [];
if ($usuarioActual && $tab === 'conversacion') {
    $st = db()->prepare("
        SELECT m.*,
               ue.nombre_completo AS remitente_nombre,
               ue.id_usuario      AS remitente_id
        FROM gestion_mensajes m
        JOIN gestion_usuarios ue ON ue.id_usuario = m.id_remitente
        WHERE (m.id_remitente = :yo AND m.id_destinatario = :el)
           OR (m.id_remitente = :el AND m.id_destinatario = :yo)
        ORDER BY m.id_mensaje ASC
    ");
    $st->execute([':yo' => $uid, ':el' => $conId]);
    $conversacion = $st->fetchAll();
}

// Marcar como leídos los mensajes que me envió este usuario
if ($usuarioActual) {
    db()->prepare("
        UPDATE gestion_mensajes
        SET leido = TRUE
        WHERE id_remitente = ? AND id_destinatario = ? AND leido = FALSE
    ")->execute([$conId, $uid]);
}
?>

<div class="card" style="padding:.8rem 1rem;">
    <h2 style="margin:0; color:var(--vino); font-size:1.2rem;">💬 Mensajes</h2>
</div>

<div class="msg-layout" style="display:grid; grid-template-columns:280px 1fr; gap:1rem; min-height:75vh;">

    <!-- ============ COLUMNA IZQUIERDA: USUARIOS ============ -->
    <aside class="card" style="padding:0; overflow-y:auto; max-height:80vh;">
        <div style="padding:.7rem 1rem; border-bottom:2px solid var(--gris-claro); background:var(--gris-claro);">
            <strong style="color:var(--vino); font-size:.85rem;">👥 USUARIOS</strong>
        </div>

        <?php foreach ($usuarios as $u):
            $esYo    = ((int)$u['id_usuario'] === $uid);
            $activo  = ((int)$u['id_usuario'] === $conId && $tab === 'conversacion');

            // Contar no leídos de este usuario hacia mí
            $noLeidos = 0;
            if (!$esYo) {
                $st = db()->prepare("
                    SELECT COUNT(*) FROM gestion_mensajes
                    WHERE id_remitente = ? AND id_destinatario = ? AND leido = FALSE
                ");
                $st->execute([$u['id_usuario'], $uid]);
                $noLeidos = (int)$st->fetchColumn();
            }
        ?>
            <?php if ($esYo): ?>
                <div style="padding:.7rem 1rem; display:flex; align-items:center; gap:.6rem;
                            background:#f6f6f6; border-bottom:1px solid #eee;">
                    <span style="font-size:1.1rem;">🟢</span>
                    <div style="flex:1; font-size:.85rem;">
                        <div style="font-weight:600;"><?= htmlspecialchars($u['nombre_completo']) ?></div>
                        <div class="text-muted" style="font-size:.75rem;">(tú)</div>
                    </div>
                </div>
            <?php else: ?>
                <a href="/mensajes?con=<?= $u['id_usuario'] ?>"
                   style="display:flex; align-items:center; gap:.6rem;
                          padding:.7rem 1rem; border-bottom:1px solid #eee;
                          text-decoration:none; color:inherit;
                          <?= $activo ? 'background:#fff7e0; border-left:4px solid var(--vino);' : '' ?>">
                    <span style="font-size:1.1rem;">⚪</span>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:600; font-size:.85rem;
                                    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?= htmlspecialchars($u['nombre_completo']) ?>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <?= htmlspecialchars($u['rol']) ?>
                        </div>
                    </div>
                    <?php if ($noLeidos > 0): ?>
                        <span style="background:var(--rojo-mod7); color:#fff;
                                     border-radius:999px; padding:.1rem .45rem;
                                     font-size:.7rem; font-weight:700;">
                            <?= $noLeidos ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

        <div style="padding:.7rem 1rem; border-top:2px solid var(--gris-claro);">
            <a href="/mensajes?tab=recibidos"
               class="btn btn-sm <?= $tab==='recibidos'?'btn-primary':'btn-outline' ?>"
               style="width:100%; text-align:left; margin-bottom:.3rem;">
                📥 Bandeja de recibidos
            </a>
            <a href="/mensajes?tab=enviados"
               class="btn btn-sm <?= $tab==='enviados'?'btn-primary':'btn-outline' ?>"
               style="width:100%; text-align:left;">
                📤 Bandeja de enviados
            </a>
        </div>
    </aside>

    <!-- ============ COLUMNA DERECHA: CONTENIDO ============ -->
    <section class="card" style="padding:0; display:flex; flex-direction:column; max-height:80vh;">

        <?php if ($tab === 'recibidos'): ?>
            <!-- ============ BANDEJA RECIBIDOS ============ -->
            <div style="padding:1rem; border-bottom:2px solid var(--gris-claro);">
                <h3 style="margin:0; color:var(--vino); font-size:1rem;">📥 Bandeja de recibidos</h3>
            </div>
            <div style="overflow-y:auto; padding:1rem;">
                <?php if (empty($recibidos)): ?>
                    <p class="text-muted" style="text-align:center; padding:2rem;">
                        No tienes mensajes recibidos.
                    </p>
                <?php else: ?>
                    <?php foreach ($recibidos as $m): ?>
                        <a href="/mensajes/ver/<?= $m['id_mensaje'] ?>"
                           style="display:block; padding:.7rem; border-bottom:1px solid #eee;
                                  text-decoration:none; color:inherit;">
                            <div style="display:flex; justify-content:space-between;">
                                <strong><?= htmlspecialchars($m['remitente_nombre']) ?></strong>
                                <?php if (!$m['leido']): ?>
                                    <span class="chip" style="background:var(--rojo-mod7); color:#fff;">Nuevo</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:.85rem; margin-top:.2rem;">
                                <?= htmlspecialchars($m['asunto'] ?? '(Sin asunto)') ?>
                            </div>
                            <div class="text-muted" style="font-size:.8rem; margin-top:.2rem;">
                                <?= htmlspecialchars(mb_substr($m['contenido'], 0, 80)) ?>…
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php elseif ($tab === 'enviados'): ?>
            <!-- ============ BANDEJA ENVIADOS ============ -->
            <div style="padding:1rem; border-bottom:2px solid var(--gris-claro);">
                <h3 style="margin:0; color:var(--vino); font-size:1rem;">📤 Bandeja de enviados</h3>
            </div>
            <div style="overflow-y:auto; padding:1rem;">
                <?php if (empty($enviados)): ?>
                    <p class="text-muted" style="text-align:center; padding:2rem;">
                        No has enviado mensajes.
                    </p>
                <?php else: ?>
                    <?php foreach ($enviados as $m): ?>
                        <a href="/mensajes/ver/<?= $m['id_mensaje'] ?>"
                           style="display:block; padding:.7rem; border-bottom:1px solid #eee;
                                  text-decoration:none; color:inherit;">
                            <strong><?= htmlspecialchars($m['destinatario_nombre']) ?></strong>
                            <div style="font-size:.85rem; margin-top:.2rem;">
                                <?= htmlspecialchars($m['asunto'] ?? '(Sin asunto)') ?>
                            </div>
                            <div class="text-muted" style="font-size:.8rem; margin-top:.2rem;">
                                <?= htmlspecialchars(mb_substr($m['contenido'], 0, 80)) ?>…
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php elseif ($usuarioActual): ?>
            <!-- ============ CONVERSACIÓN ============ -->
            <div style="padding:.8rem 1rem; border-bottom:2px solid var(--gris-claro);
                        display:flex; align-items:center; gap:.6rem;">
                <span style="font-size:1.3rem;">⚪</span>
                <div>
                    <div style="font-weight:600; color:var(--vino);">
                        <?= htmlspecialchars($usuarioActual['nombre_completo']) ?>
                    </div>
                    <div class="text-muted" style="font-size:.75rem;">
                        <?= htmlspecialchars($usuarioActual['correo_electronico']) ?>
                        · <?= htmlspecialchars($usuarioActual['rol']) ?>
                    </div>
                </div>
            </div>

            <div id="hiloMensajes" style="flex:1; overflow-y:auto; padding:1rem; background:#fafafa;">
                <?php if (empty($conversacion)): ?>
                    <p class="text-muted" style="text-align:center; padding:2rem;">
                        Aún no hay mensajes con <?= htmlspecialchars($usuarioActual['nombre_completo']) ?>.
                        Envía el primero 👇
                    </p>
                <?php else: ?>
                    <?php foreach ($conversacion as $m):
                        $mio = ((int)$m['remitente_id'] === $uid);
                    ?>
                        <div style="display:flex; <?= $mio ? 'justify-content:flex-end;' : 'justify-content:flex-start;' ?>
                                    margin-bottom:.6rem;">
                            <div style="max-width:70%; padding:.6rem .9rem; border-radius:12px;
                                        background:<?= $mio ? 'var(--azul)' : '#fff' ?>;
                                        color:<?= $mio ? '#fff' : 'var(--tinta)' ?>;
                                        box-shadow:0 1px 2px rgba(0,0,0,.08);">
                                <div style="font-size:.85rem; white-space:pre-wrap;"><?= htmlspecialchars($m['contenido']) ?></div>

                                <?php if (!empty($m['adjunto_ruta'])): ?>
                                    <div style="margin-top:.5rem; padding-top:.5rem; border-top:1px solid rgba(255,255,255,.3);">
                                        <a href="<?= htmlspecialchars($m['adjunto_ruta']) ?>"
                                           download="<?= htmlspecialchars($m['adjunto_nombre']) ?>"
                                           target="_blank" rel="noopener"
                                           style="color:<?= $mio ? '#fff' : 'var(--vino)' ?>; font-size:.8rem;">
                                            📎 <?= htmlspecialchars($m['adjunto_nombre']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <div style="font-size:.7rem; opacity:.65; margin-top:.3rem; text-align:right;">
                                    <?= date('d/m H:i', strtotime($m['creado_en'] ?? 'now')) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Formulario de envío -->
            <form action="/mensajes/enviar" method="POST" enctype="multipart/form-data"
                  style="padding:.7rem; border-top:1px solid #eee; display:flex; gap:.5rem; flex-direction:column;">
                <?= Csrf::campo() ?>
                <input type="hidden" name="id_destinatario" value="<?= (int)$usuarioActual['id_usuario'] ?>">
                <input type="hidden" name="asunto" value="Mensaje directo">

                <div style="display:flex; gap:.5rem;">
                    <input type="text" name="contenido" class="form-control"
                           placeholder="Escribe un mensaje…" required autofocus autocomplete="off">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
                <label style="font-size:.75rem;" class="text-muted">
                    📎 <input type="file" name="adjunto" style="font-size:.75rem;"
                              accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar">
                    (opcional, máx. 10 MB)
                </label>
            </form>

        <?php else: ?>
            <!-- ============ SIN CONVERSACIÓN SELECCIONADA ============ -->
            <div style="padding:3rem; text-align:center;">
                <p class="text-muted">
                    Selecciona un usuario de la lista para empezar a conversar.
                </p>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    // Auto-scroll al último mensaje
    const hilo = document.getElementById('hiloMensajes');
    if (hilo) hilo.scrollTop = hilo.scrollHeight;
</script>