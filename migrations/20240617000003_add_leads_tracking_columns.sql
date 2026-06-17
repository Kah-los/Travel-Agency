-- Guarded ALTERs for databases created before lead-source / UTM tracking.
-- Each column is added only when missing — safe to re-run on any install.

SET NAMES utf8mb4;

-- lead_source
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'lead_source'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `leads` ADD COLUMN `lead_source` VARCHAR(100) DEFAULT ''direct-form''',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- utm_source
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'utm_source'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `leads` ADD COLUMN `utm_source` VARCHAR(100) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- utm_medium
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'utm_medium'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `leads` ADD COLUMN `utm_medium` VARCHAR(100) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- utm_campaign
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'utm_campaign'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `leads` ADD COLUMN `utm_campaign` VARCHAR(100) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- utm_content
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'utm_content'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `leads` ADD COLUMN `utm_content` VARCHAR(100) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- utm_term
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'utm_term'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `leads` ADD COLUMN `utm_term` VARCHAR(100) DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index on lead_source (skip if already present)
SET @idx_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND INDEX_NAME = 'idx_leads_lead_source'
);
SET @sql = IF(@idx_exists = 0,
  'ALTER TABLE `leads` ADD KEY `idx_leads_lead_source` (`lead_source`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
