<?php
class AuthController {
    public function mostrarLogin(): void {
        if (Auth::check()) Response::redirect('/parrilla');
        require APP_ROOT . '/app/views/auth/login.php';
    }

    public function login(): void {
        Csrf::validar($_POST['_csrf'] ?? null);
        $correo = trim($_POST['correo'] ?? '');
        $pass   = $_POST['password'] ?? '';

        $u = Usuario::porCorreo($correo);
        if (!$u || !password_verify($pass, $u['contrasena'])) {
            $_SESSION['flash_error'] = 'Credenciales incorrectas.';
            Response::redirect('/login');
        }

        // Adaptamos el array de la BD a la forma que espera Auth::login()
        Auth::login([
            'id'     => $u['id_usuario'],
            'nombre' => $u['nombre_completo'],
            'email'  => $u['correo_electronico'],
            'rol'    => $u['rol'],
        ]);

        Response::redirect('/parrilla');
    }

    public function logout(): void {
        Auth::logout();
        Response::redirect('/login');
    }
}