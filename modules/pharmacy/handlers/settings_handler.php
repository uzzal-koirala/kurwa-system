<?php
header('Content-Type: application/json');
require_once '../../../includes/core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['pharmacy_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$pharmacy_id = $_SESSION['pharmacy_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $location_id = intval($_POST['location_id'] ?? 0);
    $hospital_id = intval($_POST['hospital_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'open');

    if (empty($name) || empty($email) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Name, Email, and Phone are required.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE pharmacies SET name=?, email=?, phone=?, address=?, location_id=?, hospital_id=?, status=? WHERE id=?");
    $stmt->bind_param("ssssiiis", $name, $email, $phone, $address, $location_id, $hospital_id, $status, $pharmacy_id);

    if ($stmt->execute()) {
        $_SESSION['pharmacy_name'] = $name;
        echo json_encode(['success' => true, 'message' => 'Settings updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
?>
