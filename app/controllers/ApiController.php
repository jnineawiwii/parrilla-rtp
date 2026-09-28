<?php
class ApiController {
    public function generarCopy(): void {
        Auth::require('parrilla.editar');
        try {
            $pub = $_POST;

            if (!empty($pub['id'])) {
                $guardado = Publicacion::obtener((int)$pub['id']);
                if ($guardado) $pub = array_merge($guardado, $pub);
            }

            if (!empty($pub['campana_id'])) {
                $c = Campana::porId((int)$pub['campana_id']);
                if ($c) $pub['campana_nombre'] = $c['nombre_campana'];
            }
            if (!empty($pub['tema_id'])) {
                $t = Tema::porId((int)$pub['tema_id']);
                if ($t) $pub['tema_nombre'] = $t['nombre_tema'];
            }

            $copy = IA::generarCopy($pub);
            Response::json(['ok' => true, 'copy' => $copy]);
        } catch (Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}