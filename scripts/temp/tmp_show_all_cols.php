<?php
$conn = mysqli_connect('localhost', 'root', '', 'kurwa_db');
if (!$conn) die('failed');
$tables = ['medicine_orders', 'pharmacies', 'caretakers', 'food_orders', 'caretaker_bookings'];
foreach ($tables as $t) {
    echo "TABLE: $t\n";
    $res = mysqli_query($conn, "SHOW COLUMNS FROM $t");
    while ($row = mysqli_fetch_assoc($res)) {
        echo "  {$row['Field']} ({$row['Type']})\n";
    }
    echo "\n";
}
?>
