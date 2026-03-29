<?php
require_once '../../../includes/core/config.php';
error_reporting(E_ALL); ini_set('display_errors', 1);

echo "ORDER ITEMS Table:\n";
$res = $conn->query("SELECT * FROM restaurant_order_items LIMIT 5");
if(!$res) echo $conn->error;
while($row = $res->fetch_assoc()) print_r($row);

echo "\nFOOD ITEMS Table:\n";
$res2 = $conn->query("SELECT id, name FROM food_items LIMIT 5");
if(!$res2) echo $conn->error;
while($row = $res2->fetch_assoc()) print_r($row);

echo "\nRESTAURANT MENU Table:\n";
$res3 = $conn->query("SELECT id, name FROM restaurant_menu LIMIT 5");
if(!$res3) echo $conn->error;
while($row = $res3->fetch_assoc()) print_r($row);

echo "\nJOIN QUERY:\n";
$sql = "
    SELECT GROUP_CONCAT(CONCAT(roi.quantity, 'x ', COALESCE(rm.name, fi.name, 'Menu Item')) SEPARATOR ', ') as preview
    FROM restaurant_order_items roi
    LEFT JOIN restaurant_menu rm ON roi.menu_item_id = rm.id
    LEFT JOIN food_items fi ON roi.menu_item_id = fi.id
    WHERE roi.order_id = 4
";
$res4 = $conn->query($sql);
if(!$res4) echo $conn->error;
while($row = $res4->fetch_assoc()) print_r($row);
?>
