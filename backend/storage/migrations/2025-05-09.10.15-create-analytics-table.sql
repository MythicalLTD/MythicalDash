CREATE TABLE IF NOT EXISTS `mythicaldash_analytics` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(64) NOT NULL,
  `date` DATETIME NOT NULL,
  `value` INT(11) NOT NULL DEFAULT 0,
  `metadata` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `type_date` (`type`, `date`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;