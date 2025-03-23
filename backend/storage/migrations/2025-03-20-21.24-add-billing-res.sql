ALTER TABLE `mythicaldash_users` ADD COLUMN `memory_limit` INT(11) NOT NULL DEFAULT 0 AFTER `pterodactyl_user_id`;
ALTER TABLE `mythicaldash_users` ADD COLUMN `disk_limit` INT(11) NOT NULL DEFAULT 0 AFTER `memory_limit`;
ALTER TABLE `mythicaldash_users` ADD COLUMN `cpu_limit` INT(11) NOT NULL DEFAULT 0 AFTER `disk_limit`;
ALTER TABLE `mythicaldash_users` ADD COLUMN `server_limit` INT(11) NOT NULL DEFAULT 0 AFTER `cpu_limit`;
ALTER TABLE `mythicaldash_users` ADD COLUMN `backup_limit` INT(11) NOT NULL DEFAULT 0 AFTER `server_limit`;
ALTER TABLE `mythicaldash_users` ADD COLUMN `database_limit` INT(11) NOT NULL DEFAULT 0 AFTER `backup_limit`;
ALTER TABLE `mythicaldash_users` ADD COLUMN `allocation_limit` INT(11) NOT NULL DEFAULT 0 AFTER `database_limit`;