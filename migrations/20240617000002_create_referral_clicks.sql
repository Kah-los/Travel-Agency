-- Referral click log — mirrors setup/install.sql exactly.
-- Safe to re-run: CREATE TABLE IF NOT EXISTS only.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `referral_clicks` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `clicked_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_clicks_clicked_at` (`clicked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
