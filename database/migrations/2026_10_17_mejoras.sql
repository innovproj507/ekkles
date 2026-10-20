-- ============================================================
-- EKKLES — Migración 2026-10-17
-- Convertidos, salones, eventos con aporte de comida,
-- grupos de categorías y recibos de egresos.
--
-- IDEMPOTENTE: revisa cada columna/índice/tabla antes de crearla,
-- así que se puede ejecutar más de una vez o después de una ejecución
-- a medias sin errores. Compatible con MySQL 5.7/8 y MariaDB.
--
-- Uso: seleccionar la base de datos de Ekkles y ejecutar este archivo
--   (phpMyAdmin → pestaña SQL / Importar, o: mysql -u <usuario> -p <base> < este_archivo.sql)
-- ============================================================

-- ── Miembros: convertido / bautizado ───────────────────────
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'is_converted');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `members` ADD COLUMN `is_converted` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status`',
    'SELECT ''members.is_converted ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND INDEX_NAME = 'idx_members_converted');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `members` ADD INDEX `idx_members_converted` (`is_converted`)',
    'SELECT ''idx_members_converted ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Cónyuges no bautizados según la hoja "Parejas" del Excel de miembros
-- (si esas personas no existen en esta base, no cambia nada)
UPDATE `members` SET `is_converted` = 0
 WHERE `church_id` = 1 AND `deleted_at` IS NULL
   AND CONCAT(`first_name`, ' ', `last_name`) IN ('Eneida Torres', 'Hernán Hernandez', 'Carolina Avila');

-- ── Salones ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `rooms` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`   INT UNSIGNED    NOT NULL,
  `name`        VARCHAR(100)    NOT NULL,
  `capacity`    INT UNSIGNED    NULL,
  `description` VARCHAR(255)    NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`  DATETIME        NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_rooms_church` (`church_id`),
  CONSTRAINT `fk_rooms_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Eventos: salón y aporte de comida de las familias ──────
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'room_id');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `events` ADD COLUMN `room_id` INT UNSIGNED NULL AFTER `location`',
    'SELECT ''events.room_id ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'food_contribution');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `events` ADD COLUMN `food_contribution` TINYINT(1) NOT NULL DEFAULT 0 AFTER `room_id`',
    'SELECT ''events.food_contribution ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND INDEX_NAME = 'idx_events_room');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `events` ADD INDEX `idx_events_room` (`room_id`)',
    'SELECT ''idx_events_room ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND CONSTRAINT_NAME = 'fk_events_room');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `events` ADD CONSTRAINT `fk_events_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL',
    'SELECT ''fk_events_room ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── Categorías financieras: grupo (clasificación) ──────────
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'finance_categories' AND COLUMN_NAME = 'group_name');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `finance_categories` ADD COLUMN `group_name` VARCHAR(80) NULL AFTER `name`',
    'SELECT ''finance_categories.group_name ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Clasificación inicial de las categorías de ingreso
-- (solo asigna grupo a las que todavía no tienen; no pisa cambios hechos en la app)
UPDATE `finance_categories` SET `group_name` = 'Aportes y ofrendas'
 WHERE `group_name` IS NULL AND `type` = 'ingreso' AND `target_module` = 'ofrendas'
   AND `name` IN ('Aporte Presencial', 'Aporte Especial', 'Aporte Banca en Línea', 'Ofrendas Generales', 'Ofrendas Especiales');
UPDATE `finance_categories` SET `group_name` = 'Proyectos'
 WHERE `group_name` IS NULL AND `type` = 'ingreso' AND `target_module` = 'ofrendas' AND `name` LIKE 'Proyecto%';
UPDATE `finance_categories` SET `group_name` = 'Aportes y donaciones'
 WHERE `group_name` IS NULL AND `type` = 'ingreso' AND `target_module` = 'construccion'
   AND `name` IN ('Construcción', 'Aportes', 'Donaciones');
UPDATE `finance_categories` SET `group_name` = 'Donaciones'
 WHERE `group_name` IS NULL AND `type` = 'ingreso' AND `target_module` = 'donaciones' AND `name` LIKE 'Donaci%';
UPDATE `finance_categories` SET `group_name` = 'Proyectos y misiones'
 WHERE `group_name` IS NULL AND `type` = 'ingreso' AND `target_module` = 'donaciones'
   AND `name` IN ('Eventos', 'Misiones', 'Compra de Medicinas', 'Reservación de hoteles', 'Transporte para giras médicas');
UPDATE `finance_categories` SET `group_name` = 'Saldos iniciales'
 WHERE `group_name` IS NULL AND `type` = 'ingreso' AND `name` IN ('Depósito Inicial', 'Cuenta inicial', 'Monto inicial');
UPDATE `finance_categories` SET `group_name` = 'Otros ingresos'
 WHERE `group_name` IS NULL AND `type` = 'ingreso'
   AND (`name` IN ('Intereses', 'Intereses Bancarios', 'Reembolso', 'Devolución', 'Transferencia de cuentas')
        OR (`target_module` = 'ofrendas' AND `name` = 'Donaciones'));

-- ── Verificación: todas las filas deben decir 1 ────────────
SELECT
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members'            AND COLUMN_NAME = 'is_converted')      AS members_is_converted,
  (SELECT COUNT(*) FROM information_schema.TABLES  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rooms')                                                AS tabla_rooms,
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events'             AND COLUMN_NAME = 'room_id')           AS events_room_id,
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events'             AND COLUMN_NAME = 'food_contribution') AS events_food_contribution,
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'finance_categories' AND COLUMN_NAME = 'group_name')        AS categorias_group_name;
