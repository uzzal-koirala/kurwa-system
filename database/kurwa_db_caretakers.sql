-- SQL Script to Create and Populate the `caretakers` table
-- Run this against the `kurwa_db` database

CREATE TABLE IF NOT EXISTS `caretakers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `rating` decimal(3,1) DEFAULT 0.0,
  `experience_years` int(11) DEFAULT 0,
  `price_per_day` decimal(10,2) NOT NULL,
  `patients_helped` int(11) DEFAULT 0,
  `about_text` text DEFAULT NULL,
  `availability` varchar(50) DEFAULT 'Today',
  `working_hours` varchar(50) DEFAULT '9:00 AM - 6:00 PM',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Clear any existing data for fresh seed
TRUNCATE TABLE `caretakers`;

-- Insert existing sample data representing dynamic caretakers
INSERT INTO `caretakers` (`full_name`, `category`, `specialization`, `rating`, `experience_years`, `price_per_day`, `patients_helped`, `about_text`, `image_url`) VALUES
('Sarita Rai', 'General Care', 'General Care Specialist', 4.9, 5, 800.00, 320, 'Sarita Rai is an experienced caretaker who provides daily patient support, mobility assistance, feeding help, medicine reminders, and compassionate bedside care for hospital and home recovery.', 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=80'),
('Ramesh Karki', 'Post-Surgery', 'Post-Surgery Support', 4.8, 4, 950.00, 215, 'Ramesh specializes in post-surgery recovery. He has extensive training in wound care, mobility assistance, and physical therapy exercises prescribed by doctors.', 'https://images.unsplash.com/photo-1614608682850-e0d6ed316d47?auto=format&fit=crop&w=600&q=80'),
('Pratiksha Limbu', 'Elderly Care', 'Elderly Care Expert', 4.7, 6, 900.00, 450, 'Pratiksha is deeply compassionate and excels in providing long-term elderly care. She assists with daily routines, medication management, and emotional support.', 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=600&q=80'),
('Bibek Tamang', 'Night Support', 'Night Shift Caretaker', 4.6, 3, 850.00, 180, 'Bibek ensures peace of mind during the night. He is alert and ready to assist patients with bathroom needs, medication, and comfort throughout the night hours.', 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=600&q=80'),
('Asmita Shrestha', 'Special Needs', 'Patient Mobility Assistance', 4.9, 7, 1000.00, 500, 'Asmita has advanced training in handling patients with special needs and severe mobility limitations. She is physically capable and highly attentive.', 'https://images.unsplash.com/photo-1651008376811-b90baee60c1f?auto=format&fit=crop&w=600&q=80'),
('Manoj Gurung', 'General Care', 'Hospital Assistance', 4.5, 2, 750.00, 110, 'Manoj is great for hospital environments. He helps patients navigate hospital departments, wait in lines for reports, and provides general comfort.', 'https://images.unsplash.com/photo-1618498082410-b4aa22193b38?auto=format&fit=crop&w=600&q=80'),
('Sunita Magar', 'Elderly Care', 'Senior Companionship', 4.8, 8, 950.00, 620, 'Sunita provides excellent companionship and assistance to seniors. She manages medications, diets, and assists with gentle daily exercises.', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=600&q=80'),
('Kiran Thapa', 'Post-Surgery', 'Wound Care Specialist', 4.9, 5, 1100.00, 310, 'Kiran specializes in post-operative care, specifically wound dressing, infection prevention, and helping patients regain mobility after major surgeries.', 'https://images.unsplash.com/photo-1537368910025-702800faa86b?auto=format&fit=crop&w=600&q=80'),
('Pooja Chaudhary', 'General Care', 'Daily Routine Assistance', 4.7, 3, 700.00, 150, 'Pooja is a cheerful caretaker who assists with bathing, grooming, feeding, and general daily living activities for homebound patients.', 'https://images.unsplash.com/photo-1548142813-c348350df52b?auto=format&fit=crop&w=600&q=80'),
('Bikash Sharma', 'Night Support', 'Overnight Monitoring', 4.6, 4, 900.00, 240, 'Bikash provides reliable overnight care. He monitors vitals, ensures the patient rests comfortably, and handles any night-time emergencies.', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=600&q=80'),
('Sita Poudel', 'Special Needs', 'Cognitive Care Support', 4.9, 9, 1200.00, 480, 'Sita has years of experience caring for patients with dementia, Alzheimer\'s, and other cognitive impairments. She is incredibly patient and understanding.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=600&q=80'),
('Rajendra Joshi', 'Elderly Care', 'Mobility & Physical Support', 4.5, 3, 850.00, 130, 'Rajendra is physically strong and helps elderly patients with transferring from bed to wheelchair, bathroom assistance, and outdoor walks.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80');
