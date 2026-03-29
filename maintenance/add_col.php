<?php
require 'includes/core/config.php';
$conn->query("ALTER TABLE restaurant_order_items ADD COLUMN special_notes text DEFAULT NULL");
echo $conn->error ? "Error: " . $conn->error : "Column added successfully.";
?>
