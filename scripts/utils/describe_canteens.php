<?php
$conn = new mysqli('localhost', 'root', '', 'kurwa_db');
$res = $conn->query('DESCRIBE canteens');
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
