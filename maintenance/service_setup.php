<?php
require_once dirname(__DIR__) . '/includes/core/config.php';

// Create service_locations table
$sql_locations = "CREATE TABLE IF NOT EXISTS service_locations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Create hospitals table
$sql_hospitals = "CREATE TABLE IF NOT EXISTS hospitals (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    location_id INT(11) NOT NULL,
    hospital_name VARCHAR(150) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES service_locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_locations) === TRUE) {
    echo "Table 'service_locations' created successfully.\n";
} else {
    echo "Error creating table 'service_locations': " . $conn->error . "\n";
}

if ($conn->query($sql_hospitals) === TRUE) {
    echo "Table 'hospitals' created successfully.\n";
} else {
    echo "Error creating table 'hospitals': " . $conn->error . "\n";
}

// Optional: Add some initial locations for testing
$conn->query("INSERT IGNORE INTO service_locations (location_name) VALUES ('Kathmandu'), ('Lalitpur'), ('Bhaktapur'), ('Pokhara')");

echo "Database initialization complete.";
?>
