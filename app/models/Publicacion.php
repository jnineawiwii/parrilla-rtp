<?php
class Publicacion {

    public static function listar(array $f = [], int $pag = 1, int $porPag = 25): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($f['mes'])) {
            $where[] = "EXTRACT(MONTH FROM p.fecha_publicacion) = :mes";
            $params[':mes'] = $f['mes'];
        }
        if (!empty($f['anio'])) {
            $where[] = "EXTRACT(YEAR FROM p.fecha_publicacion) = :anio";
            $params[':anio'] = $f['anio'];
        }
        if (!empty($f['campana'])) {
            $where[] = "p.id_campana = :cid";
            $params[':cid'] = $f['campana'];
        }
        if (!empty($f['estado'])) {
            $where[] = "p.estado = :est";
            $params[':est'] = $f['estado'];
        }
        if (!empty($f['q'])) {
            $where[] = "(p.titulo ILIKE :q OR p.descripcion ILIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }

        $sql = "SELECT p.*,
                       c.nombre_campana        AS campana_nombre,
                       c.color                 AS campana_color,
                       f.nombre_formato        AS formato_nombre,
                       t.nombre_tema           AS tema_nombre,
                       s.nombre_subtema        AS subtema_nombre
                FROM gestion_publicaciones p
                LEFT JOIN gestion_campanas           c ON c.id_campana = p.id_campana
                LEFT JOIN gestion_formatos_contenido f ON f.id_formato = p.id_formato_contenido
                LEFT JOIN gestion_temas              t ON t.id_tema    = p.id_tema
                LEFT JOIN gestion_subtemas           s ON s.id_subtema = p.id_subtema
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.fecha_publicacion DESC NULLS LAST, p.id_publicacion DESC
                LIMIT :lim OFFSET :off";

        $params[':lim'] = $porPag;
        $params[':off'] = ($pag - 1) * $porPag;

        $st = db()->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->execute();
        return $st->fetchAll();
    }

    public static function obtener(int $id): ?array {
        $st = db()->prepare("
            SELECT p.*,
                   c.nombre_campana  AS campana_nombre,
                   c.color           AS campana_color,
                   f.nombre_formato  AS formato_nombre,
                   t.nombre_tema     AS tema_nombre,
                   s.nombre_subtema  AS subtema_nombre,
                   ur.nombre_completo AS usuario_responsable,
                   up.nombre_completo AS usuario_produccion,
                   uo.nombre_completo AS usuario_postproduccion
            FROM gestion_publicaciones p
            LEFT JOIN gestion_campanas           c ON c.id_campana = p.id_campana
            LEFT JOIN gestion_formatos_contenido f ON f.id_formato = p.id_formato_contenido
            LEFT JOIN gestion_temas              t ON t.id_tema    = p.id_tema
            LEFT JOIN gestion_subtemas           s ON s.id_subtema = p.id_subtema
            LEFT JOIN gestion_usuarios           ur ON ur.id_usuario = p.id_usuario_responsable
            LEFT JOIN gestion_usuarios           up ON up.id_usuario = p.id_usuario_produccion
            LEFT JOIN gestion_usuarios           uo ON uo.id_usuario = p.id_usuario_postproduccion
            WHERE p.id_publicacion = ?
        ");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function crear(array $d, int $uid): int {
        $st = db()->prepare("
            INSERT INTO gestion_publicaciones
              (titulo, descripcion, texto_publicitario, fecha_publicacion, dia_semana,
               id_campana, id_tema, id_subtema, id_formato_contenido,
               id_usuario_responsable, id_usuario_produccion, id_usuario_postproduccion,
               estado, notas, ruta_archivo, tipo_archivo, url_material)
            VALUES
              (:titulo, :desc, :copy, :fecha, :dia,
               :cid, :tid, :sid, :fid,
               :uresp, :uprod, :upost,
               :estado, :notas, :ruta, :tipo_archivo, :url_material)
            RETURNING id_publicacion
        ");
        $st->execute([
            ':titulo' => $d['titulo'],
            ':desc'   => $d['descripcion'] ?? null,
            ':copy'   => $d['texto_publicitario'] ?? null,
            ':fecha'  => $d['fecha_publicacion'] ?: null,
            ':dia'    => $d['dia_semana'] ?? null,
            ':cid'    => $d['id_campana'] ?: null,
            ':tid'    => $d['id_tema'] ?: null,
            ':sid'    => $d['id_subtema'] ?: null,
            ':fid'    => $d['id_formato_contenido'] ?: null,
            ':uresp'  => $d['id_usuario_responsable'] ?: $uid,
            ':uprod'  => $d['id_usuario_produccion'] ?: null,
            ':upost'  => $d['id_usuario_postproduccion'] ?: null,
            ':estado' => $d['estado'] ?? 'Borrador',
            ':notas'  => $d['notas'] ?? null,
            ':ruta'   => $d['ruta_archivo'] ?? null,
            ':tipo_archivo' => $d['tipo_archivo'] ?? null,
            ':url_material' => $d['url_material'] ?? null,
        ]);
        return (int)$st->fetchColumn();
    }

    public static function actualizar(int $id, array $d): void {
        $st = db()->prepare("
            UPDATE gestion_publicaciones SET
                titulo                    = :titulo,
                descripcion               = :desc,
                texto_publicitario        = :copy,
                fecha_publicacion         = :fecha,
                dia_semana                = :dia,
                id_campana                = :cid,
                id_tema                   = :tid,
                id_subtema                = :sid,
                id_formato_contenido      = :fid,
                id_usuario_responsable    = :uresp,
                id_usuario_produccion     = :uprod,
                id_usuario_postproduccion = :upost,
                estado                    = :estado,
                notas                     = :notas,
                ruta_archivo              = :ruta,
                tipo_archivo              = :tipo_archivo,
                url_material              = :url_material,
                actualizado_en            = CURRENT_TIMESTAMP
            WHERE id_publicacion = :id
        ");
        $st->execute([
            ':id'     => $id,
            ':titulo' => $d['titulo'],
            ':desc'   => $d['descripcion'] ?? null,
            ':copy'   => $d['texto_publicitario'] ?? null,
            ':fecha'  => $d['fecha_publicacion'] ?: null,
            ':dia'    => $d['dia_semana'] ?? null,
            ':cid'    => $d['id_campana'] ?: null,
            ':tid'    => $d['id_tema'] ?: null,
            ':sid'    => $d['id_subtema'] ?: null,
            ':fid'    => $d['id_formato_contenido'] ?: null,
            ':uresp'  => $d['id_usuario_responsable'] ?: null,
            ':uprod'  => $d['id_usuario_produccion'] ?: null,
            ':upost'  => $d['id_usuario_postproduccion'] ?: null,
            ':estado' => $d['estado'] ?? 'Borrador',
            ':notas'  => $d['notas'] ?? null,
            ':ruta'   => $d['ruta_archivo'] ?? null,
            ':tipo_archivo' => $d['tipo_archivo'] ?? null,
            ':url_material' => $d['url_material'] ?? null,
        ]);
    }

    public static function eliminar(int $id): void {
        db()->prepare("DELETE FROM gestion_publicaciones WHERE id_publicacion = ?")->execute([$id]);
    }

    public static function deHoy(): array {
        $st = db()->query("
            SELECT * FROM gestion_publicaciones
            WHERE fecha_publicacion::date = CURRENT_DATE
              AND estado IN ('Programado','Aprobado','Borrador')
            ORDER BY fecha_publicacion
        ");
        return $st->fetchAll();
    }

    public static function deManana(): array {
        $st = db()->query("
            SELECT * FROM gestion_publicaciones
            WHERE fecha_publicacion::date = (CURRENT_DATE + INTERVAL '1 day')
              AND estado IN ('Programado','Aprobado','Borrador')
            ORDER BY fecha_publicacion
        ");
        return $st->fetchAll();
    }
}