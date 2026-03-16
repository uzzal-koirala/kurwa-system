<?php
require_once '../../includes/core/config.php';

// Add status column
$sql1 = "ALTER TABLE transactions ADD COLUMN status ENUM('pending', 'completed', 'failed', 'canceled') DEFAULT 'completed' AFTER description";
if ($conn->query($sql1)) {
    echo "Column 'status' added successfully.\n";
} else {
    echo "Error adding column 'status': " . $conn->error . "\n";
}

// Add transaction_id column
$sql2 = "ALTER TABLE transactions ADD COLUMN transaction_id VARCHAR(100) DEFAULT NULL AFTER status";
if ($conn->query($sql2)) {
    echo "Column 'transaction_id' added successfully.\n";
} else {
    echo "Error adding column 'transaction_id': " . $conn->error . "\n";
}
?>
