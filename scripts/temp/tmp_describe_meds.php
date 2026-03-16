<?php
$conn = mysqli_connect('localhost', 'root', '', 'kurwa_db');
if (!$conn) die('failed');
$res = mysqli_query($conn, 'DESCRIBE medicine_orders');
while ($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
