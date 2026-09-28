<?php
class Subtema {
    public static function listar(?int $idTema = null): array {
        if ($idTema) {
            $st = db()->prepare("
                SELECT * FROM gestion_subtemas
                WHERE id_tema = ?
                ORDER BY nombre_subtema
            ");
            $st->execute([$idTema]);
            return $st->fetchAll();
        }
        return db()->query("
            SELECT * FROM gestion_subtemas ORDER BY nombre_subtema
        ")->fetchAll();
    }

    public static function porId(int $id): ?array {
        $st = db()->prepare("SELECT * FROM gestion_subtemas WHERE id_subtema = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
}