CREATE TABLE
	`mythicaldash_linkvertise` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`code` TEXT NOT NULL,
		`user` VARCHAR(36) NOT NULL,
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`deleted` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`locked` ENUM ('false', 'true') NOT NULL DEFAULT 'false',	
		PRIMARY KEY (`id`),
		FOREIGN KEY (`user`) REFERENCES `mythicaldash_users` (`uuid`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;