-- ============================================================
-- EKKLES — Dump Completo de Migración a Producción
-- Versión: 1.0.0
-- Motor: MySQL 8+ / MariaDB 10.6+  |  Charset: utf8mb4
-- ============================================================
--
-- INSTRUCCIONES DE USO:
--   1. Crear la base de datos en el servidor de producción:
--        CREATE DATABASE ekkles CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--
--   2. Importar este archivo:
--        mysql -u <usuario> -p ekkles < ekkles_migration.sql
--      o desde phpMyAdmin: seleccionar la BD y usar "Importar".
--
--   3. Cambiar el email del super admin por el email real del administrador
--      desde el perfil de usuario en la aplicación tras el primer ingreso.
--
--   4. Configurar el archivo .env en el servidor con los valores
--      correctos de DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL, etc.
--
-- ⚠️  NOTA: Este dump NO incluye datos de producción (miembros, finanzas,
--      asistencia, etc.). Solo crea la estructura y los datos de configuración
--      iniciales (categorías, ministerios, iglesia por defecto, admin).
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================================
-- TABLA: churches  (Multi-tenant — tabla raíz)
-- ============================================================
CREATE TABLE IF NOT EXISTS `churches` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `uuid`        CHAR(36)        NOT NULL UNIQUE,
  `name`        VARCHAR(150)    NOT NULL,
  `slogan`      VARCHAR(255)    NULL,
  `logo`        VARCHAR(255)    NULL,
  `address`     VARCHAR(255)    NULL,
  `city`        VARCHAR(100)    NULL,
  `country`     VARCHAR(100)    NOT NULL DEFAULT 'Panamá',
  `phone`       VARCHAR(30)     NULL,
  `email`       VARCHAR(150)    NULL,
  `website`     VARCHAR(255)    NULL,
  `timezone`    VARCHAR(50)     NOT NULL DEFAULT 'America/Panama',
  `currency`    CHAR(3)         NOT NULL DEFAULT 'USD',
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_churches_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: finance_categories  (antes que donations y finances)
-- ============================================================
CREATE TABLE IF NOT EXISTS `finance_categories` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`     INT UNSIGNED    NOT NULL,
  `name`          VARCHAR(100)    NOT NULL,
  `type`          ENUM('ingreso','egreso') NOT NULL,
  `target_module` VARCHAR(50)     NULL,
  `parent_id`     INT UNSIGNED    NULL,
  `code`          VARCHAR(20)     NULL,
  `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_fc_church` (`church_id`),
  CONSTRAINT `fk_fc_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fc_parent` FOREIGN KEY (`parent_id`) REFERENCES `finance_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`            INT UNSIGNED    NOT NULL,
  `uuid`                 CHAR(36)        NOT NULL UNIQUE,
  `name`                 VARCHAR(100)    NOT NULL,
  `email`                VARCHAR(150)    NOT NULL,
  `password`             VARCHAR(255)    NOT NULL,
  `role`                 ENUM('super_admin','ministro_principal','evangelista','administrador','tesorero','lider_ministerio','coordinador','secretario','anciano','diacono') NOT NULL DEFAULT 'diacono',
  `avatar`               VARCHAR(255)    NULL,
  `phone`                VARCHAR(30)     NULL,
  `is_active`            TINYINT(1)      NOT NULL DEFAULT 1,
  `mfa_enabled`          TINYINT(1)      NOT NULL DEFAULT 0,
  `mfa_secret`           VARCHAR(100)    NULL,
  `last_login_at`        DATETIME        NULL,
  `last_login_ip`        VARCHAR(45)     NULL,
  `login_attempts`       TINYINT         NOT NULL DEFAULT 0,
  `locked_until`         DATETIME        NULL,
  `password_reset_token` VARCHAR(100)    NULL,
  `password_reset_at`    DATETIME        NULL,
  `email_verified_at`    DATETIME        NULL,
  `remember_token`       VARCHAR(100)    NULL,
  `created_at`           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`           DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email_church` (`email`, `church_id`),
  INDEX `idx_users_church` (`church_id`),
  INDEX `idx_users_role` (`role`),
  CONSTRAINT `fk_users_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: members
-- ============================================================
CREATE TABLE IF NOT EXISTS `members` (
  `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`           INT UNSIGNED    NOT NULL,
  `user_id`             INT UNSIGNED    NULL,
  `uuid`                CHAR(36)        NOT NULL UNIQUE,
  `code`                VARCHAR(20)     NOT NULL,
  `first_name`          VARCHAR(80)     NOT NULL,
  `last_name`           VARCHAR(80)     NOT NULL,
  `birth_date`          DATE            NULL,
  `gender`              ENUM('M','F','otro') NOT NULL DEFAULT 'M',
  `marital_status`      ENUM('soltero','casado','divorciado','viudo','union_libre') NULL,
  `document_type`       ENUM('CC','CE','Pasaporte','NIT','TI','otro') NULL DEFAULT 'CC',
  `document_number`     VARCHAR(30)     NULL,
  `address`             VARCHAR(255)    NULL,
  `city`                VARCHAR(100)    NULL,
  `phone`               VARCHAR(30)     NULL,
  `phone_alt`           VARCHAR(30)     NULL,
  `email`               VARCHAR(150)    NULL,
  `photo`               VARCHAR(255)    NULL,
  `baptism_date`        DATE            NULL,
  `membership_date`     DATE            NULL,
  `conversion_date`     DATE            NULL,
  `status`              ENUM('activo','inactivo','visitante','nuevo_creyente','trasladado','fallecido') NOT NULL DEFAULT 'visitante',
  `ministry_id`         INT UNSIGNED    NULL,
  `pastoral_notes`      TEXT            NULL,
  `emergency_contact`   VARCHAR(100)    NULL,
  `emergency_phone`     VARCHAR(30)     NULL,
  `occupation`          VARCHAR(100)    NULL,
  `education_level`     VARCHAR(80)     NULL,
  `how_did_you_know`    VARCHAR(255)    NULL,
  `last_attendance_at`  DATE            NULL,
  `created_by`          INT UNSIGNED    NULL,
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`          DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_members_code_church` (`code`, `church_id`),
  INDEX `idx_members_church` (`church_id`),
  INDEX `idx_members_status` (`status`),
  INDEX `idx_members_ministry` (`ministry_id`),
  CONSTRAINT `fk_members_church`      FOREIGN KEY (`church_id`)  REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_members_user`        FOREIGN KEY (`user_id`)    REFERENCES `users`    (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_members_created_by`  FOREIGN KEY (`created_by`) REFERENCES `users`    (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: families
-- ============================================================
CREATE TABLE IF NOT EXISTS `families` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`   INT UNSIGNED    NOT NULL,
  `name`        VARCHAR(150)    NOT NULL,
  `head_id`     INT UNSIGNED    NULL COMMENT 'Jefe de hogar',
  `address`     VARCHAR(255)    NULL,
  `notes`       TEXT            NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`  DATETIME        NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_families_church` (`church_id`),
  CONSTRAINT `fk_families_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_families_head`   FOREIGN KEY (`head_id`)   REFERENCES `members`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: family_members
-- ============================================================
CREATE TABLE IF NOT EXISTS `family_members` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `family_id`    INT UNSIGNED    NOT NULL,
  `member_id`    INT UNSIGNED    NOT NULL,
  `relationship` ENUM('jefe_hogar','conyuge','hijo','hija','padre','madre','abuelo','abuela','otro') NOT NULL DEFAULT 'otro',
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_family_member` (`family_id`, `member_id`),
  CONSTRAINT `fk_fm_family` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fm_member` FOREIGN KEY (`member_id`) REFERENCES `members`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: ministries
-- ============================================================
CREATE TABLE IF NOT EXISTS `ministries` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`   INT UNSIGNED    NOT NULL,
  `name`        VARCHAR(150)    NOT NULL,
  `description` TEXT            NULL,
  `color`       VARCHAR(7)      NULL DEFAULT '#6366f1',
  `icon`        VARCHAR(50)     NULL,
  `leader_id`   INT UNSIGNED    NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`  DATETIME        NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_ministries_church` (`church_id`),
  CONSTRAINT `fk_ministries_church`  FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ministries_leader`  FOREIGN KEY (`leader_id`) REFERENCES `members`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: ministry_members
-- ============================================================
CREATE TABLE IF NOT EXISTS `ministry_members` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `ministry_id` INT UNSIGNED    NOT NULL,
  `member_id`   INT UNSIGNED    NOT NULL,
  `role`        VARCHAR(80)     NULL DEFAULT 'miembro',
  `joined_at`   DATE            NULL,
  `left_at`     DATE            NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mm` (`ministry_id`, `member_id`),
  CONSTRAINT `fk_mm_ministry` FOREIGN KEY (`ministry_id`) REFERENCES `ministries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mm_member`   FOREIGN KEY (`member_id`)   REFERENCES `members`    (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: event_types
-- ============================================================
CREATE TABLE IF NOT EXISTS `event_types` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`   INT UNSIGNED    NOT NULL,
  `name`        VARCHAR(100)    NOT NULL,
  `color`       VARCHAR(20)     NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_event_types_church` (`church_id`),
  CONSTRAINT `fk_event_types_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: events
-- ============================================================
CREATE TABLE IF NOT EXISTS `events` (
  `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`         INT UNSIGNED    NOT NULL,
  `uuid`              CHAR(36)        NOT NULL UNIQUE,
  `title`             VARCHAR(200)    NOT NULL,
  `description`       TEXT            NULL,
  `event_type_id`     INT UNSIGNED    NOT NULL,
  `start_datetime`    DATETIME        NOT NULL,
  `end_datetime`      DATETIME        NULL,
  `location`          VARCHAR(255)    NULL,
  `online_url`        VARCHAR(500)    NULL,
  `max_capacity`      INT             NULL,
  `is_recurring`      TINYINT(1)      NOT NULL DEFAULT 0,
  `recurrence_rule`   VARCHAR(255)    NULL,
  `registration_open` TINYINT(1)      NOT NULL DEFAULT 0,
  `qr_code`           VARCHAR(255)    NULL,
  `ministry_id`       INT UNSIGNED    NULL,
  `created_by`        INT UNSIGNED    NULL,
  `status`            ENUM('borrador','publicado','cancelado','finalizado') NOT NULL DEFAULT 'publicado',
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`        DATETIME        NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_events_church` (`church_id`),
  INDEX `idx_events_start`  (`start_datetime`),
  CONSTRAINT `fk_events_church`     FOREIGN KEY (`church_id`)     REFERENCES `churches`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_events_type`       FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_events_ministry`   FOREIGN KEY (`ministry_id`)   REFERENCES `ministries`  (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_events_created_by` FOREIGN KEY (`created_by`)    REFERENCES `users`       (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: event_attendance
-- ============================================================
CREATE TABLE IF NOT EXISTS `event_attendance` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `event_id`    INT UNSIGNED    NOT NULL,
  `member_id`   INT UNSIGNED    NULL,
  `guest_name`  VARCHAR(150)    NULL,
  `guest_phone` VARCHAR(30)     NULL,
  `checkin_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `checkin_by`  INT UNSIGNED    NULL,
  `via_qr`      TINYINT(1)      NOT NULL DEFAULT 0,
  `notes`       VARCHAR(255)    NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_ea_event`  (`event_id`),
  INDEX `idx_ea_member` (`member_id`),
  CONSTRAINT `fk_ea_event`       FOREIGN KEY (`event_id`)  REFERENCES `events`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ea_member`      FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ea_checkin_by`  FOREIGN KEY (`checkin_by`) REFERENCES `users`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: simple_attendances  (conteo rápido de asistencia)
-- ============================================================
CREATE TABLE IF NOT EXISTS `simple_attendances` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`       INT UNSIGNED    NOT NULL,
  `date`            DATE            NOT NULL,
  `adults_count`    INT UNSIGNED    NOT NULL DEFAULT 0,
  `children_count`  INT UNSIGNED    NOT NULL DEFAULT 0,
  `guests_count`    INT UNSIGNED    NOT NULL DEFAULT 0,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_sa_church_date` (`church_id`, `date`),
  CONSTRAINT `fk_sa_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: event_teams  (voluntarios / equipos de servicio)
-- ============================================================
CREATE TABLE IF NOT EXISTS `event_teams` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `event_id`    INT UNSIGNED    NOT NULL,
  `member_id`   INT UNSIGNED    NOT NULL,
  `role`        ENUM('ujier','alabanza','multimedia','seguridad','intercesion','escuela_dominical','otro') NOT NULL DEFAULT 'ujier',
  `status`      ENUM('asignado','confirmado','ausente') NOT NULL DEFAULT 'asignado',
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_et_event` (`event_id`),
  CONSTRAINT `fk_et_event`  FOREIGN KEY (`event_id`)  REFERENCES `events`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_et_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: donations  (ofrendas, construcción, obras misioneras)
-- ============================================================
CREATE TABLE IF NOT EXISTS `donations` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`       INT UNSIGNED    NOT NULL,
  `uuid`            CHAR(36)        NOT NULL UNIQUE,
  `receipt_number`  VARCHAR(30)     NOT NULL,
  `member_id`       INT UNSIGNED    NULL,
  `donor_name`      VARCHAR(150)    NULL,
  `ruc`             VARCHAR(30)     NULL,
  `type`            ENUM('diezmo','ofrenda_general','ofrenda_especial','promesa','donacion','construccion','misiones','proyecto') NOT NULL DEFAULT 'ofrenda_general',
  `detail`          VARCHAR(255)    NULL,
  `category_id`     INT UNSIGNED    NULL,
  `donation_date`   DATE            NOT NULL,
  `amount`          DECIMAL(15,2)   NOT NULL,
  `currency`        CHAR(3)         NOT NULL DEFAULT 'USD',
  `payment_method`  ENUM('efectivo','transferencia','tarjeta','pago_movil','qr','cheque','otro') NOT NULL DEFAULT 'efectivo',
  `reference`       VARCHAR(100)    NULL,
  `event_id`        INT UNSIGNED    NULL,
  `campaign_id`     INT UNSIGNED    NULL,
  `notes`           TEXT            NULL,
  `period_month`    TINYINT         NOT NULL,
  `period_year`     SMALLINT        NOT NULL,
  `created_by`      INT UNSIGNED    NULL,
  `is_anonymous`    TINYINT(1)      NOT NULL DEFAULT 0,
  `receipt_sent`    TINYINT(1)      NOT NULL DEFAULT 0,
  `status`          ENUM('pendiente','aprobado','rechazado','anulado') NOT NULL DEFAULT 'aprobado',
  `approved_by`     INT UNSIGNED    NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_receipt` (`receipt_number`, `church_id`),
  INDEX `idx_donations_church`  (`church_id`),
  INDEX `idx_donations_member`  (`member_id`),
  INDEX `idx_donations_period`  (`period_year`, `period_month`),
  CONSTRAINT `fk_donations_church`      FOREIGN KEY (`church_id`)  REFERENCES `churches`          (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_donations_member`      FOREIGN KEY (`member_id`)  REFERENCES `members`           (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_donations_event`       FOREIGN KEY (`event_id`)   REFERENCES `events`            (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_donations_created_by`  FOREIGN KEY (`created_by`) REFERENCES `users`             (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_donations_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users`            (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_donations_category`    FOREIGN KEY (`category_id`) REFERENCES `finance_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: finances  (libro mayor de transacciones)
-- ============================================================
CREATE TABLE IF NOT EXISTS `finances` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`        INT UNSIGNED    NOT NULL,
  `uuid`             CHAR(36)        NOT NULL UNIQUE,
  `type`             ENUM('ingreso','egreso') NOT NULL,
  `category_id`      INT UNSIGNED    NOT NULL,
  `target_module`    VARCHAR(50)     NULL,
  `description`      VARCHAR(255)    NOT NULL,
  `amount`           DECIMAL(15,2)   NOT NULL,
  `currency`         CHAR(3)         NOT NULL DEFAULT 'USD',
  `payment_method`   ENUM('efectivo','transferencia','tarjeta','cheque','otro') NOT NULL DEFAULT 'efectivo',
  `reference`        VARCHAR(100)    NULL,
  `invoice_number`   VARCHAR(50)     NULL,
  `provider_ruc`     VARCHAR(30)     NULL,
  `provider_name`    VARCHAR(150)    NULL,
  `transaction_date` DATE            NOT NULL,
  `period_month`     TINYINT         NOT NULL,
  `period_year`      SMALLINT        NOT NULL,
  `budget_id`        INT UNSIGNED    NULL,
  `donation_id`      INT UNSIGNED    NULL,
  `attachments`      JSON            NULL,
  `notes`            TEXT            NULL,
  `is_reconciled`    TINYINT(1)      NOT NULL DEFAULT 0,
  `created_by`       INT UNSIGNED    NULL,
  `approved_by`      INT UNSIGNED    NULL,
  `status`           ENUM('pendiente','aprobado','rechazado','anulado') NOT NULL DEFAULT 'aprobado',
  `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_finances_church`  (`church_id`),
  INDEX `idx_finances_type`    (`type`),
  INDEX `idx_finances_period`  (`period_year`, `period_month`),
  CONSTRAINT `fk_finances_church`      FOREIGN KEY (`church_id`)  REFERENCES `churches`           (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_finances_category`    FOREIGN KEY (`category_id`) REFERENCES `finance_categories`(`id`),
  CONSTRAINT `fk_finances_donation`    FOREIGN KEY (`donation_id`) REFERENCES `donations`         (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_finances_created_by`  FOREIGN KEY (`created_by`) REFERENCES `users`              (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_finances_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users`             (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: budgets  (presupuestos anuales/mensuales)
-- ============================================================
CREATE TABLE IF NOT EXISTS `budgets` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`   INT UNSIGNED    NOT NULL,
  `category_id` INT UNSIGNED    NOT NULL,
  `year`        SMALLINT        NOT NULL,
  `month`       TINYINT         NULL COMMENT 'NULL = presupuesto anual',
  `amount`      DECIMAL(15,2)   NOT NULL,
  `notes`       TEXT            NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_budgets_church` (`church_id`),
  CONSTRAINT `fk_budgets_church`    FOREIGN KEY (`church_id`)  REFERENCES `churches`           (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_budgets_category`  FOREIGN KEY (`category_id`) REFERENCES `finance_categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: reconciliations  (conciliaciones bancarias)
-- NOTA: Esta tabla no estaba en schema.sql original — se agrega aquí.
-- ============================================================
CREATE TABLE IF NOT EXISTS `reconciliations` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`        INT UNSIGNED    NOT NULL,
  `account_name`     ENUM('ofrendas','construccion','misiones') NOT NULL,
  `period_start`     DATE            NOT NULL,
  `period_end`       DATE            NOT NULL,
  `starting_balance` DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `total_income`     DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `total_expense`    DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `expected_balance` DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `actual_balance`   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `difference`       DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `notes`            TEXT            NULL,
  `status`           ENUM('borrador','cerrada') NOT NULL DEFAULT 'borrador',
  `reconciled_by`    INT UNSIGNED    NULL,
  `reconciled_at`    DATETIME        NULL,
  `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`       DATETIME        NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_recon_church`  (`church_id`),
  INDEX `idx_recon_account` (`account_name`),
  INDEX `idx_recon_period`  (`period_end`),
  CONSTRAINT `fk_recon_church`        FOREIGN KEY (`church_id`)    REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recon_reconciled_by` FOREIGN KEY (`reconciled_by`) REFERENCES `users`   (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: pastoral_follows  (CRM de seguimiento pastoral)
-- ============================================================
CREATE TABLE IF NOT EXISTS `pastoral_follows` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`       INT UNSIGNED    NOT NULL,
  `member_id`       INT UNSIGNED    NOT NULL,
  `assigned_to`     INT UNSIGNED    NULL COMMENT 'Pastor o líder asignado',
  `type`            ENUM('visita','llamada','oracion','consejeria','disciplado','otro') NOT NULL DEFAULT 'llamada',
  `status`          ENUM('pendiente','en_proceso','completado','cancelado') NOT NULL DEFAULT 'pendiente',
  `pipeline_stage`  ENUM('visitante','contactado','integrado','discipulado','miembro') NOT NULL DEFAULT 'visitante',
  `subject`         VARCHAR(200)    NULL,
  `notes`           TEXT            NULL,
  `scheduled_at`    DATETIME        NULL,
  `completed_at`    DATETIME        NULL,
  `next_follow_at`  DATE            NULL,
  `priority`        ENUM('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  `created_by`      INT UNSIGNED    NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`      DATETIME        NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_pf_church`   (`church_id`),
  INDEX `idx_pf_member`   (`member_id`),
  INDEX `idx_pf_assigned` (`assigned_to`),
  INDEX `idx_pf_status`   (`status`),
  CONSTRAINT `fk_pf_church`      FOREIGN KEY (`church_id`)  REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pf_member`      FOREIGN KEY (`member_id`)  REFERENCES `members`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pf_assigned`    FOREIGN KEY (`assigned_to`) REFERENCES `users`   (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pf_created_by`  FOREIGN KEY (`created_by`) REFERENCES `users`    (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`   INT UNSIGNED    NOT NULL,
  `user_id`     INT UNSIGNED    NULL,
  `type`        ENUM('email','sms','whatsapp','push','interna') NOT NULL DEFAULT 'interna',
  `title`       VARCHAR(200)    NOT NULL,
  `body`        TEXT            NOT NULL,
  `data`        JSON            NULL,
  `read_at`     DATETIME        NULL,
  `sent_at`     DATETIME        NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_notif_user`   (`user_id`),
  INDEX `idx_notif_church` (`church_id`),
  CONSTRAINT `fk_notif_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`    (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: audit_logs  (bitácora de auditoría)
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `church_id`     INT UNSIGNED     NULL,
  `user_id`       INT UNSIGNED     NULL,
  `user_name`     VARCHAR(100)     NULL COMMENT 'Snapshot del nombre al momento del evento',
  `action`        VARCHAR(100)     NOT NULL,
  `module`        VARCHAR(50)      NOT NULL,
  `resource_type` VARCHAR(50)      NULL,
  `resource_id`   INT UNSIGNED     NULL,
  `old_values`    JSON             NULL,
  `new_values`    JSON             NULL,
  `ip_address`    VARCHAR(45)      NULL,
  `user_agent`    VARCHAR(500)     NULL,
  `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_church`   (`church_id`),
  INDEX `idx_audit_user`     (`user_id`),
  INDEX `idx_audit_action`   (`action`),
  INDEX `idx_audit_module`   (`module`),
  INDEX `idx_audit_created`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: settings  (configuración por iglesia)
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`   INT UNSIGNED    NOT NULL,
  `key`         VARCHAR(100)    NOT NULL,
  `value`       TEXT            NULL,
  `type`        ENUM('string','integer','boolean','json') NOT NULL DEFAULT 'string',
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting` (`church_id`, `key`),
  CONSTRAINT `fk_settings_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ============================================================
-- DATOS INICIALES (Seeds)
-- ============================================================
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- Iglesia por defecto
-- ⚠️  Cambia el nombre, ciudad y país según tu iglesia.
-- ────────────────────────────────────────────────────────────
INSERT IGNORE INTO `churches` (`id`, `uuid`, `name`, `city`, `country`, `timezone`, `currency`, `is_active`) VALUES
(1, UUID(), 'Mi Iglesia', 'Ciudad', 'Colombia', 'America/Bogota', 'USD', 1);

-- ────────────────────────────────────────────────────────────
-- Usuario Super Administrador
-- Contraseña inicial: Admin123!
-- ⚠️  Cambia la contraseña inmediatamente después de ingresar.
-- ────────────────────────────────────────────────────────────
INSERT IGNORE INTO `users` (`church_id`, `uuid`, `name`, `email`, `password`, `role`, `is_active`, `email_verified_at`) VALUES
(1, UUID(), 'Administrador', 'admin@ekkles.local',
 '$2y$12$5oQKbx7kS/8YjGtgCUXqI.3NNOaUOduYUu2M7o7xt3h3kV7TQowUC',
 'super_admin', 1, NOW());

-- ────────────────────────────────────────────────────────────
-- Categorías financieras base (ofrendas, donaciones, construcción)
-- ────────────────────────────────────────────────────────────
INSERT IGNORE INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`, `is_active`) VALUES
-- Base schema
(1, 'Ofrendas',              'ingreso', 'ofrendas',    'ING-001', 1),
(1, 'Ofrendas Generales',    'ingreso', 'ofrendas',    'ING-002', 1),
(1, 'Ofrendas Especiales',   'ingreso', 'ofrendas',    'ING-003', 1),
(1, 'Donaciones',            'ingreso', 'donaciones',  'ING-004', 1),
(1, 'Eventos',               'ingreso', 'donaciones',  'ING-005', 1),
(1, 'Misiones',              'ingreso', 'donaciones',  'ING-006', 1),
(1, 'Construcción',          'ingreso', 'construccion','ING-007', 1),
(1, 'Nómina',                'egreso',  'ofrendas',    'EGR-001', 1),
(1, 'Servicios Públicos',    'egreso',  'ofrendas',    'EGR-002', 1),
(1, 'Mantenimiento',         'egreso',  'construccion','EGR-003', 1),
(1, 'Ayuda Social',          'egreso',  'donaciones',  'EGR-004', 1),
(1, 'Misiones (Egreso)',     'egreso',  'donaciones',  'EGR-005', 1),
(1, 'Compras',               'egreso',  'ofrendas',    'EGR-006', 1),
(1, 'Administración',        'egreso',  'ofrendas',    'EGR-007', 1);

-- Módulo: Ofrendas — ingresos
INSERT IGNORE INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`, `is_active`) VALUES
(1, 'Aporte Presencial',         'ingreso', 'ofrendas', NULL, 1),
(1, 'Aporte Especial',           'ingreso', 'ofrendas', NULL, 1),
(1, 'Aporte Banca en Línea',     'ingreso', 'ofrendas', NULL, 1),
(1, 'Intereses Bancarios',       'ingreso', 'ofrendas', NULL, 1),
(1, 'Depósito Inicial',          'ingreso', 'ofrendas', NULL, 1),
(1, 'Reembolso',                 'ingreso', 'ofrendas', NULL, 1),
(1, 'Devolución',                'ingreso', 'ofrendas', NULL, 1);

-- Módulo: Ofrendas — egresos
INSERT IGNORE INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`, `is_active`) VALUES
(1, 'Cuenta',                        'egreso', 'ofrendas', NULL, 1),
(1, 'Ministerio de Benevolencia',     'egreso', 'ofrendas', NULL, 1),
(1, 'Ministerio de Evangelismo',      'egreso', 'ofrendas', NULL, 1),
(1, 'Ministerio de Mantenimiento',    'egreso', 'ofrendas', NULL, 1),
(1, 'Ministerio de Adoración',        'egreso', 'ofrendas', NULL, 1),
(1, 'Ministerio de Educación',        'egreso', 'ofrendas', NULL, 1),
(1, 'Ministerio de Comunicación',     'egreso', 'ofrendas', NULL, 1),
(1, 'Ministerio de Niños',            'egreso', 'ofrendas', NULL, 1),
(1, 'Mensualidades de las guías',     'egreso', 'ofrendas', NULL, 1),
(1, 'Encuentro de varones',           'egreso', 'ofrendas', NULL, 1),
(1, 'Salarios',                       'egreso', 'ofrendas', NULL, 1),
(1, 'Gastos Misceláneos',             'egreso', 'ofrendas', NULL, 1),
(1, 'Cuenta Corriente Entrada',       'egreso', 'ofrendas', NULL, 1),
(1, 'Cuenta Corriente Salida',        'egreso', 'ofrendas', NULL, 1),
(1, 'Servicios Básicos',              'egreso', 'ofrendas', NULL, 1),
(1, 'Ministerio de Jóvenes',          'egreso', 'ofrendas', NULL, 1),
(1, 'Retiro',                         'egreso', 'ofrendas', NULL, 1),
(1, 'Transferencia Construcción',     'egreso', 'ofrendas', NULL, 1),
(1, 'Transferencia por equivocación', 'egreso', 'ofrendas', NULL, 1),
(1, 'Gastos comisión internacional',  'egreso', 'ofrendas', NULL, 1);

-- Módulo: Donaciones / Obras Misioneras — ingresos
INSERT IGNORE INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`, `is_active`) VALUES
(1, 'Donación de PROSEA',                                 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación de Iglesia Unión del Área del Canal',       'ingreso', 'donaciones', NULL, 1),
(1, 'Donación de Yolanda Torres',                         'ingreso', 'donaciones', NULL, 1),
(1, 'Donación de la Sra. María',                          'ingreso', 'donaciones', NULL, 1),
(1, 'Donación de Scott',                                  'ingreso', 'donaciones', NULL, 1),
(1, 'Donación para canoa de Nelson Chamí',                'ingreso', 'donaciones', NULL, 1),
(1, 'Donación para canoa de Leo',                         'ingreso', 'donaciones', NULL, 1),
(1, 'Donación para jóvenes de Cimarrón',                  'ingreso', 'donaciones', NULL, 1),
(1, 'Donación McKenzie CoC',                              'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Timberlane CoC',                            'ingreso', 'donaciones', NULL, 1),
(1, 'Reembolso',                                          'ingreso', 'donaciones', NULL, 1),
(1, 'Devolución',                                         'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Luz Marina Velásquez',                      'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Park CoC',                                  'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Mike Vann',                                 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Rebeca Kidder',                             'ingreso', 'donaciones', NULL, 1),
(1, 'Compra de Medicinas',                                'ingreso', 'donaciones', NULL, 1),
(1, 'Monto inicial',                                      'ingreso', 'donaciones', NULL, 1),
(1, 'Donación',                                           'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Computadora PROSEA',                        'ingreso', 'donaciones', NULL, 1),
(1, 'Donaciones para el equipo de sonido',                'ingreso', 'donaciones', NULL, 1),
(1, 'Donaciones de Jason Meadow para Danitza Cárdenas',   'ingreso', 'donaciones', NULL, 1),
(1, 'Reservación de hoteles',                             'ingreso', 'donaciones', NULL, 1),
(1, 'Transporte para giras médicas',                      'ingreso', 'donaciones', NULL, 1),
(1, 'Transferencia de cuentas',                           'ingreso', 'donaciones', NULL, 1),
(1, 'Intereses',                                          'ingreso', 'donaciones', NULL, 1);

-- Módulo: Donaciones / Obras Misioneras — egresos
INSERT IGNORE INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`, `is_active`) VALUES
(1, 'Salario de Nelson Mepaquito',                   'egreso', 'donaciones', NULL, 1),
(1, 'Salario de Nelson Chamí',                       'egreso', 'donaciones', NULL, 1),
(1, 'Salario de Edwin Isabaré',                      'egreso', 'donaciones', NULL, 1),
(1, 'Salario de Euclides Nieto',                     'egreso', 'donaciones', NULL, 1),
(1, 'Gastos PROSEA',                                 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos para canoa de Nelson Chamí',             'egreso', 'donaciones', NULL, 1),
(1, 'Gastos de hoteles',                             'egreso', 'donaciones', NULL, 1),
(1, 'Gastos de transporte',                          'egreso', 'donaciones', NULL, 1),
(1, 'Donación de comida',                            'egreso', 'donaciones', NULL, 1),
(1, 'Compra de medicamentos',                        'egreso', 'donaciones', NULL, 1),
(1, 'Comida para Ancoré',                            'egreso', 'donaciones', NULL, 1),
(1, 'Misceláneos',                                   'egreso', 'donaciones', NULL, 1),
(1, 'Encuentro juvenil',                             'egreso', 'donaciones', NULL, 1),
(1, 'Reembolso a Nelson',                            'egreso', 'donaciones', NULL, 1),
(1, 'Reembolso por materiales para canoa',           'egreso', 'donaciones', NULL, 1),
(1, 'Compra de comida para familias de Cimarrón',    'egreso', 'donaciones', NULL, 1),
(1, 'Transporte en canoa y comida a Cimarron',       'egreso', 'donaciones', NULL, 1),
(1, 'Construcción de la casa de Nelson Mepaquito',   'egreso', 'donaciones', NULL, 1),
(1, 'Transporte hijas de Leo',                       'egreso', 'donaciones', NULL, 1),
(1, 'Chamí',                                         'egreso', 'donaciones', NULL, 1),
(1, 'Mano de obra canoa',                            'egreso', 'donaciones', NULL, 1),
(1, 'Escuela Bíblica de Verano',                     'egreso', 'donaciones', NULL, 1),
(1, 'Gastos del Proyecto Wellness',                  'egreso', 'donaciones', NULL, 1),
(1, 'Universidad de Enerson Mepaquito',              'egreso', 'donaciones', NULL, 1),
(1, 'Sepelio de Luis Amagara',                       'egreso', 'donaciones', NULL, 1),
(1, 'Gastos para matrimonios',                       'egreso', 'donaciones', NULL, 1),
(1, 'Donaciones de Timberlake Church of Christ',     'egreso', 'donaciones', NULL, 1),
(1, 'Donación',                                      'egreso', 'donaciones', NULL, 1),
(1, 'Eliester guiro y baños Ancoré',                 'egreso', 'donaciones', NULL, 1),
(1, 'Computadora PROSEA',                            'egreso', 'donaciones', NULL, 1),
(1, 'Transporte',                                    'egreso', 'donaciones', NULL, 1),
(1, 'Reembolso',                                     'egreso', 'donaciones', NULL, 1),
(1, 'Transferencia a JOSIAS de Scott Blackwell',     'egreso', 'donaciones', NULL, 1),
(1, 'Gastos escolares de Danitza Cárdenas',          'egreso', 'donaciones', NULL, 1),
(1, 'Gastos para equipo de sonido',                  'egreso', 'donaciones', NULL, 1),
(1, 'Salario de Emiliano Mepaquito',                 'egreso', 'donaciones', NULL, 1),
(1, 'Compra comida Sra. María',                      'egreso', 'donaciones', NULL, 1),
(1, 'Compra de comida Scott',                        'egreso', 'donaciones', NULL, 1),
(1, 'Gastos de comida Iglesia Unión',                'egreso', 'donaciones', NULL, 1),
(1, 'Transferencia de cuentas',                      'egreso', 'donaciones', NULL, 1),
(1, 'Comisión del banco',                            'egreso', 'donaciones', NULL, 1),
(1, 'Transferencia por equivocación',                'egreso', 'donaciones', NULL, 1);

-- Módulo: Construcción — ingresos
INSERT IGNORE INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`, `is_active`) VALUES
(1, 'Aportes',       'ingreso', 'construccion', NULL, 1),
(1, 'Cuenta inicial','ingreso', 'construccion', NULL, 1),
(1, 'Donaciones',    'ingreso', 'construccion', NULL, 1),
(1, 'Intereses',     'ingreso', 'construccion', NULL, 1),
(1, 'Reembolso',     'ingreso', 'construccion', NULL, 1),
(1, 'Devolución',    'ingreso', 'construccion', NULL, 1);

-- Módulo: Construcción — egresos
INSERT IGNORE INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`, `is_active`) VALUES
(1, 'Electricidad',                         'egreso', 'construccion', NULL, 1),
(1, 'Plomería',                             'egreso', 'construccion', NULL, 1),
(1, 'Albañilería',                          'egreso', 'construccion', NULL, 1),
(1, 'Materiales Electricidad',              'egreso', 'construccion', NULL, 1),
(1, 'Puertas',                              'egreso', 'construccion', NULL, 1),
(1, 'Cielo Raso',                           'egreso', 'construccion', NULL, 1),
(1, 'Ventanas',                             'egreso', 'construccion', NULL, 1),
(1, 'Puertas corredizas',                   'egreso', 'construccion', NULL, 1),
(1, 'Instalación de extintores',            'egreso', 'construccion', NULL, 1),
(1, 'Materiales para la rampa',             'egreso', 'construccion', NULL, 1),
(1, 'Mano de obra para la rampa',           'egreso', 'construccion', NULL, 1),
(1, 'Puertas de antipánico',                'egreso', 'construccion', NULL, 1),
(1, 'Pintura de puertas de metal',          'egreso', 'construccion', NULL, 1),
(1, 'Señalizaciones',                       'egreso', 'construccion', NULL, 1),
(1, 'Gravilla',                             'egreso', 'construccion', NULL, 1),
(1, 'Instalación sobres de baño',           'egreso', 'construccion', NULL, 1),
(1, 'Materiales de plomería',               'egreso', 'construccion', NULL, 1),
(1, 'Instalación de pared de plycem',       'egreso', 'construccion', NULL, 1),
(1, 'Certificación',                        'egreso', 'construccion', NULL, 1),
(1, 'Compra de lavamanos y llaves urinal',  'egreso', 'construccion', NULL, 1),
(1, 'Gastos de Uber',                       'egreso', 'construccion', NULL, 1),
(1, 'Lámparas solares',                     'egreso', 'construccion', NULL, 1),
(1, 'Materiales de trifásico',              'egreso', 'construccion', NULL, 1),
(1, 'Mano de obra de trifásico',            'egreso', 'construccion', NULL, 1),
(1, 'Materiales de alarma',                 'egreso', 'construccion', NULL, 1),
(1, 'Mano de obra de alarma',               'egreso', 'construccion', NULL, 1),
(1, 'Materiales de pasamano',               'egreso', 'construccion', NULL, 1),
(1, 'Mano de obra de pasamano',             'egreso', 'construccion', NULL, 1),
(1, 'Materiales del muro',                  'egreso', 'construccion', NULL, 1),
(1, 'Mano de obra del muro',                'egreso', 'construccion', NULL, 1),
(1, 'Transferencia por equivocación',       'egreso', 'construccion', NULL, 1),
(1, 'Materiales Plomería',                  'egreso', 'construccion', NULL, 1),
(1, 'Materiales Albañilería',               'egreso', 'construccion', NULL, 1),
(1, 'Viga Sísmica',                         'egreso', 'construccion', NULL, 1),
(1, 'Acero',                                'egreso', 'construccion', NULL, 1),
(1, 'Soldadura',                            'egreso', 'construccion', NULL, 1),
(1, 'Mano de Obra',                         'egreso', 'construccion', NULL, 1),
(1, 'Grúa',                                 'egreso', 'construccion', NULL, 1),
(1, 'Reparación de canal',                  'egreso', 'construccion', NULL, 1),
(1, 'Alquiler de baño portátil',            'egreso', 'construccion', NULL, 1),
(1, 'Transferencia',                        'egreso', 'construccion', NULL, 1),
(1, 'Gypsum',                               'egreso', 'construccion', NULL, 1),
(1, 'Baldosas y azulejos',                  'egreso', 'construccion', NULL, 1),
(1, 'Accesorios de baño',                   'egreso', 'construccion', NULL, 1),
(1, 'Accesorios de cocina',                 'egreso', 'construccion', NULL, 1),
(1, 'Acarreo',                              'egreso', 'construccion', NULL, 1),
(1, 'Inspección',                           'egreso', 'construccion', NULL, 1),
(1, 'Misceláneos',                          'egreso', 'construccion', NULL, 1),
(1, 'Otros Accesorios',                     'egreso', 'construccion', NULL, 1),
(1, 'Préstamo',                             'egreso', 'construccion', NULL, 1),
(1, 'Pago por uso de servidumbre (letrero)','egreso', 'construccion', NULL, 1),
(1, 'Instalación de ventana',               'egreso', 'construccion', NULL, 1),
(1, 'Muebles de baño',                      'egreso', 'construccion', NULL, 1),
(1, 'Confección de Planos',                 'egreso', 'construccion', NULL, 1),
(1, 'Pintura',                              'egreso', 'construccion', NULL, 1),
(1, 'Gastos comisión internacional',        'egreso', 'construccion', NULL, 1),
(1, 'Gastos comisión local',                'egreso', 'construccion', NULL, 1);

-- ────────────────────────────────────────────────────────────
-- Ministerios por defecto
-- ────────────────────────────────────────────────────────────
INSERT IGNORE INTO `ministries` (`church_id`, `name`, `description`, `color`, `icon`, `is_active`) VALUES
(1, 'Adoración',    'Ministerio de alabanza y adoración',               '#8b5cf6', 'music',    1),
(1, 'Benevolencia', 'Ministerio de ayuda social y benevolencia',        '#ef4444', 'heart',    1),
(1, 'Comunicación', 'Ministerio de comunicación y medios',              '#3b82f6', 'bullhorn', 1),
(1, 'Cumpleaños',   'Ministerio de cumpleaños y celebraciones',         '#ec4899', 'gift',     1),
(1, 'Educación',    'Ministerio de educación y formación bíblica',      '#10b981', 'book',     1),
(1, 'Evangelismo',  'Ministerio de evangelismo y misiones',             '#f97316', 'globe',    1),
(1, 'Jóvenes',      'Ministerio de jóvenes y adolescentes',             '#0ea5e9', 'users',    1),
(1, 'Mantenimiento','Ministerio de mantenimiento y servicios',          '#64748b', 'wrench',   1),
(1, 'Niños',        'Ministerio para niños y escuela dominical',        '#f59e0b', 'baby',     1),
(1, 'Parejas',      'Ministerio de parejas y matrimonios',              '#d946ef', 'heart',    1),
(1, 'Tercera Edad', 'Ministerio de adultos mayores y tercera edad',     '#14b8a6', 'heart',    1);

-- ────────────────────────────────────────────────────────────
-- Configuraciones por defecto de la iglesia
-- ────────────────────────────────────────────────────────────
INSERT IGNORE INTO `settings` (`church_id`, `key`, `value`, `type`) VALUES
(1, 'inactive_weeks_alert',         '4',   'integer'),
(1, 'send_birthday_notifications',  '1',   'boolean'),
(1, 'require_mfa',                  '0',   'boolean'),
(1, 'receipt_prefix',               'REC', 'string'),
(1, 'receipt_counter',              '1',   'integer');


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- FIN DEL DUMP
-- ============================================================
