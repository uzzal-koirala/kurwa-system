<?php
require_once '../../../includes/core/config.php';

header('Content-Type: application/json');

$canteen_id = isset($_GET['canteen_id']) ? intval($_GET['canteen_id']) : 0;

if ($canteen_id <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, name, description, price, category, is_veg, image_url 
        FROM food_items 
        WHERE canteen_id = ? 
        ORDER BY category ASC, name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $canteen_id);
$stmt->execute();
$result = $stmt->get_result();

$foods = [];
while ($row = $result->fetch_assoc()) {
    $foods[] = $row;
}

echo json_encode($foods);
?>
