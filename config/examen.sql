-- =====================================================================
-- Esquema del examen aprobatorio de manejo
-- Base de datos: esdrive   (MySQL / WAMP: root sin contrasena)
-- La tabla se crea sola la primera vez que se usa (config/conexion.php),
-- este archivo es la referencia para integrarla en el panel admin.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `esdrive`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `esdrive`;

CREATE TABLE IF NOT EXISTS `examen_alumnos` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `nombre`            VARCHAR(120) NOT NULL,
    `dui`               VARCHAR(20)  NULL,              -- opcional, sirve de segunda validacion
    `codigo`            VARCHAR(32)  NOT NULL UNIQUE,   -- codigo de acceso que usa el alumno
    `habilitado_desde`  DATETIME     NOT NULL,          -- hora en la que se habilita el examen
    `vence`             DATETIME     NOT NULL,          -- habilitado_desde + 30 minutos
    `inicio_real`       DATETIME     NULL,              -- primer acceso real del alumno
    `estado`            ENUM('pendiente','en_curso','aprobado','reprobado','expirado')
                        NOT NULL DEFAULT 'pendiente',
    `aciertos`          INT          NOT NULL DEFAULT 0,
    `total`             INT          NOT NULL DEFAULT 50,
    `porcentaje`        DECIMAL(5,2) NOT NULL DEFAULT 0,
    `nota`              TINYINT UNSIGNED NOT NULL DEFAULT 0,   -- 0 a 100
    `respuestas`        LONGTEXT     NULL,              -- JSON {"0":2,"1":0,...}
    `pregunta_actual`   INT          NOT NULL DEFAULT 0,      -- indice de la pregunta abierta
    `terminado_en`      DATETIME     NULL,
    `creado_en`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_estado` (`estado`),
    INDEX `idx_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Consultas de ejemplo para la vista de examenes del panel:
--
-- Notas de todos los alumnos:
--   SELECT nombre, dui, codigo, estado, nota, aciertos, total, porcentaje,
--          habilitado_desde, terminado_en
--   FROM examen_alumnos
--   ORDER BY terminado_en DESC;
--
-- Solo los que ya rindieron el examen:
--   SELECT * FROM examen_alumnos
--   WHERE estado IN ('aprobado','reprobado');
--
-- Pendientes de tomar:
--   SELECT * FROM examen_alumnos WHERE estado = 'pendiente';
