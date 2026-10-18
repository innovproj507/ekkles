-- ============================================================
-- EKKLES — Schema Completo de Base de Datos
-- Motor: MySQL 8+ / MariaDB 10.6+
-- Charset: utf8mb4
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- TABLA: churches (Multi-tenant)
-- ============================================================
CREATE TABLE IF NOT EXISTS `churches` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `uuid`        CHAR(36)        NOT NULL UNIQUE,
  `name`        VARCHAR(150)    NOT NULL,
  `slogan`      VARCHAR(255)    NULL,
  `logo`        VARCHAR(255)    NULL,
  `address`     VARCHAR(255)    NULL,
  `city`        VARCHAR(100)    NULL,
  `country`     VARCHAR(100)    NOT NULL DEFAULT 'Colombia',
  `phone`       VARCHAR(30)     NULL,
  `email`       VARCHAR(150)    NULL,
  `website`     VARCHAR(255)    NULL,
  `timezone`    VARCHAR(50)     NOT NULL DEFAULT 'America/Bogota',
  `currency`    CHAR(3)         NOT NULL DEFAULT 'USD',
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_churches_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: users (Usuarios del sistema)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`           INT UNSIGNED    NOT NULL,
  `uuid`                CHAR(36)        NOT NULL UNIQUE,
  `name`                VARCHAR(100)    NOT NULL,
  `email`               VARCHAR(150)    NOT NULL,
  `password`            VARCHAR(255)    NOT NULL,
  `role`                ENUM('super_admin','ministro_principal','evangelista','administrador','tesorero','lider_ministerio','coordinador','secretario','anciano','diacono') NOT NULL DEFAULT 'diacono',
  `avatar`              VARCHAR(255)    NULL,
  `phone`               VARCHAR(30)     NULL,
  `is_active`           TINYINT(1)      NOT NULL DEFAULT 1,
  `mfa_enabled`         TINYINT(1)      NOT NULL DEFAULT 0,
  `mfa_secret`          VARCHAR(100)    NULL,
  `last_login_at`       DATETIME        NULL,
  `last_login_ip`       VARCHAR(45)     NULL,
  `login_attempts`      TINYINT         NOT NULL DEFAULT 0,
  `locked_until`        DATETIME        NULL,
  `password_reset_token`VARCHAR(100)    NULL,
  `password_reset_at`   DATETIME        NULL,
  `email_verified_at`   DATETIME        NULL,
  `remember_token`      VARCHAR(100)    NULL,
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`          DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email_church` (`email`, `church_id`),
  INDEX `idx_users_church` (`church_id`),
  INDEX `idx_users_role` (`role`),
  CONSTRAINT `fk_users_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: members (Miembros de la iglesia)
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
  CONSTRAINT `fk_members_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_members_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: families (Grupos familiares)
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
  CONSTRAINT `fk_families_head` FOREIGN KEY (`head_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: family_members (Relación miembro-familia)
-- ============================================================
CREATE TABLE IF NOT EXISTS `family_members` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `family_id`   INT UNSIGNED    NOT NULL,
  `member_id`   INT UNSIGNED    NOT NULL,
  `relationship`ENUM('jefe_hogar','conyuge','hijo','hija','padre','madre','abuelo','abuela','otro') NOT NULL DEFAULT 'otro',
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_family_member` (`family_id`, `member_id`),
  CONSTRAINT `fk_fm_family` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fm_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: ministries (Ministerios)
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
  CONSTRAINT `fk_ministries_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ministries_leader` FOREIGN KEY (`leader_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: ministry_members (Miembros por ministerio)
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
  CONSTRAINT `fk_mm_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: event_types (Tipos de eventos personalizables)
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
-- TABLA: events (Cultos, conferencias, eventos)
-- ============================================================
CREATE TABLE IF NOT EXISTS `events` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`       INT UNSIGNED    NOT NULL,
  `uuid`            CHAR(36)        NOT NULL UNIQUE,
  `title`           VARCHAR(200)    NOT NULL,
  `description`     TEXT            NULL,
  `event_type_id`   INT UNSIGNED    NOT NULL,
  `start_datetime`  DATETIME        NOT NULL,
  `end_datetime`    DATETIME        NULL,
  `location`        VARCHAR(255)    NULL,
  `online_url`      VARCHAR(500)    NULL,
  `max_capacity`    INT             NULL,
  `is_recurring`    TINYINT(1)      NOT NULL DEFAULT 0,
  `recurrence_rule` VARCHAR(255)    NULL,
  `registration_open` TINYINT(1)   NOT NULL DEFAULT 0,
  `qr_code`         VARCHAR(255)    NULL,
  `ministry_id`     INT UNSIGNED    NULL,
  `created_by`      INT UNSIGNED    NULL,
  `status`          ENUM('borrador','publicado','cancelado','finalizado') NOT NULL DEFAULT 'publicado',
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`      DATETIME        NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_events_church` (`church_id`),
  INDEX `idx_events_start` (`start_datetime`),
  CONSTRAINT `fk_events_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_events_type` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_events_ministry` FOREIGN KEY (`ministry_id`) REFERENCES `ministries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: event_attendance (Asistencia a eventos)
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
  INDEX `idx_ea_event` (`event_id`),
  INDEX `idx_ea_member` (`member_id`),
  CONSTRAINT `fk_ea_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ea_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ea_checkin_by` FOREIGN KEY (`checkin_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: simple_attendances (Registro rápido por código QR)
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
-- TABLA: event_teams (Equipos de servicio por evento)
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
  CONSTRAINT `fk_et_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_et_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: donations (Ofrendas, ofrendas generales, donaciones)
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
  INDEX `idx_donations_church` (`church_id`),
  INDEX `idx_donations_member` (`member_id`),
  INDEX `idx_donations_period` (`period_year`, `period_month`),
  CONSTRAINT `fk_donations_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_donations_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_donations_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_donations_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_donations_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_donations_category` FOREIGN KEY (`category_id`) REFERENCES `finance_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: finance_categories (Categorías contables)
-- ============================================================
CREATE TABLE IF NOT EXISTS `finance_categories` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`   INT UNSIGNED    NOT NULL,
  `name`        VARCHAR(100)    NOT NULL,
  `type`        ENUM('ingreso','egreso') NOT NULL,
  `target_module`VARCHAR(50)     NULL,
  `parent_id`   INT UNSIGNED    NULL,
  `code`        VARCHAR(20)     NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_fc_church` (`church_id`),
  CONSTRAINT `fk_fc_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fc_parent` FOREIGN KEY (`parent_id`) REFERENCES `finance_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: finances (Transacciones financieras)
-- ============================================================
CREATE TABLE IF NOT EXISTS `finances` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`       INT UNSIGNED    NOT NULL,
  `uuid`            CHAR(36)        NOT NULL UNIQUE,
  `type`            ENUM('ingreso','egreso') NOT NULL,
  `category_id`     INT UNSIGNED    NOT NULL,
  `target_module`   VARCHAR(50)     NULL,
  `description`     VARCHAR(255)    NOT NULL,
  `amount`          DECIMAL(15,2)   NOT NULL,
  `currency`        CHAR(3)         NOT NULL DEFAULT 'USD',
  `payment_method`  ENUM('efectivo','transferencia','tarjeta','cheque','otro') NOT NULL DEFAULT 'efectivo',
  `reference`       VARCHAR(100)    NULL,
  `invoice_number`  VARCHAR(50)     NULL,
  `provider_ruc`    VARCHAR(30)     NULL,
  `provider_name`   VARCHAR(150)    NULL,
  `transaction_date`DATE            NOT NULL,
  `period_month`    TINYINT         NOT NULL,
  `period_year`     SMALLINT        NOT NULL,
  `budget_id`       INT UNSIGNED    NULL,
  `donation_id`     INT UNSIGNED    NULL,
  `attachments`     JSON            NULL,
  `notes`           TEXT            NULL,
  `is_reconciled`   TINYINT(1)      NOT NULL DEFAULT 0,
  `created_by`      INT UNSIGNED    NULL,
  `approved_by`     INT UNSIGNED    NULL,
  `status`          ENUM('pendiente','aprobado','rechazado','anulado') NOT NULL DEFAULT 'aprobado',
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_finances_church` (`church_id`),
  INDEX `idx_finances_type` (`type`),
  INDEX `idx_finances_period` (`period_year`, `period_month`),
  CONSTRAINT `fk_finances_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_finances_category` FOREIGN KEY (`category_id`) REFERENCES `finance_categories` (`id`),
  CONSTRAINT `fk_finances_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_finances_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_finances_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: budgets (Presupuestos anuales)
-- ============================================================
CREATE TABLE IF NOT EXISTS `budgets` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`       INT UNSIGNED    NOT NULL,
  `category_id`     INT UNSIGNED    NOT NULL,
  `year`            SMALLINT        NOT NULL,
  `month`           TINYINT         NULL COMMENT 'NULL = anual',
  `amount`          DECIMAL(15,2)   NOT NULL,
  `notes`           TEXT            NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_budgets_church` (`church_id`),
  CONSTRAINT `fk_budgets_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_budgets_category` FOREIGN KEY (`category_id`) REFERENCES `finance_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: pastoral_follows (Seguimiento pastoral)
-- ============================================================
CREATE TABLE IF NOT EXISTS `pastoral_follows` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `church_id`       INT UNSIGNED    NOT NULL,
  `member_id`       INT UNSIGNED    NOT NULL,
  `assigned_to`     INT UNSIGNED    NULL COMMENT 'Pastor/Líder asignado',
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
  INDEX `idx_pf_church` (`church_id`),
  INDEX `idx_pf_member` (`member_id`),
  INDEX `idx_pf_assigned` (`assigned_to`),
  INDEX `idx_pf_status` (`status`),
  CONSTRAINT `fk_pf_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pf_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pf_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pf_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: notifications (Cola de notificaciones)
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
  INDEX `idx_notif_user` (`user_id`),
  INDEX `idx_notif_church` (`church_id`),
  CONSTRAINT `fk_notif_church` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: audit_logs (Bitácora completa de auditoría)
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `church_id`     INT UNSIGNED     NULL,
  `user_id`       INT UNSIGNED     NULL,
  `user_name`     VARCHAR(100)     NULL COMMENT 'Snapshot del nombre',
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
  INDEX `idx_audit_church` (`church_id`),
  INDEX `idx_audit_user` (`user_id`),
  INDEX `idx_audit_action` (`action`),
  INDEX `idx_audit_module` (`module`),
  INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: settings (Configuración por iglesia)
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
-- DATOS INICIALES (Seeds)
-- ============================================================

-- Iglesia por defecto
INSERT INTO `churches` (`id`, `uuid`, `name`, `city`, `country`, `timezone`, `currency`, `is_active`) VALUES
(1, UUID(), 'Mi Iglesia', 'Bogotá', 'Colombia', 'America/Bogota', 'USD', 1);

-- Usuario Super Administrador (contraseña: Admin123!)
INSERT INTO `users` (`church_id`, `uuid`, `name`, `email`, `password`, `role`, `is_active`, `email_verified_at`) VALUES
(1, UUID(), 'Administrador', 'admin@ekkles.local',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin123!
 'super_admin', 1, NOW());

-- Categorías financieras por defecto
INSERT INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`) VALUES
(1, 'Ofrendas', 'ingreso', 'ofrendas', 'ING-001'),
(1, 'Ofrendas Generales', 'ingreso', 'ofrendas', 'ING-002'),
(1, 'Ofrendas Especiales', 'ingreso', 'ofrendas', 'ING-003'),
(1, 'Donaciones', 'ingreso', 'donaciones', 'ING-004'),
(1, 'Eventos', 'ingreso', 'donaciones', 'ING-005'),
(1, 'Misiones', 'ingreso', 'donaciones', 'ING-006'),
(1, 'Construcción', 'ingreso', 'construccion', 'ING-007'),
(1, 'Nómina', 'egreso', 'ofrendas', 'EGR-001'),
(1, 'Servicios Públicos', 'egreso', 'ofrendas', 'EGR-002'),
(1, 'Mantenimiento', 'egreso', 'construccion', 'EGR-003'),
(1, 'Ayuda Social', 'egreso', 'donaciones', 'EGR-004'),
(1, 'Misiones (Egreso)', 'egreso', 'donaciones', 'EGR-005'),
(1, 'Compras', 'egreso', 'ofrendas', 'EGR-006'),
(1, 'Administración', 'egreso', 'ofrendas', 'EGR-007');

-- Ministerios por defecto
INSERT INTO `ministries` (`church_id`, `name`, `description`, `color`, `icon`, `is_active`) VALUES
(1, 'Adoración', 'Ministerio de alabanza y adoración', '#8b5cf6', 'music', 1),
(1, 'Benevolencia', 'Ministerio de ayuda social y benevolencia', '#ef4444', 'heart', 1),
(1, 'Comunicación', 'Ministerio de comunicación y medios', '#3b82f6', 'bullhorn', 1),
(1, 'Cumpleaños', 'Ministerio de cumpleaños y celebraciones', '#ec4899', 'gift', 1),
(1, 'Educación', 'Ministerio de educación y formación bíblica', '#10b981', 'book', 1),
(1, 'Evangelismo', 'Ministerio de evangelismo y misiones', '#f97316', 'globe', 1),
(1, 'Jóvenes', 'Ministerio de jóvenes y adolescentes', '#0ea5e9', 'users', 1),
(1, 'Mantenimiento', 'Ministerio de mantenimiento y servicios', '#64748b', 'wrench', 1),
(1, 'Niños', 'Ministerio para niños y escuela dominical', '#f59e0b', 'baby', 1),
(1, 'Parejas', 'Ministerio de parejas y matrimonios', '#d946ef', 'heart', 1),
(1, 'Tercera Edad', 'Ministerio de adultos mayores y tercera edad', '#14b8a6', 'heart', 1);

-- Configuraciones por defecto
INSERT INTO `settings` (`church_id`, `key`, `value`, `type`) VALUES
(1, 'inactive_weeks_alert', '4', 'integer'),
(1, 'send_birthday_notifications', '1', 'boolean'),
(1, 'require_mfa', '0', 'boolean'),
(1, 'receipt_prefix', 'REC', 'string'),
(1, 'receipt_counter', '1', 'integer');

SET FOREIGN_KEY_CHECKS = 1;
