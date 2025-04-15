ALTER TABLE `mythicaldash_users` ADD `github_id` INT NULL DEFAULT NULL AFTER `discord_id`,
ADD `github_username` TEXT NULL DEFAULT NULL AFTER `github_id`,
ADD `github_email` TEXT NULL DEFAULT NULL AFTER `github_username`,
ADD `github_linked` ENUM('true', 'false') NULL DEFAULT 'false' AFTER `github_email`;