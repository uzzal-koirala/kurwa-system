<?php
require_once '../../../includes/core/config.php';

if (!isset($_SESSION['restaurant_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $location_id = intval($_POST['location_id'] ?? 0);
    $hospital_id = intval($_POST['hospital_id'] ?? 0);

    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Store Name and Email are required.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE restaurants SET name=?, email=?, phone=?, address=?, location_id=?, hospital_id=? WHERE id=?");
    $stmt->bind_param("ssssiii", $name, $email, $phone, $address, $location_id, $hospital_id, $restaurant_id);

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
