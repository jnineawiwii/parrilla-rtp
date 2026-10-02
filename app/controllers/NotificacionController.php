<?php
class NotificacionController {
    public function index(): void {
        Auth::require('notificaciones.ver');
        $uid = (int)Auth::user()['id'];
        $items = Notificacion::deUsuario($uid, 100);
        Response::view('notificaciones/index', compact('items'));
    }

    public function abrir(int $id): void {
        Auth::require('notificaciones.ver');
        $uid = (int)Auth::user()['id'];
        Notificacion::marcarLeida($id, $uid);
        Response::redirect('/notificaciones');
    }

    public function marcarTodas(): void {
        Auth::require('notificaciones.ver');
        Csrf::validar($_POST['_csrf'] ?? null);
        Notificacion::marcarTodasLeidas((int)Auth::user()['id']);
        Response::redirect('/notificaciones');
    }

    public function api(): void {
        if (!Auth::check()) {
            Response::json(['no_leidas' => 0, 'items' => []]);
        }
        $uid = (int)Auth::user()['id'];
        $items = Notificacion::deUsuario($uid, 10);

        // Contar no leídas
        $noLeidas = 0;
        foreach ($items as $i) {
            if (empty($i['leida'])) {
                $noLeidas++;
            }
        }

        // Renombrar campos para el JS (nombres esperados por notificaciones.js)
        $itemsJS = array_map(function ($i) {
            return [
                'id'         => $i['id'],
                'titulo'     => $i['titulo'] ?? 'Notificación',
                'mensaje'    => $i['mensaje'],
                'leida'      => (bool)($i['leida'] ?? false),
                'created_at' => $i['created_at'],
            ];
        }, $items);

        Response::json(['no_leidas' => $noLeidas, 'items' => $itemsJS]);
    }
}