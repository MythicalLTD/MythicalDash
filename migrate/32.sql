ALTER TABLE `mythicaldash_settings` ADD `enable_alting` ENUM('true','false') NOT NULL DEFAULT 'true' AFTER `stripe_currency`, ADD `enable_anti_vpn` ENUM('true','false') NOT NULL DEFAULT 'true' AFTER `enable_alting`;