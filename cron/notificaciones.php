<?php
/**
 * Script de recordatorios automáticos
 * Ejecutar 1 vez al día (ej. 7:00 AM) vía cron.
 *
 * Notifica a:
 *   - id_usuario_responsable
 *   - id_usuario_produccion
 *   - id_usuario_postproduccion
 * de cada publicación de HOY y MAÑANA.
 */

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/database.php';

spl_autoload_register(function ($clase) {
    foreach ([
        APP_ROOT . '/app/models/'  . $clase . '.php',
        APP_ROOT . '/app/helpers/' . $clase . '.php',
    ] as $archivo) {
        if (file_exists($archivo)) { require $archivo; return; }
    }
});

$logFile = APP_ROOT . '/storage/logs/notificaciones.log';

function logMsg(string $msg): void {
    global $logFile;
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    @file_put_contents($logFile, $linea, FILE_APPEND);
    echo $linea;
}

logMsg('=== Inicio de generación de recordatorios ===');

/**
 * Extrae los IDs únicos de usuarios vinculados a una publicación
 * (responsable, producción, postproducción), filtrando nulos y duplicados.
 */
function responsablesDe(array $pub): array {
    $ids = [
        $pub['id_usuario_responsable']    ?? null,
        $pub['id_usuario_produccion']     ?? null,
        $pub['id_usuario_postproduccion'] ?? null,
    ];
    $ids = array_filter($ids, fn($v) => !empty($v));
    return array_values(array_unique(array_map('intval', $ids)));
}

/**
 * Crea las notificaciones para un conjunto de publicaciones.
 */
function procesarPublicaciones(array $publicaciones, string $tipo, string $prefijo): void {
    foreach ($publicaciones as $pub) {
        $ids = responsablesDe($pub);

        if (empty($ids)) {
            logMsg("  ⚠ Publicación #{$pub['id_publicacion']} sin responsables, se omite.");
            continue;
        }

        $hora = $pub['fecha_publicacion']
              ? date('H:i', strtotime($pub['fecha_publicacion']))
              : 'sin hora';

        $mensaje = $prefijo . ' se publica: "' . $pub['titulo'] . '" a las ' . $hora;

        foreach ($ids as $uid) {
            // Evitar duplicados del mismo día
            $st = db()->prepare("
                SELECT 1 FROM gestion_notificaciones
                WHERE id_usuario = ?
                  AND id_publicacion = ?
                  AND tipo_notificacion = ?
                  AND creado_en::date = CURRENT_DATE
                LIMIT 1
            ");
            $st->execute([$uid, $pub['id_publicacion'], $tipo]);

            if ($st->fetchColumn()) continue;

            db()->prepare("
                INSERT INTO gestion_notificaciones
                    (id_usuario, id_publicacion, mensaje, tipo_notificacion)
                VALUES (?, ?, ?, ?)
            ")->execute([$uid, $pub['id_publicacion'], $mensaje, $tipo]);
        }

        logMsg("  ✓ #{$pub['id_publicacion']} → " . count($ids) . " responsable(s)");
    }
}

// ============================================================
// 1. Publicaciones de HOY
// ============================================================
$hoy = db()->query("
    SELECT id_publicacion, titulo, fecha_publicacion, estado,
           id_usuario_responsable, id_usuario_produccion, id_usuario_postproduccion
    FROM gestion_publicaciones
    WHERE fecha_publicacion::date = CURRENT_DATE
      AND estado IN ('Borrador','Programado','Aprobado')
    ORDER BY fecha_publicacion
")->fetchAll();

logMsg('Publicaciones de HOY: ' . count($hoy));
procesarPublicaciones($hoy, 'recordatorio_hoy', '📅 HOY');

// ============================================================
// 2. Publicaciones de MAÑANA
// ============================================================
$manana = db()->query("
    SELECT id_publicacion, titulo, fecha_publicacion, estado,
           id_usuario_responsable, id_usuario_produccion, id_usuario_postproduccion
    FROM gestion_publicaciones
    WHERE fecha_publicacion::date = (CURRENT_DATE + INTERVAL '1 day')
      AND estado IN ('Borrador','Programado','Aprobado')
    ORDER BY fecha_publicacion
")->fetchAll();

logMsg('Publicaciones de MAÑANA: ' . count($manana));
procesarPublicaciones($manana, 'recordatorio_manana', '⏰ MAÑANA');

// ============================================================
// 3. Limpieza de recordatorios vencidos
// ============================================================
$borradas = db()->exec("
    DELETE FROM gestion_notificaciones n
    USING gestion_publicaciones p
    WHERE n.id_publicacion = p.id_publicacion
      AND n.tipo_notificacion IN ('recordatorio_hoy','recordatorio_manana')
      AND (
            p.fecha_publicacion::date < CURRENT_DATE
         OR p.estado IN ('Publicado','Cancelado')
      )
");

logMsg('Notificaciones vencidas borradas: ' . $borradas);
logMsg('=== Fin ===');