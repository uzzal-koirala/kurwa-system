<?php
require_once dirname(__DIR__) . '/includes/core/config.php';

// Add opening_time and closing_time columns to restaurants table
$sql_rest = "ALTER TABLE restaurants 
    ADD COLUMN IF NOT EXISTS opening_time TIME DEFAULT '08:00:00',
    ADD COLUMN IF NOT EXISTS closing_time TIME DEFAULT '22:00:00';";

// Add opening_time and closing_time columns to pharmacies table
$sql_phar = "ALTER TABLE pharmacies 
    ADD COLUMN IF NOT EXISTS opening_time TIME DEFAULT '08:00:00',
    ADD COLUMN IF NOT EXISTS closing_time TIME DEFAULT '20:00:00';";

if ($conn->query($sql_rest) === TRUE) {
    echo "Columns 'opening_time' and 'closing_time' added to 'restaurants' table.\n";
} else {
    echo "Error updating restaurants table: " . $conn->error . "\n";
}

if ($conn->query($sql_phar) === TRUE) {
    echo "Columns 'opening_time' and 'closing_time' added to 'pharmacies' table.\n";
} else {
    echo "Error updating pharmacies table: " . $conn->error . "\n";
}

echo "Database migration complete.";
?>
