-- Add J4R joined servers column to users table
ALTER TABLE `mythicaldash_users` 
ADD COLUMN `j4r_joined_servers` TEXT NULL AFTER `discord_servers`;

-- Add index for better performance when checking joined servers
ALTER TABLE `mythicaldash_users` 
ADD INDEX `idx_j4r_joined_servers` (`j4r_joined_servers`(100)); 