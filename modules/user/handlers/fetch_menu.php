<?php
require_once '../../../includes/core/config.php';

header('Content-Type: application/json');

$restaurant_id = isset($_GET['canteen_id']) ? intval($_GET['canteen_id']) : 0;

if ($restaurant_id <= 0) {
    echo json_encode([]);
    exit;
}

// Fetch from restaurant_menu (Genuine items)
$sql = "SELECT 
            m.id, 
            m.name, 
            m.description, 
            m.price, 
            c.name as category, 
            m.image_url 
        FROM restaurant_menu m 
        JOIN restaurant_categories c ON m.category_id = c.id 
        WHERE m.restaurant_id = ? AND m.is_available = 1 
        ORDER BY c.name ASC, m.name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$result = $stmt->get_result();

$foods = [];
while ($row = $result->fetch_assoc()) {
    $foods[] = $row;
}

echo json_encode($foods);
?>
