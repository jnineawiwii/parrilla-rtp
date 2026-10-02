<?php
/**
 * Script de recordatorios automáticos
 * Se ejecuta 1 vez al día (por ejemplo a las 7:00 AM).
 *
 * Crea notificaciones para:
 *  - Publicaciones de HOY
 *  - Publicaciones de MAÑANA
 */

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/database.php';

// Autoload simple
spl_autoload_register(function ($clase) {
    foreach ([
        APP_ROOT . '/app/models/'  . $clase . '.php',
        APP_ROOT . '/app/helpers/' . $clase . '.php',
    ] as $archivo) {
        if (file_exists($archivo)) { require $archivo; return; }
    }
});

// ============================================================
// Configuración
// ============================================================
$logFile = APP_ROOT . '/storage/logs/notificaciones.log';

function logMsg(string $msg): void {
    global $logFile;
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents($logFile, $linea, FILE_APPEND);
    echo $linea;
}

logMsg('=== Inicio de generación de recordatorios ===');

// ============================================================
// 1. Obtener usuarios activos (solo admin y editor, que son
//    los que pueden intervenir en publicaciones)
// ============================================================
$usuarios = db()->query("
    SELECT id_usuario, nombre_completo, rol
    FROM gestion_usuarios
    WHERE rol IN ('admin','editor')
")->fetchAll();

if (empty($usuarios)) {
    logMsg('No hay usuarios admin/editor activos.');
    exit(0);
}

logMsg('Usuarios a notificar: ' . count($usuarios));

// ============================================================
// 2. Publicaciones de HOY (que aún no se han publicado)
// ============================================================
$hoy = db()->query("
    SELECT id_publicacion, titulo, fecha_publicacion, estado
    FROM gestion_publicaciones
    WHERE fecha_publicacion::date = CURRENT_DATE
      AND estado IN ('Borrador','Programado','Aprobado')
    ORDER BY fecha_publicacion
")->fetchAll();

logMsg('Publicaciones de HOY: ' . count($hoy));

foreach ($hoy as $pub) {
    foreach ($usuarios as $u) {
        // Evitar duplicados: verificar si ya existe una notificación
        // igual creada hoy
        $st = db()->prepare("
            SELECT 1 FROM gestion_notificaciones
            WHERE id_usuario = ?
              AND id_publicacion = ?
              AND tipo_notificacion = 'recordatorio_hoy'
              AND creado_en::date = CURRENT_DATE
            LIMIT 1
        ");
        $st->execute([$u['id_usuario'], $pub['id_publicacion']]);

        if ($st->fetchColumn()) continue; // Ya existe

        $hora = $pub['fecha_publicacion']
              ? date('H:i', strtotime($pub['fecha_publicacion']))
              : 'sin hora';

        db()->prepare("
            INSERT INTO gestion_notificaciones
                (id_usuario, id_publicacion, mensaje, tipo_notificacion)
            VALUES (?, ?, ?, 'recordatorio_hoy')
        ")->execute([
            $u['id_usuario'],
            $pub['id_publicacion'],
            '📅 HOY se publica: "' . $pub['titulo'] . '" a las ' . $hora
        ]);
    }
}

// ============================================================
// 3. Publicaciones de MAÑANA
// ============================================================
$manana = db()->query("
    SELECT id_publicacion, titulo, fecha_publicacion, estado
    FROM gestion_publicaciones
    WHERE fecha_publicacion::date = (CURRENT_DATE + INTERVAL '1 day')
      AND estado IN ('Borrador','Programado','Aprobado')
    ORDER BY fecha_publicacion
")->fetchAll();

logMsg('Publicaciones de MAÑANA: ' . count($manana));

foreach ($manana as $pub) {
    foreach ($usuarios as $u) {
        // Evitar duplicados
        $st = db()->prepare("
            SELECT 1 FROM gestion_notificaciones
            WHERE id_usuario = ?
              AND id_publicacion = ?
              AND tipo_notificacion = 'recordatorio_manana'
              AND creado_en::date = CURRENT_DATE
            LIMIT 1
        ");
        $st->execute([$u['id_usuario'], $pub['id_publicacion']]);

        if ($st->fetchColumn()) continue;

        $hora = $pub['fecha_publicacion']
              ? date('H:i', strtotime($pub['fecha_publicacion']))
              : 'sin hora';

        db()->prepare("
            INSERT INTO gestion_notificaciones
                (id_usuario, id_publicacion, mensaje, tipo_notificacion)
            VALUES (?, ?, ?, 'recordatorio_manana')
        ")->execute([
            $u['id_usuario'],
            $pub['id_publicacion'],
            '⏰ MAÑANA se publica: "' . $pub['titulo'] . '" a las ' . $hora
        ]);
    }
}

logMsg('=== Fin de generación ===');