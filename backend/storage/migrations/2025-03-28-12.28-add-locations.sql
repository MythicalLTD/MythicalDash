CREATE TABLE
	`mythicaldash_locations` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`name` TEXT NOT NULL,
		`description` TEXT NOT NULL,
		`pterodactyl_location_id` INT NOT NULL,
		`node_ip` TEXT NOT NULL,
		`status` ENUM ('online', 'offline', 'maintenance') NOT NULL,
		`deleted` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`locked` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;