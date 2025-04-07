CREATE TABLE
	`mythicaldash_referral_codes` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`user` varchar(36) NOT NULL,
		`code` varchar(128) NOT NULL,
		`deleted` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`locked` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		UNIQUE KEY `unique_code` (`code`),
		FOREIGN KEY (`user`) REFERENCES `mythicaldash_users` (`uuid`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;