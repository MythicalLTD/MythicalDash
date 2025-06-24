CREATE TABLE
	`mythicaldash_roles_permissions` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`role_id` INT NOT NULL,
		`permission` TEXT NOT NULL,
		`granted` ENUM ('false', 'true') NOT NULL DEFAULT 'true',
		`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`deleted` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		`locked` ENUM ('false', 'true') NOT NULL DEFAULT 'false',
		PRIMARY KEY (`id`),
		FOREIGN KEY (`role_id`) REFERENCES `mythicaldash_roles` (`id`)
	) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO `mythicaldash_roles_permissions` (`role_id`, `permission`, `granted`) VALUES
(7, 'admin.root', 'true'),
(8, 'admin.root', 'true');