ALTER TABLE `mythicaldash_mail_templates`
	ADD COLUMN `subject` VARCHAR(255) NOT NULL AFTER `name`,
	ADD COLUMN `body` TEXT NOT NULL AFTER `subject`,
	ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `locked`,
	ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

UPDATE `mythicaldash_mail_templates`
	SET `body` = `content`;

ALTER TABLE `mythicaldash_mail_templates`
	DROP COLUMN `content`;