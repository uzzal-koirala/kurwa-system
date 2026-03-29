<?php
require_once '../../../includes/core/config.php';

// First, fetch the foreign key name
$res = $conn->query("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_NAME = 'restaurant_order_items' 
    AND COLUMN_NAME = 'menu_item_id' 
    AND TABLE_SCHEMA = 'kurwa_db'
");
if ($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        $fk = $row['CONSTRAINT_NAME'];
        if ($fk !== 'PRIMARY') {
            $conn->query("ALTER TABLE restaurant_order_items DROP FOREIGN KEY `$fk`");
            echo "Dropped FK: $fk\n";
        }
    }
}

// Check if item_name exists, if not add it
$check = $conn->query("SHOW COLUMNS FROM restaurant_order_items LIKE 'item_name'");
if ($check->num_rows === 0) {
    $conn->query("ALTER TABLE restaurant_order_items ADD COLUMN item_name VARCHAR(255) DEFAULT NULL");
    echo "Added item_name column\n";
} else {
    echo "item_name already exists\n";
}
?>
