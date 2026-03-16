<?php
/**
 * Chat Handler for User-Caretaker Messaging
 */
header('Content-Type: application/json');
require_once '../../../includes/core/config.php';
require_once '../../../includes/core/auth_check.php';

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$caretaker_id = isset($_REQUEST['caretaker_id']) ? intval($_REQUEST['caretaker_id']) : 0;

if ($action === 'list') {
    // Fetch unique caretakers this user has chatted with
    $sql = "SELECT DISTINCT c.id, c.full_name, c.image_url, 
            (SELECT message FROM messages WHERE (sender_id = $user_id AND receiver_id = c.id) OR (sender_id = c.id AND receiver_id = $user_id) ORDER BY created_at DESC LIMIT 1) as last_msg,
            (SELECT created_at FROM messages WHERE (sender_id = $user_id AND receiver_id = c.id) OR (sender_id = c.id AND receiver_id = $user_id) ORDER BY created_at DESC LIMIT 1) as last_time
            FROM caretakers c
            JOIN messages m ON (m.sender_id = c.id AND m.receiver_id = $user_id) OR (m.sender_id = $user_id AND m.receiver_id = c.id)
            ORDER BY last_time DESC";
    $res = $conn->query($sql);
    $list = [];
    while ($row = $res->fetch_assoc()) {
        $list[] = $row;
    }
    echo json_encode($list);
    exit;
}

if ($caretaker_id <= 0 && $action !== 'list') {
    echo json_encode(['success' => false, 'message' => 'Invalid caretaker ID.']);
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

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, receiver_type, message) VALUES (?, ?, 'caretaker', ?)");
    $stmt->bind_param("iis", $user_id, $caretaker_id, $message);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>
