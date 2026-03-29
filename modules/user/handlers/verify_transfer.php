<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$otp_input = isset($_POST['otp']) ? trim($_POST['otp']) : '';

if (empty($otp_input)) {
    echo json_encode(['success' => false, 'message' => 'OTP required.']);
    exit;
}

if (!isset($_SESSION['pending_transfer'])) {
    echo json_encode(['success' => false, 'message' => 'No active transfer session. Please request a new OTP.']);
    exit;
}

$transfer_data = $_SESSION['pending_transfer'];

if (time() > $transfer_data['expires']) {
    unset($_SESSION['pending_transfer']);
    echo json_encode(['success' => false, 'message' => 'OTP has expired. Please try transferring again.']);
    exit;
}

if ($transfer_data['otp'] !== $otp_input) {
    echo json_encode(['success' => false, 'message' => 'Invalid Verification Code.']);
    exit;
}

$recipient_id = $transfer_data['recipient_id'];
$amount = floatval($transfer_data['amount']);
$recipient_card = $transfer_data['recipient_card'];

$conn->begin_transaction();

try {
    // 1. Lock Sender
    $stmt1 = $conn->prepare("SELECT balance, kurwa_pay_card_number, full_name, kurwa_pay_active FROM users WHERE id = ? FOR UPDATE");
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();
    $sender = $stmt1->get_result()->fetch_assoc();

    if (!$sender || !$sender['kurwa_pay_active']) {
        throw new Exception("Sender has inactive Kurwa Pay Card.");
    }
    if (floatval($sender['balance']) < $amount) {
        throw new Exception("Insufficient balance. Transfer failed.");
    }

    // 2. Lock Recipient
    $stmt2 = $conn->prepare("SELECT balance, full_name, kurwa_pay_active FROM users WHERE id = ? FOR UPDATE");
    $stmt2->bind_param("i", $recipient_id);
    $stmt2->execute();
    $recipient = $stmt2->get_result()->fetch_assoc();
    
    if (!$recipient || !$recipient['kurwa_pay_active']) {
        throw new Exception("Recipient not found or card inactive.");
    }
    
    // 3. Process Balance Swap
    $new_sender_bal = floatval($sender['balance']) - $amount;
    $new_recipient_bal = floatval($recipient['balance']) + $amount;
    
    // Update Sender
    $upd_s = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $upd_s->bind_param("di", $new_sender_bal, $user_id);
    if (!$upd_s->execute()) throw new Exception("Failed to deduct from sender.");
    
    // Update Recipient
    $upd_r = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $upd_r->bind_param("di", $new_recipient_bal, $recipient_id);
    if (!$upd_r->execute()) throw new Exception("Failed to add to recipient.");
    
    // 4. Record transactions
    $tx_id_base = 'TXN_' . strtoupper(uniqid());
    
    $desc_sender = "Transfer Sent to " . $recipient['full_name'] . " (" . $recipient_card . ")";
    $amt_s = -$amount;
    $tx_id_s = $tx_id_base . "_S";
    $tx_stmt_s = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status, transaction_id, description) VALUES (?, ?, 'payment', 'completed', ?, ?)");
    $tx_stmt_s->bind_param("idss", $user_id, $amt_s, $tx_id_s, $desc_sender);
    if (!$tx_stmt_s->execute()) throw new Exception("Failed to log sender transaction: " . $tx_stmt_s->error);
    
    $desc_rcvr = "Transfer Received from " . $sender['full_name'] . " (" . $sender['kurwa_pay_card_number'] . ")";
    $amt_r = $amount;
    $tx_id_r = $tx_id_base . "_R";
    $tx_stmt_r = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status, transaction_id, description) VALUES (?, ?, 'topup', 'completed', ?, ?)");
    $tx_stmt_r->bind_param("idss", $recipient_id, $amt_r, $tx_id_r, $desc_rcvr);
    if (!$tx_stmt_r->execute()) throw new Exception("Failed to log receiver transaction: " . $tx_stmt_r->error);
    
    $conn->commit();
    unset($_SESSION['pending_transfer']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Transfer completed successfully!',
        'new_balance' => $new_sender_bal
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
