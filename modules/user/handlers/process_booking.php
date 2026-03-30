<?php
/**
 * Booking Processor for Caretakers
 */
header('Content-Type: application/json');
require_once '../../../includes/core/config.php';
require_once '../../../includes/core/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$caretaker_id = isset($_POST['caretaker_id']) ? intval($_POST['caretaker_id']) : 0;
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

if ($caretaker_id <= 0 || !$start_date || !$end_date) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
    exit;
}

// 1. Fetch caretaker details
$stmt = $conn->prepare("SELECT full_name, price_per_day, phone_number FROM caretakers WHERE id = ?");
$stmt->bind_param("i", $caretaker_id);
$stmt->execute();
$ct = $stmt->get_result()->fetch_assoc();

if (!$ct) {
    echo json_encode(['success' => false, 'message' => 'Caretaker not found.']);
    exit;
}

// 2. Calculate total price
$start = new DateTime($start_date);
$end = new DateTime($end_date);

// overlap check
$check_sql = "SELECT id FROM caretaker_bookings 
             WHERE caretaker_id = ? 
             AND status IN ('pending', 'confirmed')
             AND (
                 (start_date <= ? AND end_date >= ?) OR 
                 (start_date <= ? AND end_date >= ?) OR 
                 (start_date >= ? AND end_date <= ?)
             )";
$c_stmt = $conn->prepare($check_sql);
$c_stmt->bind_param("issssss", $caretaker_id, $start_date, $start_date, $end_date, $end_date, $start_date, $end_date);
$c_stmt->execute();
if ($c_stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'The selected dates are already booked or pending. Please choose other dates.']);
    exit;
}
$c_stmt->close();

$interval = $start->diff($end);
$days = $interval->days + 1;
$total_price = $days * $ct['price_per_day'];

// 3. Check user balance
$stmt = $conn->prepare("SELECT full_name, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_res = $stmt->get_result()->fetch_assoc();
$user_balance = $user_res['balance'];
$user_name = $user_res['full_name'];

if ($user_balance < $total_price) {
    echo json_encode(['success' => false, 'message' => 'Insufficient balance. Please top up your wallet. Total needed: Rs. ' . number_format($total_price)]);
    exit;
}

// 4. Execute transaction
$conn->begin_transaction();
try {
    // A. Deduct balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmt->bind_param("di", $total_price, $user_id);
    $stmt->execute();

    // B. Create booking
    $stmt = $conn->prepare("INSERT INTO caretaker_bookings (user_id, caretaker_id, start_date, end_date, total_price, status) VALUES (?, ?, ?, ?, ?, 'confirmed')");
    $stmt->bind_param("iissd", $user_id, $caretaker_id, $start_date, $end_date, $total_price);
    $stmt->execute();

    // C. Log transaction
    $txn_id = uniqid('BK-', true);
    $desc = "Caretaker Booking: " . $ct['full_name'] . " ($days Days)";
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status, transaction_id, description) VALUES (?, ?, 'payment', 'completed', ?, ?)");
    $neg_amount = -$total_price;
    $stmt->bind_param("idss", $user_id, $neg_amount, $txn_id, $desc);
    $stmt->execute();

    $conn->commit();
    
    // D. Send SMS notification to caretaker
    if (!empty($ct['phone_number'])) {
        require_once '../../../includes/core/sms_helper.php';
        $sms_msg = "तपाईंलाई नयाँ केयरटेकर बुकिङको रिक्वेस्ट आएको छ। कृपया ड्यासबोर्ड चेक गरी उक्त बुकिङ स्वीकार वा पूरा गरिदिनुहोला।";
        send_sms($ct['phone_number'], $sms_msg);
    }

    echo json_encode(['success' => true, 'message' => 'Booking successful! Your session is reserved.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()]);
}
?>
