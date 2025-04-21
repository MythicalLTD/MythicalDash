CREATE TABLE
	`mythicaldash_servers_queue_logs` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`build` INT NOT NULL,
		`log` TEXT NOT NULL,
		`deleted` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`locked` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`purge` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`expires_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		FOREIGN KEY (`build`) REFERENCES `mythicaldash_servers_queue` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;