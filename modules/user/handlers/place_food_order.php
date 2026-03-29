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
$lat = isset($input['delivery_lat']) && $input['delivery_lat'] !== '' ? floatval($input['delivery_lat']) : null;
$lng = isset($input['delivery_lng']) && $input['delivery_lng'] !== '' ? floatval($input['delivery_lng']) : null;
$special_notes = $conn->real_escape_string($input['special_notes'] ?? '');
$total_amount = floatval($input['total_amount']);
$items = $input['items'] ?? [];

if ($restaurant_id <= 0 || empty($address) || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields or empty cart']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check user balance
    $user_res = $conn->query("SELECT balance FROM users WHERE id = $user_id FOR UPDATE");
    $user_data = $user_res->fetch_assoc();
    $current_balance = floatval($user_data['balance'] ?? 0);

    if ($current_balance < $total_amount) {
        throw new Exception("Insufficient wallet balance. Please top up your wallet.");
    }

    // Deduct balance
    $new_balance = $current_balance - $total_amount;
    $conn->query("UPDATE users SET balance = $new_balance WHERE id = $user_id");

    // 1. Insert into restaurant_orders
    $stmt = $conn->prepare("INSERT INTO restaurant_orders (restaurant_id, user_id, total_amount, special_notes, status, delivery_address, delivery_lat, delivery_lng) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?)");
    $stmt->bind_param("iidssdd", $restaurant_id, $user_id, $total_amount, $special_notes, $address, $lat, $lng);
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order record: " . $stmt->error);
    }
    
    $order_id = $stmt->insert_id;
    
    // Validate that order_id is actually captured correctly
    if (!$order_id || $order_id <= 0) {
        throw new Exception("Critical error: Order ID generation failed.");
    }
    
    // 2. Insert items into restaurant_order_items
    $stmt_item = $conn->prepare("INSERT INTO restaurant_order_items (order_id, menu_item_id, quantity, price, item_name, special_notes) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $mi_id = intval($item['id']);
        $mi_qty = intval($item['quantity'] ?? 1);
        $mi_price = floatval($item['price']);
        $mi_name = isset($item['name']) ? $item['name'] : 'Menu Item';
        $mi_note = isset($item['special_note']) && trim($item['special_note']) !== '' ? $item['special_note'] : null;
        
        if ($mi_id <= 0 || $mi_qty <= 0) {
            throw new Exception("Invalid menu item or quantity.");
        }
        
        $stmt_item->bind_param("iiidss", $order_id, $mi_id, $mi_qty, $mi_price, $mi_name, $mi_note);
        if (!$stmt_item->execute()) {
            throw new Exception("Failed to save order items. " . $stmt_item->error);
        }
    }
    
    // 3. Record transaction in payment history (Negative amount for payment)
    $desc = "Food Order: #ORD-" . str_pad($order_id, 5, '0', STR_PAD_LEFT);
    $tx_amount = -$total_amount;
    $txn_id = 'TXN_' . strtoupper(uniqid());
    
    $tx_stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status, transaction_id, description) VALUES (?, ?, 'payment', 'completed', ?, ?)");
    $tx_stmt->bind_param("idss", $user_id, $tx_amount, $txn_id, $desc);
    
    if (!$tx_stmt->execute()) {
        throw new Exception("Failed to record transaction history.");
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Order placed successfully. Amount deducted from wallet.', 'order_id' => $order_id, 'new_balance' => $new_balance]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
