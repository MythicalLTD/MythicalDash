CREATE TABLE
	`mythicaldash_referral_uses` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`referral_code_id` INT NOT NULL,
		`referred_user_id` varchar(36) NOT NULL,
		`deleted` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		FOREIGN KEY (`referral_code_id`) REFERENCES `mythicaldash_referral_codes` (`id`),
		FOREIGN KEY (`referred_user_id`) REFERENCES `mythicaldash_users` (`uuid`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;