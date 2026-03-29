<?php
require_once '../../../includes/core/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['restaurant_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$restaurant_id = $_SESSION['restaurant_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// 1. Verify this order belongs to the restaurant
$verify_sql = "SELECT id, special_notes as overall_notes FROM restaurant_orders WHERE id = $order_id AND restaurant_id = $restaurant_id";
$verify_res = $conn->query($verify_sql);

if (!$verify_res || $verify_res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found or unauthorized']);
    exit;
}
$order_data = $verify_res->fetch_assoc();

// 2. Fetch items 
$items_sql = "
    SELECT 
        quantity, 
        price,
        item_name AS name,
        special_notes
    FROM restaurant_order_items
    WHERE order_id = $order_id
";

$items_res = $conn->query($items_sql);
$items = [];

if ($items_res && $items_res->num_rows > 0) {
    while($row = $items_res->fetch_assoc()) {
        $items[] = [
            'name' => htmlspecialchars($row['name'] ?: 'Menu Item'),
            'quantity' => intval($row['quantity']),
            'price' => floatval($row['price']),
            'special_notes' => htmlspecialchars($row['special_notes'] ?: '')
        ];
    }
}

echo json_encode([
    'success' => true,
    'overall_notes' => $order_data['overall_notes'],
    'items' => $items
]);
?>
