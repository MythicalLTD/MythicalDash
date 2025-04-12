ALTER TABLE `mythicaldash_users` ADD `discord_id` INT NULL DEFAULT NULL AFTER `2fa_blocked`,
ADD `discord_username` TEXT NULL DEFAULT NULL AFTER `discord_id`,
ADD `discord_global_name` TEXT NULL DEFAULT NULL AFTER `discord_username`,
ADD `discord_email` TEXT NULL DEFAULT NULL AFTER `discord_global_name`,
ADD `discord_linked` ENUM('true', 'false') NULL DEFAULT 'false' AFTER `discord_email`;