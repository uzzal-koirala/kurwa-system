<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = 'user'; // We only manage 'user' role here for safety
    $verified = isset($_POST['verified']) ? 1 : 0;
    
    if (empty($full_name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Name and Email are required.']);
        exit;
    }

    if ($action === 'add') {
        $password = trim($_POST['password']);
        if (empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Password is required for new users.']);
            exit;
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, verified) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $full_name, $email, $hashed_password, $role, $verified);
    } else {
        $id = intval($_POST['id']);
        if (!empty($_POST['password'])) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, verified = ? WHERE id = ? AND role = 'user'");
            $stmt->bind_param("sssii", $full_name, $email, $hashed_password, $verified, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, verified = ? WHERE id = ? AND role = 'user'");
            $stmt->bind_param("ssii", $full_name, $email, $verified, $id);
        }
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User saved successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} 

elseif ($action === 'delete') {
    $id = intval($_POST['id']);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid User ID']);
        exit;
    }

    // Start Transaction for absolute integrity
    $conn->begin_transaction();

    try {
        // 1. Delete dependent records (Medicine Orders)
        $conn->query("DELETE FROM medicine_orders WHERE user_id = $id");
        
        // 2. Delete dependent records (Food Orders)
        $conn->query("DELETE FROM food_orders WHERE user_id = $id");
        
        // 3. Delete dependent records (Caretaker Bookings & Reviews)
        $conn->query("DELETE FROM caretaker_bookings WHERE user_id = $id");
        $conn->query("DELETE FROM caretaker_reviews WHERE user_id = $id");
        
        // 4. Delete Favorites & Notifications
        $conn->query("DELETE FROM caretaker_favorites WHERE user_id = $id");
        $conn->query("DELETE FROM notifications WHERE user_id = $id");
        
        // 5. Delete Onboarding & Cart
        $conn->query("DELETE FROM user_onboarding_answers WHERE user_id = $id");
        $conn->query("DELETE FROM cart WHERE user_id = $id");

        // 6. Finally Delete the user
        $conn->query("DELETE FROM users WHERE id = $id AND role = 'user'");

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'User and ALL related data deleted successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Critical Error during deletion: ' . $e->getMessage()]);
    }
}
?>
