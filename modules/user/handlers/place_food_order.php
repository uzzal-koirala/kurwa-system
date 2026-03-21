<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

$user_id = $_SESSION['user_id'];
$restaurant_id = intval($input['restaurant_id']);
$address = $conn->real_escape_string($input['delivery_address'] ?? '');
$total_amount = floatval($input['total_amount']);
$items = $input['items'] ?? [];

if ($restaurant_id <= 0 || empty($address) || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields or empty cart']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Insert into restaurant_orders
    $stmt = $conn->prepare("INSERT INTO restaurant_orders (restaurant_id, user_id, total_amount, status, delivery_address) VALUES (?, ?, ?, 'pending', ?)");
    $stmt->bind_param("iids", $restaurant_id, $user_id, $total_amount, $address);
    $stmt->execute();
    
    $order_id = $conn->insert_id;
    
    // 2. Insert items into restaurant_order_items
    $stmt_item = $conn->prepare("INSERT INTO restaurant_order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, 1, ?)");
    
    foreach ($items as $item) {
        $mi_id = intval($item['id']);
        $mi_price = floatval($item['price']);
        
        // Basic validation
        if ($mi_id <= 0) {
            throw new Exception("Invalid menu item ID.");
        }
        
        $stmt_item->bind_param("iid", $order_id, $mi_id, $mi_price);
        $stmt_item->execute();
    }
    
    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
?>
