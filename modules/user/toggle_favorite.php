<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Mock user ID (In real app, use session)
$user_id = 1; 
$caretaker_id = isset($_POST['caretaker_id']) ? intval($_POST['caretaker_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : 'toggle';

if ($caretaker_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

// Check if already favorited
$check_sql = "SELECT id FROM caretaker_favorites WHERE user_id = ? AND caretaker_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ii", $user_id, $caretaker_id);
$stmt->execute();
$is_fav = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($is_fav) {
    // Unfavorite
    $del_sql = "DELETE FROM caretaker_favorites WHERE user_id = ? AND caretaker_id = ?";
    $stmt = $conn->prepare($del_sql);
    $stmt->bind_param("ii", $user_id, $caretaker_id);
    $stmt->execute();
    $new_status = false;
} else {
    // Favorite
    $ins_sql = "INSERT INTO caretaker_favorites (user_id, caretaker_id) VALUES (?, ?)";
    $stmt = $conn->prepare($ins_sql);
    $stmt->bind_param("ii", $user_id, $caretaker_id);
    $stmt->execute();
    $new_status = true;
}

echo json_encode(['status' => 'success', 'is_favorite' => $new_status]);
?>
