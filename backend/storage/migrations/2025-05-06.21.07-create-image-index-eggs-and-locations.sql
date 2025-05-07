ALTER TABLE `mythicaldash_locations` ADD `image_id` INT NULL DEFAULT NULL AFTER `node_ip`;
ALTER TABLE `mythicaldash_locations` ADD FOREIGN KEY (`image_id`) REFERENCES `mythicaldash_image_db`(`id`);
ALTER TABLE `mythicaldash_eggs` ADD `image_id` INT NULL DEFAULT NULL AFTER `category`;
ALTER TABLE `mythicaldash_eggs` ADD FOREIGN KEY (`image_id`) REFERENCES `mythicaldash_image_db`(`id`);