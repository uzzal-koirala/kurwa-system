<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name = $conn->real_escape_string($_POST['name']);
    $sql = "INSERT INTO locations (name) VALUES ('$name')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Location added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} 
elseif ($action === 'edit') {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $sql = "UPDATE locations SET name = '$name' WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Location updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} 
elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    if ($conn->query("DELETE FROM locations WHERE id = $id")) {
        echo json_encode(['success' => true, 'message' => 'Location deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>
