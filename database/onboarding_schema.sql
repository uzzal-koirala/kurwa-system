-- Create locations table
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create hospitals table
CREATE TABLE IF NOT EXISTS `hospitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add hospital_id to existing tables
ALTER TABLE `users` ADD COLUMN `hospital_id` int(11) DEFAULT NULL;
ALTER TABLE `users` ADD FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`);

ALTER TABLE `canteens` ADD COLUMN `hospital_id` int(11) DEFAULT NULL;
ALTER TABLE `canteens` ADD FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`);

ALTER TABLE `caretakers` ADD COLUMN `hospital_id` int(11) DEFAULT NULL;
ALTER TABLE `caretakers` ADD FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`);

-- Seed Locations
INSERT INTO `locations` (`name`) VALUES 
('Kathmandu'), 
('Lalitpur'), 
('Bhaktapur'), 
('Pokhara'), 
('Biratnagar');

-- Seed Hospitals
INSERT INTO `hospitals` (`name`, `location_id`) VALUES 
('Tribhuvan University Teaching Hospital', 1),
('Bir Hospital', 1),
('Norvic International Hospital', 1),
('Patan Hospital', 2),
('Mediciti Hospital', 2),
('Bhaktapur Cancer Hospital', 3),
('Manipal Teaching Hospital', 4),
('Nobel Medical College', 5);

-- Link existing canteens/caretakers to some hospitals (Sample)
UPDATE `canteens` SET `hospital_id` = 1 WHERE `id` <= 2;
UPDATE `canteens` SET `hospital_id` = 2 WHERE `id` > 2;

UPDATE `caretakers` SET `hospital_id` = 1 WHERE `id` % 2 = 0;
UPDATE `caretakers` SET `hospital_id` = 2 WHERE `id` % 2 != 0;
