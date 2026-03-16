<?php
$conn = mysqli_connect('localhost', 'root', '', 'kurwa_db');
if (!$conn) die('failed');
echo "FOOD_ORDERS:\n";
$res = mysqli_query($conn, 'DESCRIBE food_orders');
while ($row = mysqli_fetch_assoc($res)) print_r($row);
echo "\nCARETAKER_BOOKINGS:\n";
$res = mysqli_query($conn, 'DESCRIBE caretaker_bookings');
while ($row = mysqli_fetch_assoc($res)) print_r($row);
?>
