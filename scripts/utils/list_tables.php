<?php
$conn = new mysqli('localhost', 'root', '', 'kurwa_db');
$res = $conn->query('SHOW TABLES');
while($row = $res->fetch_array()) echo $row[0]."\n";
?>
