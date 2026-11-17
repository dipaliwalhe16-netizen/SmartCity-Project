-- ============================================================
-- Smart City Portal – UPGRADE MIGRATION v2.0
-- Safe: only adds new columns/tables, preserves all existing data
-- Run in phpMyAdmin or: mysql -u root smart_city_portal < database_upgrade.sql
-- ============================================================

USE `smart_city_portal`;

-- ── Add new columns to complaints (safe ALTER IGNORE / IF NOT EXISTS) ──

-- Priority
ALTER TABLE `complaints`
  ADD COLUMN IF NOT EXISTS `priority`    ENUM('Low','Medium','High','Emergency') NOT NULL DEFAULT 'Medium' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `department`  ENUM('Road','Water','Electricity','Sanitation','Other') NOT NULL DEFAULT 'Other' AFTER `priority`,
  ADD COLUMN IF NOT EXISTS `latitude`    DECIMAL(10,7) DEFAULT NULL AFTER `city_area`,
  ADD COLUMN IF NOT EXISTS `longitude`   DECIMAL(10,7) DEFAULT NULL AFTER `latitude`,
  ADD COLUMN IF NOT EXISTS `media_files` TEXT          DEFAULT NULL COMMENT 'JSON array of uploaded file names' AFTER `photo`,
  ADD COLUMN IF NOT EXISTS `resolved_at` DATETIME      DEFAULT NULL AFTER `admin_remarks`;

-- ── Status history ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `complaint_history` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `complaint_id` INT(11)       NOT NULL,
  `old_status`   VARCHAR(30)   DEFAULT NULL,
  `new_status`   VARCHAR(30)   NOT NULL,
  `changed_by`   ENUM('admin','citizen') NOT NULL DEFAULT 'admin',
  `actor_id`     INT(11)       DEFAULT NULL,
  `actor_name`   VARCHAR(150)  DEFAULT NULL,
  `remarks`      TEXT          DEFAULT NULL,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cid` (`complaint_id`),
  CONSTRAINT `fk_ch_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Citizen notifications ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11)       NOT NULL,
  `complaint_id` INT(11)       DEFAULT NULL,
  `title`        VARCHAR(150)  NOT NULL,
  `message`      TEXT          NOT NULL,
  `type`         ENUM('status_change','assigned','resolved','rejected','reopened','general') NOT NULL DEFAULT 'general',
  `is_read`      TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_uid`  (`user_id`),
  KEY `idx_read` (`user_id`,`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Feedback / ratings after resolution ─────────────────────
CREATE TABLE IF NOT EXISTS `complaint_feedback` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `complaint_id` INT(11)       NOT NULL UNIQUE,
  `user_id`      INT(11)       DEFAULT NULL,
  `rating`       TINYINT(1)    NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `comment`      TEXT          DEFAULT NULL,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_fb_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Reopen log ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `complaint_reopens` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `complaint_id` INT(11)       NOT NULL,
  `user_id`      INT(11)       NOT NULL,
  `reason`       TEXT          NOT NULL,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ro_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── CSRF tokens ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `csrf_tokens` (
  `token`      VARCHAR(64)  NOT NULL,
  `session_id` VARCHAR(128) NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token`),
  KEY `idx_sess` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Add profile columns to users ────────────────────────────
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `address`    VARCHAR(255) DEFAULT NULL AFTER `city_area`,
  ADD COLUMN IF NOT EXISTS `updated_pw` DATETIME     DEFAULT NULL AFTER `updated_at`;

-- ── Add Maps API key to admin settings ──────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `key_name`  VARCHAR(80)  NOT NULL,
  `value`     TEXT         DEFAULT NULL,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `settings` (`key_name`,`value`) VALUES
  ('maps_api_key',   ''),
  ('site_name',      'Smart City Complaint Portal'),
  ('admin_email',    'admin@smartcity.gov'),
  ('max_file_size',  '10485760'),
  ('allowed_types',  'image/jpeg,image/png,image/webp,video/mp4,video/webm');

-- ── Seed initial history for existing complaints ─────────────
INSERT IGNORE INTO `complaint_history` (complaint_id, old_status, new_status, changed_by, actor_name, remarks, created_at)
SELECT id, NULL, status, 'admin', 'System Migration', 'Initial status from migration', created_at
FROM complaints
WHERE id NOT IN (SELECT DISTINCT complaint_id FROM complaint_history);
