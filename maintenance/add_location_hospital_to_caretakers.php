<?php
require_once dirname(__DIR__) . '/includes/core/config.php';

// Add location_id and hospital_id columns to caretakers table
$sql_add_cols = "ALTER TABLE caretakers 
    ADD COLUMN IF NOT EXISTS location_id INT(11) AFTER price_per_day,
    ADD COLUMN IF NOT EXISTS hospital_id INT(11) AFTER location_id;";

// Add foreign key constraints (optional but recommended)
// Note: We use IF NOT EXISTS logic via a procedural check if possible, or just run the ADD COLUMN
if ($conn->query($sql_add_cols) === TRUE) {
    echo "Columns 'location_id' and 'hospital_id' added successfully to 'caretakers' table.\n";
} else {
    echo "Error adding columns: " . $conn->error . "\n";
}

echo "Database migration complete.";
?>
