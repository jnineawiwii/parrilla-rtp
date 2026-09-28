ALTER TABLE gestion_publicaciones
    ADD COLUMN IF NOT EXISTS tipo_archivo VARCHAR(20),
    ADD COLUMN IF NOT EXISTS url_material VARCHAR(500);