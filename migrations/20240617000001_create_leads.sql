-- Core leads table — mirrors setup/install.sql exactly.
-- Safe to re-run: CREATE TABLE IF NOT EXISTS only.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `leads` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`       VARCHAR(120)  NOT NULL,
  `email`           VARCHAR(180)  NOT NULL,
  `whatsapp`        VARCHAR(40)   NOT NULL,
  `country`         VARCHAR(80)   NOT NULL,
  `travel_interest` VARCHAR(40)   NOT NULL,
  `lead_source`     VARCHAR(100)  DEFAULT 'direct-form',
  `utm_source`      VARCHAR(100)  DEFAULT NULL,
  `utm_medium`      VARCHAR(100)  DEFAULT NULL,
  `utm_campaign`    VARCHAR(100)  DEFAULT NULL,
  `utm_content`     VARCHAR(100)  DEFAULT NULL,
  `utm_term`        VARCHAR(100)  DEFAULT NULL,
  `ip_address`      VARCHAR(45)   DEFAULT NULL,
  `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leads_created_at`    (`created_at`),
  KEY `idx_leads_email`         (`email`),
  KEY `idx_leads_lead_source`   (`lead_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
