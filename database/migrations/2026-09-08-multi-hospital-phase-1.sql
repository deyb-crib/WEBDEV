-- KATOC Phase 1 ownership migration.
-- Run once against the existing katoc database before creating Hospital Admins.
USE katoc;

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'dialysis_centers'
      AND COLUMN_NAME = 'facility_type'
);
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE dialysis_centers ADD COLUMN facility_type VARCHAR(30) NOT NULL DEFAULT ''Dialysis Center'' AFTER name',
    'SELECT 1'
);
PREPARE phase1_stmt FROM @sql;
EXECUTE phase1_stmt;
DEALLOCATE PREPARE phase1_stmt;

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'hospital_id'
);
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE users ADD COLUMN hospital_id INT UNSIGNED NULL AFTER role, ADD KEY users_hospital_id_index (hospital_id)',
    'SELECT 1'
);
PREPARE phase1_stmt FROM @sql;
EXECUTE phase1_stmt;
DEALLOCATE PREPARE phase1_stmt;

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'appointments'
      AND COLUMN_NAME = 'hospital_id'
);
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE appointments ADD COLUMN hospital_id INT UNSIGNED NULL AFTER user_id, ADD KEY appointments_hospital_id_index (hospital_id)',
    'SELECT 1'
);
PREPARE phase1_stmt FROM @sql;
EXECUTE phase1_stmt;
DEALLOCATE PREPARE phase1_stmt;

CREATE TABLE IF NOT EXISTS hospital_admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    hospital_id INT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY hospital_admins_user_unique (user_id),
    KEY hospital_admins_hospital_index (hospital_id),
    CONSTRAINT hospital_admins_status_check CHECK (status IN ('Active', 'Inactive'))
) ENGINE=InnoDB;

-- Backfill ownership for legacy appointments where the center name is known.
UPDATE appointments a
INNER JOIN dialysis_centers c ON c.name = a.center_name
SET a.hospital_id = c.id
WHERE a.hospital_id IS NULL;

-- Review any rows that could not be assigned before enabling strict ownership.
SELECT a.id, a.appointment_reference, a.center_name
FROM appointments a
WHERE a.hospital_id IS NULL;