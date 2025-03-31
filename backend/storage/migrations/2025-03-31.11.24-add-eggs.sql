CREATE TABLE
	`mythicaldash_eggs` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`name` TEXT NOT NULL,
		`description` TEXT NOT NULL,
		`category` INT (16) NOT NULL,
		`pterodactyl_egg_id` INT (16) NOT NULL,
		`enabled` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`deleted` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`locked` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY (`category`) REFERENCES `mythicaldash_eggs_categories` (`id`),
		PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;