<?php
/**
 * Chat Handler for Caretaker-User Messaging
 */
header('Content-Type: application/json');
require_once '../../../includes/core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['caretaker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$caretaker_id = $_SESSION['caretaker_id'];
$action = $_GET['action'] ?? '';
$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

if ($action === 'list') {
    // Fetch unique users this caretaker has chatted with or has a booking with
    $sql = "SELECT DISTINCT u.id, u.full_name, u.profile_image, 
            (SELECT message FROM messages WHERE (sender_id = $caretaker_id AND receiver_id = u.id AND receiver_type = 'user') OR (sender_id = u.id AND receiver_id = $caretaker_id AND receiver_type = 'caretaker') ORDER BY created_at DESC LIMIT 1) as last_msg,
            (SELECT created_at FROM messages WHERE (sender_id = $caretaker_id AND receiver_id = u.id AND receiver_type = 'user') OR (sender_id = u.id AND receiver_id = $caretaker_id AND receiver_type = 'caretaker') ORDER BY created_at DESC LIMIT 1) as last_time
            FROM users u
            LEFT JOIN messages m ON (m.sender_id = u.id AND m.receiver_id = $caretaker_id AND m.receiver_type = 'caretaker') OR (m.sender_id = $caretaker_id AND m.receiver_id = u.id AND m.receiver_type = 'user')
            LEFT JOIN caretaker_bookings cb ON cb.user_id = u.id AND cb.caretaker_id = $caretaker_id
            WHERE m.id IS NOT NULL OR cb.id IS NOT NULL
            ORDER BY last_time DESC";
    $res = $conn->query($sql);
    $list = [];
    if($res) {
        while ($row = $res->fetch_assoc()) {
            $list[] = $row;
        }
    }
    echo json_encode($list);
    exit;
}

if ($user_id <= 0 && $action !== 'list') {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    exit;
}

if ($action === 'fetch') {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ? AND receiver_type = 'caretaker') OR (sender_id = ? AND receiver_id = ? AND receiver_type = 'user') ORDER BY created_at ASC");
    $stmt->bind_param("iiii", $user_id, $caretaker_id, $caretaker_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $messages = [];
    while ($row = $res->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode($messages);

} elseif ($action === 'send') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
    
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, receiver_type, message) VALUES (?, ?, 'user', ?)");
    $stmt->bind_param("iis", $caretaker_id, $user_id, $message);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>
