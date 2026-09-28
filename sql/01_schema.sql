-- ============================================================
-- SISTEMA DE PARRILLA DE CONTENIDOS RTP
-- PostgreSQL 12+ | Compatible con pgAdmin 4
-- ============================================================

-- ------------------------------------------------------------
-- 0. LIMPIEZA (opcional, descomentar si necesitas reiniciar)
-- ------------------------------------------------------------
-- DROP TABLE IF EXISTS ia_logs, mensajes, conversacion_participantes,
--   conversaciones, notificaciones, metricas, parrilla, subtemas, temas,
--   campanas, formatos, plataformas, responsables, usuarios CASCADE;
-- DROP TYPE IF EXISTS rol_usuario CASCADE;
-- DROP TYPE IF EXISTS estado_pub CASCADE;
-- DROP TYPE IF EXISTS tipo_notif CASCADE;

-- ------------------------------------------------------------
-- 1. TIPOS ENUMERADOS
-- ------------------------------------------------------------
CREATE TYPE rol_usuario  AS ENUM ('admin', 'editor', 'lector');
CREATE TYPE estado_pub   AS ENUM ('Programado','Publicado','Cancelado','No publicado','Aprobado');
CREATE TYPE tipo_notif   AS ENUM ('recordatorio_hoy','recordatorio_proximo','mensaje','sistema');

-- ------------------------------------------------------------
-- 2. USUARIOS
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id              SERIAL PRIMARY KEY,
    nombre          VARCHAR(120) NOT NULL,
    email           VARCHAR(150) UNIQUE NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    rol             rol_usuario  NOT NULL DEFAULT 'lector',
    activo          BOOLEAN      DEFAULT TRUE,
    avatar          VARCHAR(255),
    ultimo_acceso   TIMESTAMP,
    created_at      TIMESTAMP DEFAULT NOW(),
    updated_at      TIMESTAMP DEFAULT NOW()
);

-- ------------------------------------------------------------
-- 3. CATÁLOGOS
-- ------------------------------------------------------------
CREATE TABLE campanas (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(120) NOT NULL UNIQUE,
    color_hex   VARCHAR(7)
);

CREATE TABLE temas (
    id          SERIAL PRIMARY KEY,
    campana_id  INT REFERENCES campanas(id) ON DELETE CASCADE,
    nombre      VARCHAR(150) NOT NULL
);

CREATE TABLE subtemas (
    id       SERIAL PRIMARY KEY,
    tema_id  INT REFERENCES temas(id) ON DELETE CASCADE,
    nombre   VARCHAR(150) NOT NULL
);

CREATE TABLE formatos (
    id      SERIAL PRIMARY KEY,
    nombre  VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE plataformas (
    id      SERIAL PRIMARY KEY,
    nombre  VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE responsables (
    id      SERIAL PRIMARY KEY,
    nombre  VARCHAR(120) NOT NULL UNIQUE
);

-- ------------------------------------------------------------
-- 4. PARRILLA (tabla central)
-- ------------------------------------------------------------
CREATE TABLE parrilla (
    id                    SERIAL PRIMARY KEY,
    sem                   VARCHAR(20),
    num                   INT,
    dia_semana            VARCHAR(15),
    fecha                 DATE NOT NULL,
    hora                  TIME,
    campana_id            INT REFERENCES campanas(id),
    titulo                VARCHAR(200) NOT NULL,
    tema_id               INT REFERENCES temas(id),
    subtema_id            INT REFERENCES subtemas(id),
    plataforma_id         INT REFERENCES plataformas(id),
    formato_id            INT REFERENCES formatos(id),
    ubicacion             VARCHAR(60),
    idea                  TEXT,
    descripcion           TEXT,
    pie_publicacion       TEXT,
    copy_in               TEXT,
    copy_generado_ia      BOOLEAN DEFAULT FALSE,
    encargado_produccion  INT REFERENCES responsables(id),
    actividad             VARCHAR(120),
    encargado_edicion     INT REFERENCES responsables(id),
    tipo_material         VARCHAR(80),
    link_material         VARCHAR(500),
    estado                estado_pub DEFAULT 'Programado',
    link_facebook         VARCHAR(500),
    link_instagram        VARCHAR(500),
    link_x                VARCHAR(500),
    link_youtube          VARCHAR(500),
    creado_por            INT REFERENCES usuarios(id),
    editado_por           INT REFERENCES usuarios(id),
    created_at            TIMESTAMP DEFAULT NOW(),
    updated_at            TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_parrilla_fecha   ON parrilla(fecha);
CREATE INDEX idx_parrilla_estado  ON parrilla(estado);
CREATE INDEX idx_parrilla_campana ON parrilla(campana_id);

-- ------------------------------------------------------------
-- 5. MÉTRICAS
-- ------------------------------------------------------------
CREATE TABLE metricas (
    id                SERIAL PRIMARY KEY,
    parrilla_id       INT REFERENCES parrilla(id) ON DELETE CASCADE,
    red               VARCHAR(20) NOT NULL,
    alcance           INT DEFAULT 0,
    impresiones       INT DEFAULT 0,
    me_gusta          INT DEFAULT 0,
    comentarios       INT DEFAULT 0,
    compartidos       INT DEFAULT 0,
    interaccion       INT DEFAULT 0,
    reproducciones    INT DEFAULT 0,
    coment_positivos  INT DEFAULT 0,
    coment_negativos  INT DEFAULT 0,
    coment_neutros    INT DEFAULT 0,
    fecha_captura     DATE DEFAULT CURRENT_DATE,
    UNIQUE(parrilla_id, red)
);

-- ------------------------------------------------------------
-- 6. NOTIFICACIONES
-- ------------------------------------------------------------
CREATE TABLE notificaciones (
    id            SERIAL PRIMARY KEY,
    usuario_id    INT REFERENCES usuarios(id) ON DELETE CASCADE,
    tipo          tipo_notif NOT NULL,
    titulo        VARCHAR(180) NOT NULL,
    mensaje       TEXT,
    url_destino   VARCHAR(300),
    parrilla_id   INT REFERENCES parrilla(id) ON DELETE CASCADE,
    leida         BOOLEAN DEFAULT FALSE,
    created_at    TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_notif_usuario ON notificaciones(usuario_id, leida);

-- ------------------------------------------------------------
-- 7. MENSAJERÍA
-- ------------------------------------------------------------
CREATE TABLE conversaciones (
    id           SERIAL PRIMARY KEY,
    creador_id   INT REFERENCES usuarios(id),
    asunto       VARCHAR(180),
    created_at   TIMESTAMP DEFAULT NOW(),
    updated_at   TIMESTAMP DEFAULT NOW()
);

CREATE TABLE conversacion_participantes (
    conversacion_id INT REFERENCES conversaciones(id) ON DELETE CASCADE,
    usuario_id      INT REFERENCES usuarios(id) ON DELETE CASCADE,
    PRIMARY KEY (conversacion_id, usuario_id)
);

CREATE TABLE mensajes (
    id              SERIAL PRIMARY KEY,
    conversacion_id INT REFERENCES conversaciones(id) ON DELETE CASCADE,
    emisor_id       INT REFERENCES usuarios(id),
    cuerpo          TEXT NOT NULL,
    leido           BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_msg_conv ON mensajes(conversacion_id, created_at DESC);

-- ------------------------------------------------------------
-- 8. LOGS DE IA
-- ------------------------------------------------------------
CREATE TABLE ia_logs (
    id           SERIAL PRIMARY KEY,
    usuario_id   INT REFERENCES usuarios(id),
    prompt       TEXT,
    respuesta    TEXT,
    parrilla_id  INT REFERENCES parrilla(id),
    created_at   TIMESTAMP DEFAULT NOW()
);