<?php
class Usuario {
    /** Login: busca por correo_electronico */
    public static function porCorreo(string $correo): ?array {
        $st = db()->prepare("
            SELECT * FROM gestion_usuarios
            WHERE correo_electronico = :c
            LIMIT 1
        ");
        $st->execute([':c' => $correo]);
        return $st->fetch() ?: null;
    }

    public static function porId(int $id): ?array {
        $st = db()->prepare("SELECT * FROM gestion_usuarios WHERE id_usuario = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function listar(): array {
        return db()->query("
            SELECT id_usuario, num_credencial, nombre_usuario, correo_electronico,
                   nombre_completo, rol, creado_en
            FROM gestion_usuarios
            ORDER BY nombre_completo
        ")->fetchAll();
    }

    public static function crear(array $d): int {
        $st = db()->prepare("
            INSERT INTO gestion_usuarios
                (num_credencial, nombre_usuario, contrasena,
                 correo_electronico, nombre_completo, rol)
            VALUES
                (:cred, :user, :pass, :correo, :nombre, :rol)
            RETURNING id_usuario
        ");
        $st->execute([
            ':cred'   => $d['num_credencial'] ?? ('USR-' . time()),
            ':user'   => $d['nombre_usuario'],
            ':pass'   => password_hash($d['password'], PASSWORD_BCRYPT),
            ':correo' => $d['correo_electronico'],
            ':nombre' => $d['nombre_completo'],
            ':rol'    => $d['rol'] ?? 'lector',
        ]);
        return (int)$st->fetchColumn();
    }

    public static function actualizar(int $id, array $d): void {
        $sets = [
            'nombre_usuario = :user',
            'correo_electronico = :correo',
            'nombre_completo = :nombre',
            'rol = :rol',
        ];
        $params = [
            ':id'     => $id,
            ':user'   => $d['nombre_usuario'],
            ':correo' => $d['correo_electronico'],
            ':nombre' => $d['nombre_completo'],
            ':rol'    => $d['rol'],
        ];
        if (!empty($d['password'])) {
            $sets[] = 'contrasena = :pass';
            $params[':pass'] = password_hash($d['password'], PASSWORD_BCRYPT);
        }
        $st = db()->prepare("
            UPDATE gestion_usuarios SET " . implode(', ', $sets) . "
            WHERE id_usuario = :id
        ");
        $st->execute($params);
    }

    public static function eliminar(int $id): void {
        db()->prepare("DELETE FROM gestion_usuarios WHERE id_usuario = ?")->execute([$id]);
    }
}