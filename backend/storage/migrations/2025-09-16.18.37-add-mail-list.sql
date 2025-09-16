CREATE TABLE
	IF NOT EXISTS `mythicaldash_mail_list` (
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`queue_id` INT NOT NULL,
		`user_uuid` CHAR(36) NOT NULL,
		`deleted` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`locked` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		FOREIGN KEY (`queue_id`) REFERENCES `mythicaldash_mail_queue` (`id`) ON DELETE CASCADE,
		FOREIGN KEY (`user_uuid`) REFERENCES `mythicaldash_users` (`uuid`) ON DELETE CASCADE
	) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;