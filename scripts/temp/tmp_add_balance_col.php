<?php
$conn = mysqli_connect('localhost', 'root', '', 'kurwa_db');
if (!$conn) die('failed');
$sql = "ALTER TABLE users ADD COLUMN balance DECIMAL(10, 2) DEFAULT 0.00 AFTER verified";
if (mysqli_query($conn, $sql)) {
    echo "success";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
