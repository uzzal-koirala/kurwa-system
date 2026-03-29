<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $location_id = (int)$_POST['location_id'];
    $hospital_id = (int)($_POST['hospital_id'] ?? 0);
    $status = $conn->real_escape_string($_POST['status'] ?? 'open');
    $rating = (float)($_POST['rating'] ?? 5.0);
    $opening_time = $conn->real_escape_string($_POST['opening_time'] ?? '08:00:00');
    $closing_time = $conn->real_escape_string($_POST['closing_time'] ?? '20:00:00');
    $delivery_time = $conn->real_escape_string($_POST['delivery_time'] ?? '30-45 min');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $video_url = $conn->real_escape_string($_POST['video_url'] ?? '');
    
    // Existing image URL (fallback)
    $image_url = $_POST['image_url'] ?? '';

    // Handle File Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['image_file']['name']);
        $upload_dir = '../../../assets/images/pharmacies/';
        $target_file = $upload_dir . $file_name;
        
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_url = 'assets/images/pharmacies/' . $file_name;
        }
    }

    if ($action === 'add') {
        $sql = "INSERT INTO pharmacies (name, phone, address, location_id, hospital_id, rating, delivery_time, image_url, status, description, video_url, opening_time, closing_time, verified) 
                VALUES ('$name', '$phone', '$address', $location_id, $hospital_id, $rating, '$delivery_time', '$image_url', '$status', '$description', '$video_url', '$opening_time', '$closing_time', 1)";
    } else {
        $id = (int)$_POST['id'];
        $sql = "UPDATE pharmacies SET 
                name = '$name', 
                phone = '$phone',
                address = '$address',
                location_id = $location_id,
                hospital_id = $hospital_id,
                rating = $rating,
                delivery_time = '$delivery_time',
                image_url = '$image_url',
                status = '$status',
                description = '$description',
                video_url = '$video_url',
                opening_time = '$opening_time',
                closing_time = '$closing_time'
                WHERE id = $id";
    }

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Pharmacy saved successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} 
elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    if ($conn->query("DELETE FROM pharmacies WHERE id = $id")) {
        echo json_encode(['success' => true, 'message' => 'Pharmacy removed.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>
