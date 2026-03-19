<?php
require_once '../../../includes/core/config.php';

if (!isset($_SESSION['restaurant_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $owner_name = trim($_POST['owner_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Store Name and Email are required.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE restaurants SET name=?, owner_name=?, email=?, phone=?, address=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $owner_name, $email, $phone, $address, $restaurant_id);

    if ($stmt->execute()) {
        // Update session name if changed
        $_SESSION['restaurant_name'] = $name;
        echo json_encode(['success' => true, 'message' => 'Settings updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update settings.']);
    }
    $stmt->close();
    exit;
}
