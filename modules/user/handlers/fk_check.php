<?php
require_once '../../../includes/core/config.php';

$res = $conn->query("
    SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_NAME = 'restaurant_order_items' 
    AND TABLE_SCHEMA = 'kurwa_db'
");

while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
