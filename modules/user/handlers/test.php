<?php
require_once '../../../includes/core/config.php';
error_reporting(E_ALL); ini_set('display_errors', 1);

echo "ORDER ITEMS:\n";
$res = $conn->query("SELECT * FROM restaurant_order_items LIMIT 10");
while($row = $res->fetch_assoc()) print_r($row);

echo "FOOD ITEMS:\n";
$res2 = $conn->query("SELECT id, name FROM food_items LIMIT 5");
while($row = $res2->fetch_assoc()) print_r($row);

echo "RESTAURANT MENU:\n";
$res3 = $conn->query("SELECT id, name FROM restaurant_menu LIMIT 5");
while($row = $res3->fetch_assoc()) print_r($row);
?>
