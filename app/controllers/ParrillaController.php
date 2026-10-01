<?php
class ParrillaController {

    public function index(): void {
        Auth::require('parrilla.ver');

        // Obtenemos los datos del usuario logueado
        $user = Auth::user();
        $rol  = strtolower($user['rol']); // <-- NORMALIZAMOS A MINÚSCULAS
        $uid  = (int)$user['id'];

        $filtros = [
            'mes'     => $_GET['mes']     ?? null,
            'anio'    => $_GET['anio']    ?? date('Y'),
            'campana' => $_GET['campana'] ?? null,
            'estado'  => $_GET['estado']  ?? null,
            'q'       => $_GET['q']       ?? null,
        ];

        // LÓGICA DE VISIBILIDAD POR ROL
        if ($rol === 'editor') {
            // Los editores ven sus proyectos donde son responsables O postproducción
            $filtros['id_usuario_asociado'] = $uid;
        } else {
            // Admin y Lector ven TODOS los proyectos
            $filtros['id_usuario_asociado'] = null;
        }

        $pag = max(1, (int)($_GET['pag'] ?? 1));

        $registros = Publicacion::listar($filtros, $pag, 25);
        $campanas  = Campana::listar();

        Response::view('parrilla/index', compact('registros', 'campanas', 'filtros', 'pag'));
    }

    public function crear(): void {
        Auth::require('parrilla.crear');

        Response::view('parrilla/form', [
            'registro'    => null,
            'campanas'    => Campana::listar(),
            'temas'       => Tema::listar(),
            'subtemas'    => Subtema::listar(),
            'formatos'    => Formato::listar(),
            'plataformas' => Plataforma::listar(),
            'responsables'=> Usuario::listar(),
        ]);
    }

    public function editar(int $id): void {
        Auth::require('parrilla.editar');
        $registro = Publicacion::obtener($id);
        if (!$registro) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }

        Response::view('parrilla/form', [
            'registro'    => $registro,
            'campanas'    => Campana::listar(),
            'temas'       => Tema::listar(),
            'subtemas'    => Subtema::listar(),
            'formatos'    => Formato::listar(),
            'plataformas' => Plataforma::listar(),
            'responsables'=> Usuario::listar(),
        ]);
    }

    public function guardar(): void {
        Auth::require('parrilla.crear');
        Csrf::validar($_POST['_csrf'] ?? null);

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Auth::require('parrilla.editar');
        }
        $datos = $this->recolectar($_POST);

        try {
            $archivo = $this->procesarArchivoMaterial($_FILES['archivo_material'] ?? []);
            if ($archivo !== null) {
                $datos['ruta_archivo'] = $archivo['ruta'];
                $datos['tipo_archivo'] = $archivo['tipo'];
            } elseif ($id > 0) {
                $actual = Publicacion::obtener($id);
                $datos['ruta_archivo'] = $actual['ruta_archivo'] ?? null;
                $datos['tipo_archivo'] = $actual['tipo_archivo'] ?? null;
            }
        } catch (RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            Response::redirect($id > 0 ? '/parrilla/editar/' . $id : '/parrilla/crear');
        }

        if ($id > 0) {
            Publicacion::actualizar($id, $datos);
        } else {
            $id = Publicacion::crear($datos, (int)Auth::user()['id']);
        }

        // Guardar métricas si vienen
        if (!empty($_POST['metrica'])) {
            foreach ($_POST['metrica'] as $red => $m) {
                // Solo guardamos si hay algún dato > 0
                $suma = array_sum(array_map('intval', $m));
                if ($suma > 0) {
                    Metrica::guardar($id, $red, $m);
                }
            }
        }

        $_SESSION['flash_ok'] = 'Publicación guardada correctamente.';
        Response::redirect('/parrilla/ver/' . $id);
    }

    public function ver(int $id): void {
        Auth::require('parrilla.ver');
        $registro = Publicacion::obtener($id);
        if (!$registro) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }
        $metricas = Metrica::porPublicacion($id);
        Response::view('parrilla/ver', compact('registro', 'metricas'));
    }

    public function eliminar(int $id): void {
        Auth::require('parrilla.borrar');
        Csrf::validar($_POST['_csrf'] ?? null);
        Publicacion::eliminar($id);
        $_SESSION['flash_ok'] = 'Publicación eliminada.';
        Response::redirect('/parrilla');
    }

    private function recolectar(array $p): array {
        return [
            'titulo'                    => trim($p['titulo']),
            'descripcion'               => $p['descripcion'] ?? null,
            'copy'                      => $p['copy_in'] ?? null,
            'fecha_publicacion'         => !empty($p['fecha'])
                                            ? ($p['fecha'] . ' ' . ($p['hora'] ?: '00:00:00'))
                                            : null,
            'dia_semana'                => $p['dia_semana'] ?? null,
            'id_campana'                => $p['campana_id'] ?: null,
            'id_tema'                   => $p['tema_id'] ?: null,
            'id_subtema'                => $p['subtema_id'] ?: null,
            'id_formato_contenido'      => $p['formato_id'] ?: null,
            'id_usuario_responsable'    => $p['encargado_produccion'] ?: null,
            'id_usuario_produccion'     => $p['encargado_produccion'] ?: null,
            'id_usuario_postproduccion' => $p['encargado_edicion'] ?: null,
            'estado'                    => $p['estado'] ?? 'Borrador',
            'notas'                     => $p['idea'] ?? null,
            'ruta_archivo'              => null,
            'url_material'              => trim($p['link_material'] ?? '') ?: null,
        ];
    }

    private function procesarArchivoMaterial(array $archivo): ?array {
        if (empty($archivo) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo recibir el archivo. Intenta nuevamente.');
        }

        $maxBytes = 50 * 1024 * 1024;
        if (($archivo['size'] ?? 0) > $maxBytes) {
            throw new RuntimeException('El archivo no puede superar los 50 MB.');
        }

        $tiposPermitidos = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
            'image/gif' => ['gif'],
            'video/mp4' => ['mp4'],
            'video/webm' => ['webm'],
        ];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);
        $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
        if (!isset($tiposPermitidos[$mime]) || !in_array($extension, $tiposPermitidos[$mime], true)) {
            throw new RuntimeException('Formato no permitido. Sube una imagen JPG, PNG, WebP o GIF, o un video MP4/WebM.');
        }

        $directorio = APP_ROOT . '/public/uploads/material';
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo preparar la carpeta de archivos.');
        }
        $nombreSeguro = bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($archivo['tmp_name'], $directorio . '/' . $nombreSeguro)) {
            throw new RuntimeException('No se pudo guardar el archivo en el servidor.');
        }

        return [
            'ruta' => '/uploads/material/' . $nombreSeguro,
            'tipo' => str_starts_with($mime, 'image/') ? 'imagen' : 'video',
        ];
    }

    public function aprobar(int $id): void {
    Auth::require('parrilla.aprobar');
    Csrf::validar($_POST['_csrf'] ?? null);

    db()->prepare("
        UPDATE gestion_publicaciones
        SET estado = 'Aprobado',
            actualizado_en = CURRENT_TIMESTAMP
        WHERE id_publicacion = ?
    ")->execute([$id]);

    // Notificar al responsable
    $pub = Publicacion::obtener($id);
    if ($pub && $pub['id_usuario_responsable']) {
        Notificacion::crear(
            (int)$pub['id_usuario_responsable'],
            'sistema',
            '✅ Tu publicación "' . $pub['titulo'] . '" fue aprobada.',
            $id
        );
    }

    $_SESSION['flash_ok'] = '✅ Publicación aprobada.';
    Response::redirect('/parrilla/ver/' . $id);
}

public function rechazar(int $id): void {
    Auth::require('parrilla.rechazar');
    Csrf::validar($_POST['_csrf'] ?? null);

    $motivo = trim($_POST['motivo'] ?? '');

    db()->prepare("
        UPDATE gestion_publicaciones
        SET estado = 'Cancelado',
            notas = COALESCE(notas, '') || E'\n--- Rechazado ---\n' || :motivo,
            actualizado_en = CURRENT_TIMESTAMP
        WHERE id_publicacion = ?
    ")->execute([':motivo' => $motivo, ':id' => $id]);

    // Notificar
    $pub = Publicacion::obtener($id);
    if ($pub && $pub['id_usuario_responsable']) {
        $msg = '❌ Tu publicación "' . $pub['titulo'] . '" fue rechazada.';
        if ($motivo) $msg .= ' Motivo: ' . mb_substr($motivo, 0, 100);
        Notificacion::crear(
            (int)$pub['id_usuario_responsable'],
            'sistema',
            $msg,
            $id
        );
    }

    $_SESSION['flash_ok'] = '❌ Publicación rechazada.';
    Response::redirect('/parrilla/ver/' . $id);
}
}