CREATE DATABASE IF NOT EXISTS katoc
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE katoc;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    appointment_reference VARCHAR(30) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    center_name VARCHAR(191) NOT NULL,
    center_location VARCHAR(191) NOT NULL,
    dialysis_type VARCHAR(50) NOT NULL,
    appointment_date DATE NOT NULL,
    session VARCHAR(20) NOT NULL,
    appointment_time TIME NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(50) NOT NULL,
    email VARCHAR(191) NOT NULL,
    emergency_contact_name VARCHAR(100) NOT NULL,
    emergency_contact_number VARCHAR(50) NOT NULL,
    notes TEXT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending Confirmation',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY appointments_reference_unique (appointment_reference),
    KEY appointments_user_id_index (user_id),
    KEY appointments_slot_index (center_name, appointment_date, appointment_time, status),
    CONSTRAINT appointments_status_check
        CHECK (status IN ('Pending Confirmation', 'Confirmed', 'Completed', 'Cancelled'))
) ENGINE=InnoDB;
