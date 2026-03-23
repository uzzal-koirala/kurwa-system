<?php
$conn = new mysqli('localhost', 'root', '', 'kurwa_db');
$sql = file_get_contents(__DIR__ . '/../database/kurwa_db_pharmacies.sql');
$conn->multi_query($sql);
while ($conn->next_result()) {;}
echo "Success";
?>
