<?php
class Plataforma {
    public static function listar(): array {
        return db()->query("
            SELECT * FROM gestion_plataformas
            WHERE activo = TRUE
            ORDER BY nombre_plataforma
        ")->fetchAll();
    }

    public static function porId(int $id): ?array {
        $st = db()->prepare("SELECT * FROM gestion_plataformas WHERE id_plataforma = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
}