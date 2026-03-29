<?php
require 'includes/core/config.php';
$conn->query("ALTER TABLE users ADD COLUMN kurwa_pay_active TINYINT(1) DEFAULT 0");
$conn->query("ALTER TABLE users ADD COLUMN kurwa_pay_card_number VARCHAR(10) UNIQUE DEFAULT NULL");
echo "DB Updated: " . ($conn->error ? $conn->error : "OK");
?>
