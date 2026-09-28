<?php
class MensajeController {

        public function index(): void {
        Auth::require('mensajes.usar');
        $uid = (int)Auth::user()['id'];

        $recibidos = Mensaje::recibidos($uid);
        $enviados  = Mensaje::enviados($uid);
        $usuarios  = Usuario::listar();

        Response::view('mensajes/index', compact('recibidos', 'enviados', 'usuarios', 'uid'));
    }

    public function ver(int $id): void {
        Auth::require('mensajes.usar');
        $uid = (int)Auth::user()['id'];
        $mensaje = Mensaje::obtener($id, $uid);
        if (!$mensaje) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }
        Response::view('mensajes/ver', compact('mensaje'));
    }

    public function enviar(): void {
        Auth::require('mensajes.usar');
        Csrf::validar($_POST['_csrf'] ?? null);

        $de        = (int)Auth::user()['id'];
        $para      = (int)($_POST['id_destinatario'] ?? 0);
        $asunto    = trim($_POST['asunto'] ?? '(Sin asunto)');
        $contenido = trim($_POST['contenido'] ?? '');

        if ($para <= 0 || $contenido === '') {
            $_SESSION['flash_error'] = 'Destinatario y contenido son obligatorios.';
            Response::redirect('/mensajes');
        }

        try {
            $adjunto = null;
            if (!empty($_FILES['adjunto']['name'])) {
                $adjunto = Mensaje::procesarAdjunto($_FILES['adjunto']);
            }
            Mensaje::enviar($de, $para, $asunto, $contenido, $adjunto);
            $_SESSION['flash_ok'] = $adjunto
                ? 'Mensaje enviado con adjunto.'
                : 'Mensaje enviado.';
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Error al enviar: ' . $e->getMessage();
        }
        Response::redirect('/mensajes');
    }
}