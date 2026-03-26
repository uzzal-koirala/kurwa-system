<?php
require_once '../../../includes/core/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'caretaker') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Create upload directories
$photo_dir = "../../../uploads/caretakers/photos/";
$doc_dir = "../../../uploads/caretakers/documents/";

if (!is_dir($photo_dir)) mkdir($photo_dir, 0777, true);
if (!is_dir($doc_dir)) mkdir($doc_dir, 0777, true);

$photo_path = "";
$doc_path = "";

// Handle Photo
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = "photo_" . $user_id . "_" . time() . "." . $ext;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_dir . $filename)) {
        $photo_path = "uploads/caretakers/photos/" . $filename;
    }
}

// Handle Document
if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
    $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
    $filename = "doc_" . $user_id . "_" . time() . "." . $ext;
    if (move_uploaded_file($_FILES['document']['tmp_name'], $doc_dir . $filename)) {
        $doc_path = "uploads/caretakers/documents/" . $filename;
    }
}

if (empty($photo_path) || empty($doc_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload orientation files.']);
    exit;
}

$full_name = trim($_POST['full_name']);
$phone = trim($_POST['phone']);
$skills = trim($_POST['skills']);
$expertise = trim($_POST['expertise']);

$stmt = $conn->prepare("UPDATE caretakers SET full_name = ?, phone = ?, skills = ?, expertise = ?, photo = ?, document = ?, onboarding_completed = 1, status = 'pending' WHERE id = ?");
$stmt->bind_param("ssssssi", $full_name, $phone, $skills, $expertise, $photo_path, $doc_path, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Onboarding successful!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
?>
