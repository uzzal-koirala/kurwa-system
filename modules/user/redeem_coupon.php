<?php
/**
 * AJAX Endpoint for Coupon Redemption
 */
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$code = isset($_POST['code']) ? trim(strtoupper($_POST['code'])) : '';

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code.']);
    exit;
}

// 1. Check if coupon exists and is active
$stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' LIMIT 1");
$stmt->bind_param("s", $code);
$stmt->execute();
$coupon_res = $stmt->get_result();

if ($coupon_res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code.']);
    exit;
}

$coupon = $coupon_res->fetch_assoc();
$coupon_id = $coupon['id'];
$amount = $coupon['amount'];

// 2. Check global usage limit
if ($coupon['times_used'] >= $coupon['usage_limit']) {
    echo json_encode(['success' => false, 'message' => 'This coupon has reached its usage limit.']);
    exit;
}

// 3. Check if user already used this coupon
$stmt = $conn->prepare("SELECT id FROM coupon_usage WHERE user_id = ? AND coupon_id = ?");
$stmt->bind_param("ii", $user_id, $coupon_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already redeemed this coupon.']);
    exit;
}

// 4. Start transaction for safety
$conn->begin_transaction();

try {
    // A. Update user balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->bind_param("di", $amount, $user_id);
    $stmt->execute();

    // B. Increment coupon usage
    $stmt = $conn->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE id = ?");
    $stmt->bind_param("i", $coupon_id);
    $stmt->execute();

    // C. Log usage
    $stmt = $conn->prepare("INSERT INTO coupon_usage (user_id, coupon_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $coupon_id);
    $stmt->execute();

    // D. Log transaction
    $desc = "Coupon Redeemed: $code";
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'coupon', ?)");
    $stmt->bind_param("ids", $user_id, $amount, $desc);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => "Success! Rs. " . number_format($amount, 2) . " has been added to your wallet.",
        'new_balance' => null // Frontend will refresh, or we can fetch it
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
