<?php
class Campana {
    public static function listar(): array {
        return db()->query("
            SELECT * FROM gestion_campanas
            WHERE activo = TRUE
            ORDER BY nombre_campana
        ")->fetchAll();
    }

    public static function porId(int $id): ?array {
        $st = db()->prepare("SELECT * FROM gestion_campanas WHERE id_campana = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
}