<?php
$conn = mysqli_connect('localhost', 'root', '', 'kurwa_db');
if (!$conn) die('failed');
$res = mysqli_query($conn, 'SHOW COLUMNS FROM users');
while ($row = mysqli_fetch_assoc($res)) {
    echo "{$row['Field']} ({$row['Type']})\n";
}
?>
