<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$recipient_card = isset($_POST['recipient_card']) ? trim($_POST['recipient_card']) : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

if (empty($recipient_card) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Valid card number and amount required.']);
    exit;
}

try {
    // Check sender
    $stmt = $conn->prepare("SELECT phone, balance, kurwa_pay_active, kurwa_pay_card_number FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $sender = $stmt->get_result()->fetch_assoc();

    if (!$sender || !$sender['kurwa_pay_active']) {
        echo json_encode(['success' => false, 'message' => 'Activate your Kurwa Pay Card first.']);
        exit;
    }
    
    if ($sender['kurwa_pay_card_number'] === $recipient_card) {
        echo json_encode(['success' => false, 'message' => 'You cannot transfer money to yourself.']);
        exit;
    }

    if (floatval($sender['balance']) < $amount) {
        echo json_encode(['success' => false, 'message' => 'Insufficient wallet balance for this transfer.']);
        exit;
    }

    if (empty($sender['phone'])) {
        echo json_encode(['success' => false, 'message' => 'Please add a phone number to your profile to send transfers.']);
        exit;
    }

    // Check recipient
    $rcpt_stmt = $conn->prepare("SELECT id, full_name, kurwa_pay_active FROM users WHERE kurwa_pay_card_number = ?");
    $rcpt_stmt->bind_param("s", $recipient_card);
    $rcpt_stmt->execute();
    $recipient = $rcpt_stmt->get_result()->fetch_assoc();

    if (!$recipient || !$recipient['kurwa_pay_active']) {
        echo json_encode(['success' => false, 'message' => 'Recipient not found or does not have an active Kurwa Pay Card.']);
        exit;
    }

    // Generate OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    
    // Store transfer details in session
    $_SESSION['pending_transfer'] = [
        'recipient_card' => $recipient_card,
        'recipient_id' => $recipient['id'],
        'amount' => $amount,
        'otp' => $otp,
        'expires' => time() + 300 // 5 minutes
    ];

    // Send SMS 
    $message = "Your Kurwa Pay Transfer OTP is $otp. Do not share this with anyone. Valid for 5 minutes.";
    $sms_res = send_sms($sender['phone'], $message);

    if ($sms_res['success']) {
        echo json_encode(['success' => true, 'message' => 'OTP sent to your phone number.', 'recipient_name' => $recipient['full_name']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP via SMS. Try again later.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
