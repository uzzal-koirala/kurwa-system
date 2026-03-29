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
    $loc_id = (int)$_POST['location_id'];
    $name = $conn->real_escape_string($_POST['hospital_name']);
    $addr = $conn->real_escape_string($_POST['address']);
    
    $sql = "INSERT INTO hospitals (location_id, name, address) VALUES ($loc_id, '$name', '$addr')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Hospital added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} 
elseif ($action === 'edit') {
    $id = (int)$_POST['id'];
    $loc_id = (int)$_POST['location_id'];
    $name = $conn->real_escape_string($_POST['hospital_name']);
    $addr = $conn->real_escape_string($_POST['address']);
    
    $sql = "UPDATE hospitals SET location_id = $loc_id, name = '$name', address = '$addr' WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Hospital updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} 
elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    if ($conn->query("DELETE FROM hospitals WHERE id = $id")) {
        echo json_encode(['success' => true, 'message' => 'Hospital deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>
