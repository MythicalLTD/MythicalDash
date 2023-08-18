


DROP TABLE IF EXISTS `mythicaldash_apikeys`;
CREATE TABLE `mythicaldash_apikeys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `skey` text NOT NULL,
  `ownerkey` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_apikeys` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_eggs`;
CREATE TABLE `mythicaldash_eggs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `egg` text NOT NULL,
  `nest` text NOT NULL,
  `category` text NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_eggs` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_locations`;
CREATE TABLE `mythicaldash_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `locationid` text NOT NULL,
  `status` enum('ONLINE','OFFLINE','MAINTENANCE') NOT NULL DEFAULT 'ONLINE',
  `slots` text NOT NULL,
  `created` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_locations` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_login_logs`;
CREATE TABLE `mythicaldash_login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddr` text NOT NULL,
  `userkey` text NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_login_logs` WRITE;
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


DROP TABLE IF EXISTS `mythicaldash_redeem`;
CREATE TABLE `mythicaldash_redeem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` text NOT NULL,
  `uses` text NOT NULL,
  `coins` text NOT NULL,
  `ram` text NOT NULL,
  `disk` text NOT NULL,
  `cpu` text NOT NULL,
  `server_limit` text NOT NULL,
  `ports` text NOT NULL,
  `databases` text NOT NULL,
  `backups` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_redeem` WRITE;
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


DROP TABLE IF EXISTS `mythicaldash_servers`;
CREATE TABLE `mythicaldash_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` text NOT NULL,
  `uid` text NOT NULL,
  `location` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_servers` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_servers_queue`;
CREATE TABLE `mythicaldash_servers_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `ram` text NOT NULL,
  `disk` text NOT NULL,
  `cpu` text NOT NULL,
  `xtra_ports` text NOT NULL,
  `databases` text NOT NULL,
  `backuplimit` text NOT NULL,
  `location` text NOT NULL,
  `ownerid` text NOT NULL,
  `type` text NOT NULL,
  `egg` text NOT NULL,
  `puid` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_servers_queue` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_settings`;
CREATE TABLE `mythicaldash_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL DEFAULT '\'MythicalDash\'',
  `logo` text NOT NULL DEFAULT 'https://avatars.githubusercontent.com/u/117385445',
  `seo_description` text NOT NULL DEFAULT '\'MythicalDash is a feature-rich and user-friendly client area for Pterodactyl, designed to simplify server management. With MythicalDash, you have unparalleled control over your hosting environment, effortlessly managing game servers, databases, files, and more. Experience seamless server administration, enhanced security, and optimized performance with MythicalDash, your ultimate solution for streamlined Pterodactyl server management.\'',
  `seo_keywords` text NOT NULL DEFAULT 'Pterodactyl client area, server management, game servers, MythicalDash, advanced features, server administration, streamlined management, hosting environment, database management, file management, efficient performance, enhanced security, optimized server management.',
  `enable_turnstile` enum('false','true') NOT NULL DEFAULT 'false',
  `turnstile_sitekey` text DEFAULT NULL,
  `turnstile_secretkey` text DEFAULT NULL,
  `discord_invite` text NOT NULL DEFAULT 'https://discord.gg/7BZTmSK2D8',
  `enable_discord_link` enum('false','true') NOT NULL DEFAULT 'false',
  `discord_serverid` text NOT NULL DEFAULT '1080933452091752448',
  `discord_clientid` text DEFAULT NULL,
  `discord_clientsecret` text DEFAULT NULL,
  `enable_smtp` enum('false','true') NOT NULL DEFAULT 'false',
  `smtpHost` text DEFAULT NULL,
  `smtpPort` text DEFAULT NULL,
  `smtpSecure` enum('ssl','tls') DEFAULT NULL,
  `smtpUsername` text DEFAULT NULL,
  `smtpPassword` text DEFAULT NULL,
  `fromEmail` text DEFAULT NULL,
  `PterodactylURL` text DEFAULT NULL,
  `PterodactylAPIKey` text DEFAULT NULL,
  `def_coins` text DEFAULT NULL,
  `def_memory` text DEFAULT NULL,
  `def_disk_space` text DEFAULT NULL,
  `def_cpu` text DEFAULT NULL,
  `def_server_limit` text DEFAULT NULL,
  `def_port` text DEFAULT NULL,
  `def_db` text DEFAULT NULL,
  `def_backups` text DEFAULT NULL,
  `price_memory` text DEFAULT NULL,
  `price_cpu` text DEFAULT NULL,
  `price_disk_space` text DEFAULT NULL,
  `price_server_limit` text DEFAULT NULL,
  `price_allocation` text DEFAULT NULL,
  `price_database` text DEFAULT NULL,
  `price_backup` text DEFAULT NULL,
  `afk_min` text NOT NULL DEFAULT '1',
  `afk_coins_per_min` text NOT NULL DEFAULT '5',
  `discord_webhook` text DEFAULT NULL,
  `version` text NOT NULL DEFAULT '2.0.0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_settings` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_tickets`;
CREATE TABLE `mythicaldash_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownerkey` text NOT NULL,
  `ticketuuid` text NOT NULL,
  `subject` text NOT NULL,
  `priority` enum('low','medium','high') NOT NULL,
  `description` text NOT NULL,
  `attachment` text NOT NULL,
  `status` enum('open','closed','deleted') NOT NULL DEFAULT 'open',
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_tickets` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_tickets_messages`;
CREATE TABLE `mythicaldash_tickets_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticketuuid` text NOT NULL,
  `userkey` text NOT NULL,
  `message` text NOT NULL,
  `attachment` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_tickets_messages` WRITE;
UNLOCK TABLES;


DROP TABLE IF EXISTS `mythicaldash_users`;
CREATE TABLE `mythicaldash_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `panel_id` text NOT NULL,
  `email` text NOT NULL,
  `username` text NOT NULL,
  `first_name` text NOT NULL,
  `last_name` text NOT NULL,
  `password` text NOT NULL,
  `api_key` text NOT NULL,
  `avatar` text NOT NULL DEFAULT 'https://cdn.discordapp.com/embed/avatars/0.png',
  `banner` text NOT NULL DEFAULT 'https://c4.wallpaperflare.com/wallpaper/902/646/497/minecraft-shaders-hd-wallpaper-preview.jpg',
  `role` enum('Administrator','User') NOT NULL DEFAULT 'User',
  `coins` text DEFAULT '0',
  `ram` text NOT NULL DEFAULT '0',
  `disk` text NOT NULL DEFAULT '0',
  `cpu` text NOT NULL DEFAULT '0',
  `server_limit` text NOT NULL DEFAULT '0',
  `ports` text NOT NULL DEFAULT '0',
  `databases` text NOT NULL DEFAULT '0',
  `backups` text NOT NULL DEFAULT '0',
  `minutes_afk` text NOT NULL DEFAULT '0',
  `last_seen` bigint(111) NOT NULL DEFAULT 0,
  `first_ip` text DEFAULT NULL,
  `banned` text NOT NULL DEFAULT '',
  `discord_linked` enum('false','true') NOT NULL DEFAULT 'false',
  `discord_id` text DEFAULT NULL,
  `discord_username` text DEFAULT NULL,
  `discord_global_username` text DEFAULT NULL,
  `discord_email` text DEFAULT NULL,
  `registred` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `mythicaldash_users` WRITE;
UNLOCK TABLES;


