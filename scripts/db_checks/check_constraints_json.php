<?php
require_once 'includes/core/config.php';
header('Content-Type: application/json');

$sql = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_SCHEMA = 'kurwa_db' AND REFERENCED_TABLE_NAME = 'caretakers'";

$res = $conn->query($sql);
$data = [];
if ($res) {
    while($row = $res->fetch_assoc()) $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
