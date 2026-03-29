<?php
require_once 'includes/core/config.php';

// Add missing columns to hospitals table if they don't exist
$add_address = "ALTER TABLE hospitals ADD COLUMN IF NOT EXISTS address VARCHAR(255) AFTER name";
$add_timestamp = "ALTER TABLE hospitals ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";

if ($conn->query($add_address) && $conn->query($add_timestamp)) {
    echo "Database structure updated successfully.\n";
} else {
    echo "Error updating structure: " . $conn->error . "\n";
}
?>
