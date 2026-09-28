<?php
class Mensaje {

    /**
     * Envía un mensaje. $adjunto es opcional:
     *   [ 'nombre' => ..., 'ruta' => ..., 'mime' => ..., 'tamanio' => ... ]
     */
    public static function enviar(int $de, int $para, string $asunto, string $contenido, ?array $adjunto = null): int {
        $st = db()->prepare("
            INSERT INTO gestion_mensajes
                (id_remitente, id_destinatario, asunto, contenido,
                 adjunto_nombre, adjunto_ruta, adjunto_mime, adjunto_tamanio)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            RETURNING id_mensaje
        ");
        $st->execute([
            $de, $para, $asunto, $contenido,
            $adjunto['nombre']  ?? null,
            $adjunto['ruta']    ?? null,
            $adjunto['mime']    ?? null,
            $adjunto['tamanio'] ?? null,
        ]);
        $id = (int)$st->fetchColumn();

        // Notificación al destinatario
        Notificacion::crear(
            $para,
            'mensaje',
            'Nuevo mensaje: ' . mb_substr($asunto ?: $contenido, 0, 60)
        );

        return $id;
    }

    public static function recibidos(int $uid): array {
        $st = db()->prepare("
            SELECT m.*, u.nombre_completo AS remitente_nombre,
                   u.correo_electronico AS remitente_correo
            FROM gestion_mensajes m
            JOIN gestion_usuarios u ON u.id_usuario = m.id_remitente
            WHERE m.id_destinatario = ?
            ORDER BY m.id_mensaje DESC
        ");
        $st->execute([$uid]);
        return $st->fetchAll();
    }

    public static function enviados(int $uid): array {
        $st = db()->prepare("
            SELECT m.*, u.nombre_completo AS destinatario_nombre,
                   u.correo_electronico AS destinatario_correo
            FROM gestion_mensajes m
            JOIN gestion_usuarios u ON u.id_usuario = m.id_destinatario
            WHERE m.id_remitente = ?
            ORDER BY m.id_mensaje DESC
        ");
        $st->execute([$uid]);
        return $st->fetchAll();
    }

    public static function obtener(int $id, int $uid): ?array {
        $st = db()->prepare("
            SELECT m.*,
                   ue.nombre_completo AS remitente_nombre,
                   ue.correo_electronico AS remitente_correo,
                   ud.nombre_completo AS destinatario_nombre,
                   ud.correo_electronico AS destinatario_correo
            FROM gestion_mensajes m
            JOIN gestion_usuarios ue ON ue.id_usuario = m.id_remitente
            JOIN gestion_usuarios ud ON ud.id_usuario = m.id_destinatario
            WHERE m.id_mensaje = ?
              AND (m.id_remitente = ? OR m.id_destinatario = ?)
        ");
        $st->execute([$id, $uid, $uid]);
        return $st->fetch() ?: null;
    }

    /**
     * Guarda un archivo subido y devuelve un array listo para pasar a enviar().
     * Devuelve null si no hay archivo o si falla.
     */
    public static function procesarAdjunto(array $file): ?array {
        if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir el archivo (código ' . $file['error'] . ')');
        }

        // Tamaño máximo 10 MB
        $maxBytes = 10 * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            throw new RuntimeException('El archivo excede los 10 MB permitidos.');
        }

        // Nombre seguro
        $nombreOriginal = basename($file['name']);
        $ext            = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $nombreSeguro   = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $destino = APP_ROOT . '/public/uploads/mensajes/' . $nombreSeguro;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            throw new RuntimeException('No se pudo guardar el archivo en el servidor.');
        }

        // Detectar mime real
        $mime = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($destino) ?: $mime;
        }

        return [
            'nombre'  => $nombreOriginal,
            'ruta'    => '/uploads/mensajes/' . $nombreSeguro,
            'mime'    => $mime,
            'tamanio' => (int)$file['size'],
        ];
    }
}