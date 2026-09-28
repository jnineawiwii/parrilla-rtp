<?php
class UsuarioController {
    public function index(): void {
        Auth::require('usuarios.ver');
        $usuarios = Usuario::listar();
        Response::view('usuarios/index', compact('usuarios'));
    }

    public function crear(): void {
        Auth::require('usuarios.editar');
        Response::view('usuarios/form', ['usuario' => null]);
    }

    public function editar(int $id): void {
        Auth::require('usuarios.editar');
        $u = Usuario::porId($id);
        if (!$u) {
            http_response_code(404);
            require APP_ROOT . '/app/views/errors/404.php';
            return;
        }
        Response::view('usuarios/form', ['usuario' => $u]);
    }

    public function guardar(): void {
        Auth::require('usuarios.editar');
        Csrf::validar($_POST['_csrf'] ?? null);

        $id = (int)($_POST['id'] ?? 0);
        $datos = [
            'num_credencial'     => trim($_POST['num_credencial'] ?? ''),
            'nombre_usuario'     => trim($_POST['nombre_usuario'] ?? ''),
            'correo_electronico' => trim($_POST['correo_electronico'] ?? ''),
            'nombre_completo'    => trim($_POST['nombre_completo'] ?? ''),
            'rol'                => $_POST['rol'] ?? 'lector',
            'password'           => $_POST['password'] ?? '',
        ];

        if ($id > 0) {
            Usuario::actualizar($id, $datos);
        } else {
            // Si no dan num_credencial, autogeneramos uno
            if (empty($datos['num_credencial'])) {
                $datos['num_credencial'] = 'USR-' . time();
            }
            // Si no dan nombre_usuario, derivar del correo
            if (empty($datos['nombre_usuario'])) {
                $datos['nombre_usuario'] = strstr($datos['correo_electronico'], '@', true) ?: ('user' . time());
            }
            Usuario::crear($datos);
        }
        $_SESSION['flash_ok'] = 'Usuario guardado.';
        Response::redirect('/usuarios');
    }

    public function eliminar(int $id): void {
        Auth::require('usuarios.editar');
        Csrf::validar($_POST['_csrf'] ?? null);

        if ($id === (int)Auth::user()['id']) {
            $_SESSION['flash_error'] = 'No puedes eliminar tu propio usuario.';
            Response::redirect('/usuarios');
        }
        Usuario::eliminar($id);
        Response::redirect('/usuarios');
    }
}