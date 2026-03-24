-- Create system_settings table
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default onboarding questions
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES 
('onboarding_step1_question', 'Where are you located?'),
('onboarding_step2_question', 'Which hospital are you at?')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
