-- =============================================================
--  TravelWithNaomi — Database Installation Script
--  Vortex365 Referral Landing Page
-- -------------------------------------------------------------
--  Run this once in phpMyAdmin (cPanel) or the Railway MySQL
--  console to create the tables this site needs.
--  It is safe to re-run: each table is dropped first.
-- -------------------------------------------------------------
--  MIGRATION NOTE (existing installs only)
--  If you ALREADY imported an earlier version of this file and
--  have live leads you don't want to drop, do NOT re-run the
--  whole script (it drops tables). Instead run these once to add
--  the lead-source + UTM tracking columns to your existing table:
--
--    ALTER TABLE leads ADD COLUMN lead_source  VARCHAR(100) DEFAULT 'direct-form';
--    ALTER TABLE leads ADD COLUMN utm_source   VARCHAR(100) DEFAULT NULL;
--    ALTER TABLE leads ADD COLUMN utm_medium   VARCHAR(100) DEFAULT NULL;
--    ALTER TABLE leads ADD COLUMN utm_campaign VARCHAR(100) DEFAULT NULL;
--    ALTER TABLE leads ADD COLUMN utm_content  VARCHAR(100) DEFAULT NULL;
--    ALTER TABLE leads ADD COLUMN utm_term     VARCHAR(100) DEFAULT NULL;
--
--  New installs need nothing extra: the columns are in the
--  CREATE TABLE statement below.
-- =============================================================

-- Use sensible defaults for text storage.
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- -------------------------------------------------------------
--  Table: leads
--  Every visitor who submits the lead-capture form.
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `leads`;
CREATE TABLE `leads` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`       VARCHAR(120)  NOT NULL,
  `email`           VARCHAR(180)  NOT NULL,
  `whatsapp`        VARCHAR(40)   NOT NULL,
  `country`         VARCHAR(80)   NOT NULL,
  `travel_interest` VARCHAR(40)   NOT NULL,
  `lead_source`     VARCHAR(100)  DEFAULT 'direct-form',  -- which card/URL produced the lead
  `utm_source`      VARCHAR(100)  DEFAULT NULL,           -- campaign attribution (UTM)
  `utm_medium`      VARCHAR(100)  DEFAULT NULL,
  `utm_campaign`    VARCHAR(100)  DEFAULT NULL,
  `utm_content`     VARCHAR(100)  DEFAULT NULL,
  `utm_term`        VARCHAR(100)  DEFAULT NULL,
  `ip_address`      VARCHAR(45)   DEFAULT NULL,   -- IPv4 or IPv6
  `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leads_created_at`  (`created_at`),
  KEY `idx_leads_email`       (`email`),
  KEY `idx_leads_lead_source` (`lead_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  Table: referral_clicks
--  Logged every time the form successfully sends a visitor
--  through to the Vortex365 referral link.
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `referral_clicks`;
CREATE TABLE `referral_clicks` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `clicked_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_clicks_clicked_at` (`clicked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
