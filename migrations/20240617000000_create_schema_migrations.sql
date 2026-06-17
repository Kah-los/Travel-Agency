-- Tracks which migration files have been applied.
-- Safe to re-run: CREATE TABLE IF NOT EXISTS only.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename`   VARCHAR(255) NOT NULL,
  `applied_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_schema_migrations_filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
