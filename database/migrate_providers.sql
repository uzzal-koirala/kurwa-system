-- Migration to link service providers to hospitals and locations
USE kurwa_db;

-- 1. Update caretakers (already has hospital_id in live DB, but add location_id)
SET @exist := (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'caretakers' AND column_name = 'location_id' AND table_schema = 'kurwa_db');
SET @query := IF(@exist = 0, 'ALTER TABLE caretakers ADD COLUMN location_id INT AFTER image_url', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Update pharmacies
SET @exist := (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'pharmacies' AND column_name = 'location_id' AND table_schema = 'kurwa_db');
SET @query := IF(@exist = 0, 'ALTER TABLE pharmacies ADD COLUMN location_id INT AFTER address', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'pharmacies' AND column_name = 'hospital_id' AND table_schema = 'kurwa_db');
SET @query := IF(@exist = 0, 'ALTER TABLE pharmacies ADD COLUMN hospital_id INT AFTER location_id', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Update restaurants
SET @exist := (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'restaurants' AND column_name = 'location_id' AND table_schema = 'kurwa_db');
SET @query := IF(@exist = 0, 'ALTER TABLE restaurants ADD COLUMN location_id INT AFTER address', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'restaurants' AND column_name = 'hospital_id' AND table_schema = 'kurwa_db');
SET @query := IF(@exist = 0, 'ALTER TABLE restaurants ADD COLUMN hospital_id INT AFTER location_id', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. Update canteens (if exists)
SET @exist_table := (SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'canteens' AND table_schema = 'kurwa_db');
SET @query := IF(@exist_table > 0, 
    'ALTER TABLE canteens ADD COLUMN IF NOT EXISTS location_id INT AFTER name', 
    'SELECT 1'
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5. Foreign Keys (Optional but recommended)
-- We skip if they already exist, but for simplicity we rely on columns for now.
