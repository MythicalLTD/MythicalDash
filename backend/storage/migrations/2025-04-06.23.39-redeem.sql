CREATE TABLE
	`mythicaldash_redeem_codes` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`code` TEXT NOT NULL,
		`uses` INT NOT NULL,
		`coins` INT NOT NULL,
		`enabled` ENUM ('false', 'true') NOT NULL DEFAULT 'true',
		`deleted` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`locked` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;