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
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $location_id = (int)$_POST['location_id'];
    $hospital_id = (int)($_POST['hospital_id'] ?? 0);
    $status = $conn->real_escape_string($_POST['status'] ?? 'active');
    $rating = (float)($_POST['rating'] ?? 5.0);
    $opening_time = $conn->real_escape_string($_POST['opening_time'] ?? '08:00:00');
    $closing_time = $conn->real_escape_string($_POST['closing_time'] ?? '22:00:00');
    
    // Existing image URL (fallback)
    $image_url = $_POST['image_url'] ?? '';

    // Handle File Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['image_file']['name']);
        $upload_dir = '../../../assets/images/restaurants/';
        $target_file = $upload_dir . $file_name;
        
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_url = 'assets/images/restaurants/' . $file_name;
        }
    }

    if ($action === 'add') {
        $pass_raw = $_POST['password'] ?? 'kurwa123';
        $hashed_pass = password_hash($pass_raw, PASSWORD_DEFAULT);
        $sql = "INSERT INTO restaurants (name, email, phone, password, address, location_id, hospital_id, image_url, status, rating, opening_time, closing_time, verified) 
                VALUES ('$name', '$email', '$phone', '$hashed_pass', '$address', $location_id, $hospital_id, '$image_url', '$status', $rating, '$opening_time', '$closing_time', 1)";
    } else {
        $id = (int)$_POST['id'];
        $pass_update = "";
        if (!empty($_POST['password'])) {
            $hashed_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $pass_update = ", password = '$hashed_pass'";
        }

        $sql = "UPDATE restaurants SET 
                name = '$name', 
                email = '$email', 
                phone = '$phone',
                address = '$address',
                location_id = $location_id,
                hospital_id = $hospital_id,
                image_url = '$image_url',
                status = '$status',
                rating = $rating,
                opening_time = '$opening_time',
                closing_time = '$closing_time',
                verified = 1
                $pass_update
                WHERE id = $id";
    }

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Restaurant saved successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} 
elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    if ($conn->query("DELETE FROM restaurants WHERE id = $id")) {
        echo json_encode(['success' => true, 'message' => 'Restaurant removed.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>
