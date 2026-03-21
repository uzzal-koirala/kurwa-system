<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

// Ensure only admin can access
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $category = $conn->real_escape_string($_POST['category']);
    $specialization = $conn->real_escape_string($_POST['specialization']);
    $rating = (float)($_POST['rating'] ?? 0);
    $experience = (int)($_POST['experience_years'] ?? 0);
    $price = (float)($_POST['price_per_day'] ?? 0);
    $patients = (int)($_POST['patients_helped'] ?? 0);
    $about = $conn->real_escape_string($_POST['about_text']);
    $image_url = $conn->real_escape_string($_POST['image_url']);
    $video_url = $conn->real_escape_string($_POST['video_url']);

    if ($action === 'add') {
        $sql = "INSERT INTO caretakers (full_name, category, specialization, rating, experience_years, price_per_day, patients_helped, about_text, image_url, video_url) 
                VALUES ('$full_name', '$category', '$specialization', $rating, $experience, $price, $patients, '$about', '$image_url', '$video_url')";
    } else {
        $id = (int)$_POST['id'];
        $sql = "UPDATE caretakers SET 
                full_name = '$full_name', 
                category = '$category', 
                specialization = '$specialization', 
                rating = $rating, 
                experience_years = $experience, 
                price_per_day = $price, 
                patients_helped = $patients, 
                about_text = '$about', 
                image_url = '$image_url',
                video_url = '$video_url'
                WHERE id = $id";
    }

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Caretaker saved successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} 
elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    $sql = "DELETE FROM caretakers WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Caretaker deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} 
else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
