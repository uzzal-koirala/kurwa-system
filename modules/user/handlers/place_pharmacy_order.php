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
$pharmacy_id = intval($input['pharmacy_id']);
$address = $conn->real_escape_string($input['delivery_address'] ?? '');
$rx_url = $conn->real_escape_string($input['prescription_url'] ?? '');
$total_amount = floatval($input['total_amount']);
$items = $input['items'] ?? [];

if ($pharmacy_id <= 0 || empty($address) || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields or empty cart']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Check User Balance
    $user_res = $conn->query("SELECT balance FROM users WHERE id = $user_id FOR UPDATE");
    $user_data = $user_res->fetch_assoc();
    $current_balance = floatval($user_data['balance'] ?? 0);

    if ($current_balance < $total_amount) {
        throw new Exception("Insufficient wallet balance. Please top up your wallet. Current Balance: Rs. $current_balance");
    }

    // 2. Deduct wallet balance
    $new_balance = $current_balance - $total_amount;
    $conn->query("UPDATE users SET balance = $new_balance WHERE id = $user_id");

    // 3. Insert Pharmacy Order
    $stmt = $conn->prepare("INSERT INTO pharmacy_orders (pharmacy_id, user_id, total_amount, status, delivery_address, prescription_url) VALUES (?, ?, ?, 'pending', ?, ?)");
    $stmt->bind_param("iids", $pharmacy_id, $user_id, $total_amount, $address, $rx_url);
    $stmt->execute();
    
    $order_id = $conn->insert_id;
    
    // 4. Insert Items
    $stmt_item = $conn->prepare("INSERT INTO pharmacy_order_items (order_id, medicine_id, quantity, price) VALUES (?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $mi_id = intval($item['id']);
        $mi_qty = intval($item['quantity'] ?? 1);
        $mi_price = floatval($item['price']);
        
        if ($mi_id <= 0 || $mi_qty <= 0) {
            throw new Exception("Invalid medicine item or quantity.");
        }
        
        $stmt_item->bind_param("iiid", $order_id, $mi_id, $mi_qty, $mi_price);
        $stmt_item->execute();
    }
    
    // 5. Record Transaction Log
    $desc = "Pharmacy Order: #ORD-" . str_pad($order_id, 5, '0', STR_PAD_LEFT);
    $tx_amount = -$total_amount;
    $txn_id = 'TXN_PHARM_' . strtoupper(uniqid());
    
    $tx_stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status, transaction_id, description) VALUES (?, ?, 'payment', 'completed', ?, ?)");
    $tx_stmt->bind_param("idss", $user_id, $tx_amount, $txn_id, $desc);
    
    if (!$tx_stmt->execute()) {
        throw new Exception("Failed to record payment transaction log.");
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Pharmacy order placed successfully.', 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
