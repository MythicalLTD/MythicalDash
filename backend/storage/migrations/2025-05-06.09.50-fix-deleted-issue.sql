ALTER TABLE `mythicaldash_migrations` ADD `deleted` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `migrated`;
ALTER TABLE `mythicaldash_migrations` ADD `locked` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `deleted`;
ALTER TABLE `mythicaldash_referral_uses` ADD `locked` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `deleted`;
ALTER TABLE `mythicaldash_settings` ADD `locked` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `value`;
ALTER TABLE `mythicaldash_settings` ADD `deleted` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `locked`;
ALTER TABLE `mythicaldash_users_email_verification` ADD `deleted` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `type`;
ALTER TABLE `mythicaldash_users_email_verification` ADD `locked` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `deleted`;