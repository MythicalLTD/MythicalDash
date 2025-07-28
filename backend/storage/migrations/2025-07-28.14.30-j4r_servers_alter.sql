-- Alter J4R Servers table to add new fields
ALTER TABLE `mythicaldash_j4r_servers` 
ADD COLUMN `server_id` VARCHAR(255) NULL AFTER `invite_code`,
ADD COLUMN `description` TEXT NULL AFTER `server_id`,
ADD COLUMN `icon_url` VARCHAR(500) NULL AFTER `description`;

-- Add index for server_id for faster lookups
ALTER TABLE `mythicaldash_j4r_servers` 
ADD INDEX `idx_server_id` (`server_id`);

-- Add unique constraint for server_id to prevent duplicates
ALTER TABLE `mythicaldash_j4r_servers` 
ADD UNIQUE INDEX `idx_unique_server_id` (`server_id`); 