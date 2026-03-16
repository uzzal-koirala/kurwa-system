<?php
$conn = mysqli_connect('localhost', 'root', '', 'kurwa_db');
if (!$conn) die('failed');
echo "CARETAKER_BOOKINGS:\n";
$res = mysqli_query($conn, 'SHOW COLUMNS FROM caretaker_bookings');
while ($row = mysqli_fetch_assoc($res)) echo $row['Field'] . " (" . $row['Type'] . ")\n";
echo "\nFOOD_ORDERS:\n";
$res = mysqli_query($conn, 'SHOW COLUMNS FROM food_orders');
while ($row = mysqli_fetch_assoc($res)) echo $row['Field'] . " (" . $row['Type'] . ")\n";
echo "\nMEDICINE_ORDERS:\n";
$res = mysqli_query($conn, 'SHOW COLUMNS FROM medicine_orders');
while ($row = mysqli_fetch_assoc($res)) echo $row['Field'] . " (" . $row['Type'] . ")\n";
?>
