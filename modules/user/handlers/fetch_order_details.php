<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

header('Content-Type: application/json');

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// 1. Verify this order belongs to the user
$verify_sql = "SELECT id FROM restaurant_orders WHERE id = $order_id AND user_id = $user_id";
$verify_res = $conn->query($verify_sql);

if (!$verify_res || $verify_res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found or unauthorized']);
    exit;
}

// 2. Fetch items 
$items_sql = "
    SELECT 
        quantity, 
        price,
        COALESCE(item_name, 'Menu Item') AS name
    FROM restaurant_order_items
    WHERE order_id = $order_id
";

$items_res = $conn->query($items_sql);
$items = [];

if ($items_res && $items_res->num_rows > 0) {
    while($row = $items_res->fetch_assoc()) {
        $items[] = [
            'name' => htmlspecialchars($row['name']),
            'quantity' => intval($row['quantity']),
            'price' => floatval($row['price'])
        ];
    }
}

echo json_encode([
    'success' => true,
    'items' => $items
]);
?>
