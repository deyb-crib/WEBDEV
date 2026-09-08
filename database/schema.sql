CREATE DATABASE IF NOT EXISTS katoc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE katoc;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'patient',
    hospital_id INT UNSIGNED NULL,
    account_status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dialysis_centers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(191) NOT NULL,
    facility_type VARCHAR(30) NOT NULL DEFAULT 'Dialysis Center',
    address VARCHAR(191) NOT NULL,
    contact_number VARCHAR(50) NOT NULL,
    email VARCHAR(191) NOT NULL,
    operating_hours VARCHAR(100) NOT NULL,
    dialysis_types VARCHAR(191) NOT NULL,
    image_path VARCHAR(255) NULL,
    machine_count INT UNSIGNED NOT NULL DEFAULT 0,
    available_slots INT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY dialysis_centers_name_unique (name)
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
    PRIMARY KEY (id), KEY center_schedules_date_index (schedule_date, schedule_time)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    appointment_reference VARCHAR(30) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    hospital_id INT UNSIGNED NULL,
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
    rejection_reason VARCHAR(500) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending Confirmation',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), UNIQUE KEY appointments_reference_unique (appointment_reference),
    KEY appointments_user_id_index (user_id), KEY appointments_hospital_id_index (hospital_id), KEY appointments_slot_index (center_name, appointment_date, appointment_time, status),
    CONSTRAINT appointments_status_check CHECK (status IN ('Pending Confirmation', 'Confirmed', 'Rejected', 'Completed', 'Cancelled'))
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hospital_admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    hospital_id INT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY hospital_admins_user_unique (user_id),
    KEY hospital_admins_hospital_index (hospital_id)
) ENGINE=InnoDB;

-- Existing patient accounts and operational records for a ready-to-use installation.
INSERT INTO users (name, email, password_hash, role, account_status) VALUES
('Mark Santos', 'mark.santos@example.com', '$2y$12$lwfcijUTHTk/NncuyaZv8OEgUJ/3esj.JpIu3GdNT2v/AO84mCyf2', 'patient', 'Active'),
('Ethan Reyes', 'ethan.reyes@example.com', '$2y$12$lwfcijUTHTk/NncuyaZv8OEgUJ/3esj.JpIu3GdNT2v/AO84mCyf2', 'patient', 'Active'),
('Angela Cruz', 'angela.cruz@example.com', '$2y$12$lwfcijUTHTk/NncuyaZv8OEgUJ/3esj.JpIu3GdNT2v/AO84mCyf2', 'patient', 'Active'),
('Sofia Garcia', 'sofia.garcia@example.com', '$2y$12$lwfcijUTHTk/NncuyaZv8OEgUJ/3esj.JpIu3GdNT2v/AO84mCyf2', 'patient', 'Active'),
('Noah Villanueva', 'noah.villanueva@example.com', '$2y$12$lwfcijUTHTk/NncuyaZv8OEgUJ/3esj.JpIu3GdNT2v/AO84mCyf2', 'patient', 'Active')
ON DUPLICATE KEY UPDATE name = VALUES(name), role = 'patient', account_status = 'Active';

INSERT IGNORE INTO dialysis_centers (name,address,contact_number,email,operating_hours,dialysis_types,machine_count,available_slots,status) VALUES
('Love Center','Dumaguete City, Negros Oriental','+63 917 123 4501','love@katoc.local','8:00 am - 5:00 pm','Hemodialysis, Peritoneal Dialysis',20,12,'Active'),
('HemoCent','Valencia, Negros Oriental','+63 917 123 4502','hemocent@katoc.local','8:00 am - 5:00 pm','Hemodialysis',16,8,'Active'),
('Sibulan Dialysis Center','Sibulan, Negros Oriental','+63 917 123 4503','sibulan@katoc.local','8:00 am - 5:00 pm','Hemodialysis, Peritoneal Dialysis',14,6,'Active'),
('Nephrology Center of Dumaguete City Dialysis, Inc.','Dumaguete City, Negros Oriental','+63 917 123 4504','nephrology@katoc.local','8:00 am - 5:00 pm','Hemodialysis, Peritoneal Dialysis',18,9,'Active'),
('Bais Community Hospital','Bais City, Negros Oriental','+63 917 123 4505','bais@katoc.local','8:00 am - 5:00 pm','Hemodialysis',12,5,'Active'),
('Tanjay Renal Care Center','Tanjay City, Negros Oriental','+63 917 123 4506','tanjay@katoc.local','8:00 am - 5:00 pm','Hemodialysis',14,7,'Active'),
('Bayawan Medical Center','Bayawan City, Negros Oriental','+63 917 123 4507','bayawan@katoc.local','8:00 am - 5:00 pm','Hemodialysis, Peritoneal Dialysis',10,4,'Active'),
('Dumaguete Kidney Institute','Dumaguete City, Negros Oriental','+63 917 123 4508','kidney.institute@katoc.local','8:00 am - 5:00 pm','Hemodialysis, Peritoneal Dialysis',22,11,'Active');

INSERT INTO center_schedules (center_id,schedule_date,schedule_time,dialysis_type,available_slots,reserved_slots,status)
SELECT id,'2026-09-10','08:00:00','Hemodialysis',8,2,'Available' FROM dialysis_centers WHERE name='Love Center'
UNION ALL SELECT id,'2026-09-10','13:00:00','Peritoneal Dialysis',5,1,'Available' FROM dialysis_centers WHERE name='Love Center'
UNION ALL SELECT id,'2026-09-11','10:00:00','Hemodialysis',6,3,'Available' FROM dialysis_centers WHERE name='HemoCent'
UNION ALL SELECT id,'2026-09-11','15:00:00','Hemodialysis',4,4,'Available' FROM dialysis_centers WHERE name='HemoCent'
UNION ALL SELECT id,'2026-09-12','08:00:00','Hemodialysis',7,2,'Available' FROM dialysis_centers WHERE name='Sibulan Dialysis Center'
UNION ALL SELECT id,'2026-09-12','13:00:00','Peritoneal Dialysis',3,1,'Available' FROM dialysis_centers WHERE name='Sibulan Dialysis Center';

INSERT IGNORE INTO appointments (appointment_reference,user_id,center_name,center_location,dialysis_type,appointment_date,session,appointment_time,patient_name,contact_number,email,emergency_contact_name,emergency_contact_number,notes,status) VALUES
('KATOC-20260910-001',6,'Love Center','Dumaguete City, Negros Oriental','Hemodialysis','2026-09-10','Morning','08:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Appointment request.','Pending Confirmation'),
('KATOC-20260911-002',6,'HemoCent','Valencia, Negros Oriental','Peritoneal Dialysis','2026-09-11','Morning','10:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Appointment request.','Confirmed'),
('KATOC-20260912-003',6,'Sibulan Dialysis Center','Sibulan, Negros Oriental','Hemodialysis','2026-09-12','Afternoon','13:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Appointment request.','Pending Confirmation'),
('KATOC-20260913-004',6,'Bais Community Hospital','Bais City, Negros Oriental','Hemodialysis','2026-09-13','Afternoon','15:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Appointment request.','Confirmed'),
('KATOC-20260914-005',6,'Dumaguete Kidney Institute','Dumaguete City, Negros Oriental','Peritoneal Dialysis','2026-09-14','Evening','17:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Appointment request.','Pending Confirmation'),
('KATOC-20260901-006',6,'Love Center','Dumaguete City, Negros Oriental','Hemodialysis','2026-09-01','Morning','08:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Previous appointment.','Completed'),
('KATOC-20260902-007',6,'HemoCent','Valencia, Negros Oriental','Hemodialysis','2026-09-02','Morning','10:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Previous appointment.','Completed'),
('KATOC-20260903-008',6,'Sibulan Dialysis Center','Sibulan, Negros Oriental','Peritoneal Dialysis','2026-09-03','Afternoon','13:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Previous appointment.','Cancelled'),
('KATOC-20260904-009',6,'Bais Community Hospital','Bais City, Negros Oriental','Hemodialysis','2026-09-04','Afternoon','15:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Previous appointment.','Completed'),
('KATOC-20260905-010',6,'Dumaguete Kidney Institute','Dumaguete City, Negros Oriental','Hemodialysis','2026-09-05','Evening','17:00:00','Dave','+639171234501','markdaveopena968@gmail.com','Maria Dela Cruz','+639181111001','Previous appointment.','Cancelled');
