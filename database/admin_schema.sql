USE katoc;

-- Run this migration once for an existing KATOC database.
ALTER TABLE dialysis_centers
    ADD COLUMN facility_type VARCHAR(30) NOT NULL DEFAULT 'Dialysis Center' AFTER name;

ALTER TABLE users
    ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'patient',
    ADD COLUMN account_status VARCHAR(20) NOT NULL DEFAULT 'Active',
    ADD COLUMN hospital_id INT UNSIGNED NULL;

CREATE TABLE IF NOT EXISTS dialysis_centers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(191) NOT NULL,
    facility_type VARCHAR(30) NOT NULL DEFAULT 'Dialysis Center',
    address VARCHAR(191) NOT NULL,
    contact_number VARCHAR(50) NOT NULL,
    email VARCHAR(191) NOT NULL,
    operating_hours VARCHAR(100) NOT NULL,
    dialysis_types VARCHAR(191) NOT NULL,
    machine_count INT UNSIGNED NOT NULL DEFAULT 0,
    available_slots INT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY dialysis_centers_name_unique (name),
    CONSTRAINT dialysis_centers_status_check CHECK (status IN ('Active', 'Inactive'))
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS center_schedules (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    center_id INT UNSIGNED NOT NULL,
    schedule_date DATE NOT NULL,
    schedule_time TIME NOT NULL,
    dialysis_type VARCHAR(50) NOT NULL,
    available_slots INT UNSIGNED NOT NULL DEFAULT 0,
    reserved_slots INT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY center_schedules_date_index (schedule_date, schedule_time),
    CONSTRAINT center_schedules_status_check CHECK (status IN ('Available', 'Unavailable'))
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY admin_notifications_read_index (is_read, created_at)
) ENGINE=InnoDB;

INSERT INTO users (name, email, password_hash, role, account_status)
VALUES ('KATOC Administrator', 'admin@katoc.local', '$2y$12$W4IFQ.f2e8TUqyUOPQTVje3EUtaoeZ2LaYUiy7H7VIsyaBnw6tQ3C', 'admin', 'Active')
ON DUPLICATE KEY UPDATE role = 'admin', account_status = 'Active';
