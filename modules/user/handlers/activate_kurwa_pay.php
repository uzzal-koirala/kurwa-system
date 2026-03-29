<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$activation_fee = 500.00;

$conn->begin_transaction();

try {
    // Check user data with FOR UPDATE to prevent race conditions
    $stmt = $conn->prepare("SELECT balance, kurwa_pay_active FROM users WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();

    if (!$user_data) {
        throw new Exception("User not found.");
    }

    if ($user_data['kurwa_pay_active']) {
        throw new Exception("Kurwa Pay Card is already active.");
    }

    if (floatval($user_data['balance']) < $activation_fee) {
        throw new Exception("Insufficient balance. You need Rs. 500 to activate.");
    }

    // Generate unique 10-digit card number
    $card_number = '';
    do {
        $card_number = str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE kurwa_pay_card_number = ?");
        $check_stmt->bind_param("s", $card_number);
        $check_stmt->execute();
        $exists = $check_stmt->get_result()->num_rows > 0;
    } while ($exists);

    // Deduct balance and update user
    $new_balance = floatval($user_data['balance']) - $activation_fee;
    
    $upd_stmt = $conn->prepare("UPDATE users SET balance = ?, kurwa_pay_active = 1, kurwa_pay_card_number = ? WHERE id = ?");
    $upd_stmt->bind_param("dsi", $new_balance, $card_number, $user_id);
    if (!$upd_stmt->execute()) {
        throw new Exception("Failed to update user record.");
    }

    // Record transaction
    $tx_id = 'TXN_' . strtoupper(uniqid());
    $desc = "Kurwa Pay Setup Fee";
    $tx_amt = -$activation_fee;

    $tx_stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status, transaction_id, description) VALUES (?, ?, 'payment', 'completed', ?, ?)");
    $tx_stmt->bind_param("idss", $user_id, $tx_amt, $tx_id, $desc);
    if (!$tx_stmt->execute()) {
        throw new Exception("Failed to log transaction.");
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Kurwa Pay Card activated successfully!',
        'new_balance' => $new_balance,
        'card_number' => $card_number
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
