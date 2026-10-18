-- ============================================================
-- EKKLES — Migración 2026-10-17
-- Convertidos, salones, eventos con aporte de comida,
-- grupos de categorías y recibos de egresos.
-- Ejecutar una sola vez:  mysql -u <usuario> -p ekkles < 2026_10_17_mejoras.sql
-- ============================================================

-- ── Miembros: convertido / bautizado ───────────────────────
ALTER TABLE `members`
  ADD COLUMN `is_converted` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status`,
  ADD INDEX `idx_members_converted` (`is_converted`);

-- Cónyuges no bautizados según la hoja "Parejas" del Excel de miembros
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
ALTER TABLE `events`
  ADD COLUMN `room_id` INT UNSIGNED NULL AFTER `location`,
  ADD COLUMN `food_contribution` TINYINT(1) NOT NULL DEFAULT 0 AFTER `room_id`,
  ADD INDEX `idx_events_room` (`room_id`),
  ADD CONSTRAINT `fk_events_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

-- ── Categorías financieras: grupo (clasificación) ──────────
ALTER TABLE `finance_categories`
  ADD COLUMN `group_name` VARCHAR(80) NULL AFTER `name`;

-- Clasificación inicial de las categorías de ingreso
UPDATE `finance_categories` SET `group_name` = 'Aportes y ofrendas'
 WHERE `type` = 'ingreso' AND `target_module` = 'ofrendas'
   AND `name` IN ('Aporte Presencial', 'Aporte Especial', 'Aporte Banca en Línea', 'Ofrendas Generales', 'Ofrendas Especiales');
UPDATE `finance_categories` SET `group_name` = 'Proyectos'
 WHERE `type` = 'ingreso' AND `target_module` = 'ofrendas' AND `name` LIKE 'Proyecto%';
UPDATE `finance_categories` SET `group_name` = 'Aportes y donaciones'
 WHERE `type` = 'ingreso' AND `target_module` = 'construccion' AND `name` IN ('Construcción', 'Aportes', 'Donaciones');
UPDATE `finance_categories` SET `group_name` = 'Donaciones'
 WHERE `type` = 'ingreso' AND `target_module` = 'donaciones' AND (`name` LIKE 'Donaci%');
UPDATE `finance_categories` SET `group_name` = 'Proyectos y misiones'
 WHERE `type` = 'ingreso' AND `target_module` = 'donaciones'
   AND `name` IN ('Eventos', 'Misiones', 'Compra de Medicinas', 'Reservación de hoteles', 'Transporte para giras médicas');
UPDATE `finance_categories` SET `group_name` = 'Saldos iniciales'
 WHERE `type` = 'ingreso' AND `name` IN ('Depósito Inicial', 'Cuenta inicial', 'Monto inicial');
UPDATE `finance_categories` SET `group_name` = 'Otros ingresos'
 WHERE `type` = 'ingreso' AND `group_name` IS NULL
   AND `name` IN ('Intereses', 'Intereses Bancarios', 'Reembolso', 'Devolución', 'Transferencia de cuentas')
   OR (`type` = 'ingreso' AND `target_module` = 'ofrendas' AND `name` = 'Donaciones');
