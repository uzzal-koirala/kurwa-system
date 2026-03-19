CREATE TABLE IF NOT EXISTS `restaurant_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add category_id if not exists, safely (MySQL MariaDB)
-- To avoid errors if it exists. But it's a fresh DB instance basically.
ALTER TABLE `restaurant_menu` ADD COLUMN `category_id` INT(11) NULL DEFAULT NULL AFTER `price`;
ALTER TABLE `restaurant_menu` ADD CONSTRAINT `fk_menu_category` FOREIGN KEY (`category_id`) REFERENCES `restaurant_categories`(`id`) ON DELETE SET NULL;
