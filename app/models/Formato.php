<?php
class Formato {
    public static function listar(): array {
        return db()->query("
            SELECT * FROM gestion_formatos_contenido
            ORDER BY nombre_formato
        ")->fetchAll();
    }

    public static function porId(int $id): ?array {
        $st = db()->prepare("SELECT * FROM gestion_formatos_contenido WHERE id_formato = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
}