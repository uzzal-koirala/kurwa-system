<?php
require_once '../../../includes/core/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name = trim($_POST['name']);
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO caretaker_categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add category']);
    }
} 
elseif ($action === 'edit') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    
    if ($id <= 0 || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE caretaker_categories SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $name, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update category']);
    }
}
elseif ($action === 'delete') {
    $id = intval($_POST['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM caretaker_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
