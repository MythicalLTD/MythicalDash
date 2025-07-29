-- Create image hosting reports table
CREATE TABLE
	`mythicaldash_image_reports` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`image_id` VARCHAR(255) NOT NULL,
		`image_url` TEXT NOT NULL,
		`reason` VARCHAR(50) NOT NULL,
		`details` TEXT NULL,
		`reporter_ip` VARCHAR(45) NOT NULL,
		`user_agent` TEXT NULL,
		`reported_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`status` ENUM('pending', 'reviewed', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
		`admin_notes` TEXT NULL,
		`resolved_at` DATETIME NULL,
		`resolved_by` VARCHAR(255) NULL,
		`deleted` ENUM('false', 'true') NOT NULL DEFAULT 'false',
		PRIMARY KEY (`id`),
		INDEX `idx_image_id` (`image_id`),
		INDEX `idx_status` (`status`),
		INDEX `idx_reported_at` (`reported_at`),
		INDEX `idx_deleted` (`deleted`)
	) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;