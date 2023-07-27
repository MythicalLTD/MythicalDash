


DROP TABLE IF EXISTS `mythicaldash_apikeys`;
CREATE TABLE `mythicaldash_apikeys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `skey` text NOT NULL,
  `ownerkey` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_apikeys` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_logs`;
CREATE TABLE `mythicaldash_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log` text NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `type` enum('auth','action','error') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_logs` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_resetpasswords`;
CREATE TABLE `mythicaldash_resetpasswords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` text DEFAULT NULL,
  `user-apikey` text NOT NULL,
  `user-resetkeycode` text NOT NULL,
  `ip_addres` text NOT NULL,
  `status` enum('valid','unknown','expired') NOT NULL DEFAULT 'valid',
  `dateinfo` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_resetpasswords` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_settings`;
CREATE TABLE `mythicaldash_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL DEFAULT '\'MythicalDash\'',
  `logo` text NOT NULL DEFAULT 'https://avatars.githubusercontent.com/u/117385445',
  `seo_description` text NOT NULL DEFAULT '\'MythicalDash is a feature-rich and user-friendly client area for Pterodactyl, designed to simplify server management. With MythicalDash, you have unparalleled control over your hosting environment, effortlessly managing game servers, databases, files, and more. Experience seamless server administration, enhanced security, and optimized performance with MythicalDash, your ultimate solution for streamlined Pterodactyl server management.\'',
  `seo_keywords` text NOT NULL DEFAULT 'Pterodactyl client area, server management, game servers, MythicalDash, advanced features, server administration, streamlined management, hosting environment, database management, file management, efficient performance, enhanced security, optimized server management.',
  `reCAPTCHA_sitekey` text DEFAULT NULL,
  `reCAPTCHA_secretkey` text DEFAULT NULL,
  `discord_invite` text NOT NULL DEFAULT 'https://discord.gg/7BZTmSK2D8',
  `smtpHost` text DEFAULT NULL,
  `smtpPort` text DEFAULT NULL,
  `smtpSecure` enum('ssl','tls') DEFAULT NULL,
  `smtpUsername` text DEFAULT NULL,
  `smtpPassword` text DEFAULT NULL,
  `fromEmail` text DEFAULT NULL,
  `PterodactylURL` text DEFAULT NULL,
  `PterodactylAPIKey` text DEFAULT NULL,
  `version` text NOT NULL DEFAULT '0.0.0.1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_settings` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_users`;
CREATE TABLE `mythicaldash_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` text NOT NULL,
  `username` text NOT NULL,
  `first_name` text NOT NULL,
  `last_name` text NOT NULL,
  `password` text NOT NULL,
  `api_key` text NOT NULL,
  `avatar` text NOT NULL DEFAULT 'https://cdn.discordapp.com/embed/avatars/0.png',
  `role` enum('Administrator','User') NOT NULL DEFAULT 'User',
  `last_login` datetime NOT NULL DEFAULT current_timestamp(),
  `last_ip` text DEFAULT NULL,
  `first_ip` text DEFAULT NULL,
  `registred` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_users` WRITE;
UNLOCK TABLES;


