<?php
class Tema {
    public static function listar(?int $idCampana = null): array {
        if ($idCampana) {
            $st = db()->prepare("
                SELECT * FROM gestion_temas
                WHERE id_campana = ?
                ORDER BY nombre_tema
            ");
            $st->execute([$idCampana]);
            return $st->fetchAll();
        }
        return db()->query("
            SELECT * FROM gestion_temas ORDER BY nombre_tema
        ")->fetchAll();
    }

    public static function porId(int $id): ?array {
        $st = db()->prepare("SELECT * FROM gestion_temas WHERE id_tema = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
}