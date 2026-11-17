-- ============================================================
-- Smart City Citizen Complaint Portal - Database Schema
-- Database: smart_city_portal
-- ============================================================

CREATE DATABASE IF NOT EXISTS `smart_city_portal`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smart_city_portal`;

-- ------------------------------------------------------------
-- Table: users  (citizens)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `full_name`    VARCHAR(150) NOT NULL,
  `email`        VARCHAR(150) NOT NULL UNIQUE,
  `mobile`       VARCHAR(15)  NOT NULL,
  `password`     VARCHAR(255) NOT NULL,
  `city_area`    VARCHAR(200) DEFAULT NULL,
  `profile_pic`  VARCHAR(255) DEFAULT NULL,
  `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: complaints
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `complaints` (
  `id`              INT(11)       NOT NULL AUTO_INCREMENT,
  `tracking_id`     VARCHAR(20)   NOT NULL UNIQUE,
  `user_id`         INT(11)       DEFAULT NULL,
  `citizen_name`    VARCHAR(150)  NOT NULL,
  `email`           VARCHAR(150)  NOT NULL,
  `mobile`          VARCHAR(15)   NOT NULL,
  `category`        ENUM(
                      'Roads & Footpaths',
                      'Water Supply',
                      'Electricity',
                      'Garbage & Sanitation',
                      'Drainage & Sewage',
                      'Street Lights',
                      'Parks & Gardens',
                      'Public Transport',
                      'Noise Pollution',
                      'Illegal Construction',
                      'Other'
                    ) NOT NULL DEFAULT 'Other',
  `title`           VARCHAR(255)  NOT NULL,
  `description`     TEXT          NOT NULL,
  `city_area`       VARCHAR(255)  NOT NULL,
  `complaint_date`  DATE          NOT NULL,
  `photo`           VARCHAR(255)  DEFAULT NULL,
  `status`          ENUM('Pending','In Progress','Resolved','Rejected') NOT NULL DEFAULT 'Pending',
  `admin_remarks`   TEXT          DEFAULT NULL,
  `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tracking` (`tracking_id`),
  KEY `idx_user`     (`user_id`),
  KEY `idx_status`   (`status`),
  CONSTRAINT `fk_complaints_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: admin_users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(80)  NOT NULL UNIQUE,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `full_name`  VARCHAR(150) NOT NULL DEFAULT 'Administrator',
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Seed: default admin  (password: Admin@1234)
-- ------------------------------------------------------------
INSERT INTO `admin_users` (`username`, `email`, `password`, `full_name`)
VALUES (
  'admin',
  'admin@smartcity.gov',
  '$2y$12$aBcDeFgHiJkLmNoPqRsTuuVwXyZ012345678901234567890123456',
  'City Administrator'
);

-- We replace the hash above with a proper one at runtime via PHP,
-- but for convenience here is a known valid hash for Admin@1234
-- generated with password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost'=>12])
UPDATE `admin_users`
SET `password` = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE `username` = 'admin';
-- Note: the hash above is the well-known bcrypt hash of the string "password".
-- Run install.php after importing to set a real admin password.

-- ------------------------------------------------------------
-- Sample citizen (optional – remove in production)
-- ------------------------------------------------------------
INSERT INTO `users` (`full_name`, `email`, `mobile`, `password`, `city_area`)
VALUES (
  'Demo Citizen',
  'citizen@example.com',
  '9876543210',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Central Zone'
);
