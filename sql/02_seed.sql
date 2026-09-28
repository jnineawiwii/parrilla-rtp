-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Campañas con colores de la paleta RTP
INSERT INTO campanas (nombre, color_hex) VALUES
  ('A_su_servicio',              '#266CB4'),
  ('RTP_avanza',                 '#027A35'),
  ('RTP_siempre_contigo',        '#D72F89'),
  ('Cultura_en_el_transporte',   '#F08217'),
  ('RTP_en_directo',             '#E5074C'),
  ('Movilidad_Integrada_MI',     '#8F4889'),
  ('Subete_a_la_cultura',        '#AC6D14'),
  ('Covid_19',                   '#55585A');

-- Formatos
INSERT INTO formatos (nombre) VALUES
  ('Fijo'), ('Reel'), ('Video'), ('Historia 24h'),
  ('Fotografía'), ('Ilustración'), ('Animación'),
  ('Infografía'), ('Composición'), ('Mapa'),
  ('Boletín / Tarjeta informativa'), ('Evento'),
  ('Texto'), ('PDF'), ('Página web'), ('Gif'), ('Impreso'), ('En vivo');

-- Plataformas
INSERT INTO plataformas (nombre) VALUES
  ('TODAS'), ('Meta'), ('X'), ('YouTube'), ('Facebook'),
  ('Instagram'), ('X e Instagram'), ('X e Instagram Facebook');

-- Responsables
INSERT INTO responsables (nombre) VALUES
  ('Alexander'), ('Teresa'), ('Ximena'), ('Esther'),
  ('Juan Carlos'), ('Fabiola'), ('Servicio Social'),
  ('Karen'), ('Joaquín'), ('Mónica'), ('Diego'), ('Benjamín');

-- Temas por campaña
INSERT INTO temas (campana_id, nombre) VALUES
  ((SELECT id FROM campanas WHERE nombre='A_su_servicio'),            'RTP Informa'),
  ((SELECT id FROM campanas WHERE nombre='A_su_servicio'),            'Modalidades de servicio'),
  ((SELECT id FROM campanas WHERE nombre='A_su_servicio'),            'Servicios Especiales'),
  ((SELECT id FROM campanas WHERE nombre='A_su_servicio'),            'Mapas'),
  ((SELECT id FROM campanas WHERE nombre='A_su_servicio'),            'Estadística'),
  ((SELECT id FROM campanas WHERE nombre='A_su_servicio'),            'Atención Ciudadana'),
  ((SELECT id FROM campanas WHERE nombre='RTP_avanza'),               'Nuevos autobuses'),
  ((SELECT id FROM campanas WHERE nombre='RTP_avanza'),               'Nuevas tecnologías'),
  ((SELECT id FROM campanas WHERE nombre='RTP_avanza'),               'Desarrollo de nuevos proyectos'),
  ((SELECT id FROM campanas WHERE nombre='RTP_siempre_contigo'),      'Historias de personas'),
  ((SELECT id FROM campanas WHERE nombre='RTP_siempre_contigo'),      'Memes'),
  ((SELECT id FROM campanas WHERE nombre='RTP_siempre_contigo'),      'Dinámicas'),
  ((SELECT id FROM campanas WHERE nombre='RTP_siempre_contigo'),      'Datos curiosos de la red'),
  ((SELECT id FROM campanas WHERE nombre='RTP_siempre_contigo'),      'RTP en el tiempo'),
  ((SELECT id FROM campanas WHERE nombre='RTP_siempre_contigo'),      'Fechas importantes'),
  ((SELECT id FROM campanas WHERE nombre='RTP_siempre_contigo'),      'Fans'),
  ((SELECT id FROM campanas WHERE nombre='RTP_siempre_contigo'),      'Postales'),
  ((SELECT id FROM campanas WHERE nombre='Cultura_en_el_transporte'), 'Cultura Cívica'),
  ((SELECT id FROM campanas WHERE nombre='Cultura_en_el_transporte'), 'Cuidado del transporte'),
  ((SELECT id FROM campanas WHERE nombre='Cultura_en_el_transporte'), 'Derechos Humanos'),
  ((SELECT id FROM campanas WHERE nombre='Cultura_en_el_transporte'), 'Personas con discapacidad'),
  ((SELECT id FROM campanas WHERE nombre='Cultura_en_el_transporte'), 'Violencia de género'),
  ((SELECT id FROM campanas WHERE nombre='Cultura_en_el_transporte'), 'Conciencia social'),
  ((SELECT id FROM campanas WHERE nombre='Cultura_en_el_transporte'), 'Tequios'),
  ((SELECT id FROM campanas WHERE nombre='RTP_en_directo'),           'Actividades DG'),
  ((SELECT id FROM campanas WHERE nombre='RTP_en_directo'),           'Mensajes DG'),
  ((SELECT id FROM campanas WHERE nombre='Movilidad_Integrada_MI'),   'Contenido movilidad'),
  ((SELECT id FROM campanas WHERE nombre='Movilidad_Integrada_MI'),   'Informes Institucionales'),
  ((SELECT id FROM campanas WHERE nombre='Movilidad_Integrada_MI'),   'Otros');

-- ============================================================
-- USUARIOS INICIALES
-- Contraseñas:
--   admin@rtp.cdmx.gob.mx   → Admin123!
--   editor@rtp.cdmx.gob.mx  → Editor123!
--   lector@rtp.cdmx.gob.mx  → Lector123!
-- ============================================================
INSERT INTO usuarios (nombre, email, password_hash, rol, activo) VALUES
('Administrador RTP', 'admin@rtp.cdmx.gob.mx',
 '$2y$10$xbg56oOETXeWoafJPxHXgOu4zBgNrFcVPI7EjWDQMkZDeW5rPKJoC', 'admin', TRUE),
('Editor de Prueba', 'editor@rtp.cdmx.gob.mx',
 '$2y$10$BUBjzJzOmemT3mLQFqZ7i.u.elEJPAIShlR.PdD7YjQTtga6QAYkK', 'editor', TRUE),
('Lector de Prueba', 'lector@rtp.cdmx.gob.mx',
 '$2y$10$Koy9lPxA9868mOitY3JSP.Rh89H7WlOoMJzMkkNhzKAksrwMYrCAa', 'lector', TRUE);