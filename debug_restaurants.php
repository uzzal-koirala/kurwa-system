<?php
require_once 'includes/core/config.php';
$res = $conn->query("SELECT id, name, opening_time, closing_time, status FROM restaurants");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Open: {$row['opening_time']} | Close: {$row['closing_time']} | Status: {$row['status']}\n";
}
echo "\nCurrent Server time: " . date('H:i:s') . "\n";
?>
