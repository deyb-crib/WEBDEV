USE katoc;

SET @column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appointments' AND COLUMN_NAME = 'rejection_reason'
);
SET @sql = IF(@column_exists = 0, 'ALTER TABLE appointments ADD COLUMN rejection_reason VARCHAR(500) NULL AFTER notes', 'SELECT 1');
PREPARE booking_crud_stmt FROM @sql;
EXECUTE booking_crud_stmt;
DEALLOCATE PREPARE booking_crud_stmt;

-- MySQL 5.5 ignores CHECK constraints; newer installations should include Rejected in the status check.