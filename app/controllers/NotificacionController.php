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
        return;   // ✅ sin esto, sigue y explota
    }

    $uid   = (int)Auth::user()['id'];
    $items = Notificacion::deUsuario($uid, 10);
    $noLeidas = Notificacion::noLeidas($uid);   // ✅ usa el método correcto

    $itemsJS = array_map(fn($i) => [
        'id'         => (int)$i['id'],
        'titulo'     => $i['titulo'] ?? 'sistema',
        'mensaje'    => $i['mensaje'],
        'leida'      => (bool)($i['leida'] ?? false),
        'created_at' => $i['created_at'],
    ], $items);

    Response::json(['no_leidas' => $noLeidas, 'items' => $itemsJS]);
}
}