<?php
require_once 'includes/core/config.php';

function get_schema($table) {
    global $conn;
    $res = $conn->query("DESCRIBE $table");
    if (!$res) {
        echo "\nError describing $table: " . $conn->error . "\n";
        return;
    }
    echo "\nSchema for $table:\n";
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
}

get_schema('restaurant_menu');
get_schema('restaurant_categories');
get_schema('food_items');
?>
