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
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $hospital_name = $conn->real_escape_string($_POST['hospital_name']);
    $rating = (float)($_POST['rating'] ?? 0);
    $experience = (int)($_POST['experience_years'] ?? 0);
    $price = (float)($_POST['price_per_day'] ?? 0);
    $patients = (int)($_POST['patients_helped'] ?? 0);
    $about = $conn->real_escape_string($_POST['about_text']);
    $video_url = $conn->real_escape_string($_POST['video_url']);
    $email = $conn->real_escape_string($_POST['email']);
    $pass_raw = $_POST['password'] ?? '';
    $location_id = (int)($_POST['location_id'] ?? 0);
    $hospital_id = (int)($_POST['hospital_id'] ?? 0);
    $opening_time = $conn->real_escape_string($_POST['opening_time'] ?? '09:00:00');
    $closing_time = $conn->real_escape_string($_POST['closing_time'] ?? '21:00:00');
    
    // Convert phone_number from form to phone for DB consistency
    $phone = $phone_number; 
    
    // Existing image URL (fallback)
    $image_url = $_POST['image_url'] ?? '';

    // Handle File Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['image_file']['name']);
        $upload_dir = '../../../assets/images/caretakers/';
        $target_file = $upload_dir . $file_name;
        
        // Ensure directory exists (redundant but safe)
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp, $target_file)) {
            // Store relative path for frontend compatibility
            $image_url = 'assets/images/caretakers/' . $file_name;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
            exit;
        }
    }

    if ($action === 'add') {
        $hashed_pass = password_hash($pass_raw, PASSWORD_DEFAULT);
        $sql = "INSERT INTO caretakers (full_name, phone, phone_number, email, password, hospital_name, category, specialization, rating, experience_years, price_per_day, location_id, hospital_id, patients_helped, about_text, image_url, video_url, verified, status, opening_time, closing_time, onboarding_completed) 
                VALUES ('$full_name', '$phone', '$phone', '$email', '$hashed_pass', '$hospital_name', '$category', '$specialization', $rating, $experience, $price, $location_id, $hospital_id, $patients, '$about', '$image_url', '$video_url', 1, 'approved', '$opening_time', '$closing_time', 1)";
    } else {
        $id = (int)$_POST['id'];
        $pass_update = "";
        if (!empty($pass_raw)) {
            $hashed_pass = password_hash($pass_raw, PASSWORD_DEFAULT);
            $pass_update = ", password = '$hashed_pass'";
        }

        $sql = "UPDATE caretakers SET 
                full_name = '$full_name', 
                phone = '$phone',
                phone_number = '$phone',
                email = '$email',
                hospital_name = '$hospital_name',
                category = '$category', 
                specialization = '$specialization', 
                rating = $rating, 
                experience_years = $experience, 
                price_per_day = $price, 
                location_id = $location_id,
                hospital_id = $hospital_id,
                patients_helped = $patients, 
                about_text = '$about', 
                image_url = '$image_url',
                video_url = '$video_url',
                opening_time = '$opening_time',
                closing_time = '$closing_time',
                onboarding_completed = 1,
                verified = 1,
                status = 'approved'
                $pass_update
                WHERE id = $id";
    }

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Caretaker saved successfully!', 'image_url' => $image_url]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} 
elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    
    // Optional: Delete physical file if it exists and is local
    $result = $conn->query("SELECT image_url FROM caretakers WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $img_path = $row['image_url'];
        if (!empty($img_path) && !str_starts_with($img_path, 'http')) {
            $old_img = '../../../' . $img_path;
            if (is_file($old_img) && file_exists($old_img)) {
                @unlink($old_img);
            }
        }
    }

    // Delete dependent records to avoid foreign key constraint errors
    $conn->query("DELETE FROM caretaker_favorites WHERE caretaker_id = $id");
    $conn->query("DELETE FROM caretaker_reviews WHERE caretaker_id = $id");
    $conn->query("DELETE FROM caretaker_bookings WHERE caretaker_id = $id");

    $sql = "DELETE FROM caretakers WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Caretaker deleted successfully.']);
    } else {
        $error = $conn->error;
        file_put_contents('delete_error_log.txt', date('[Y-m-d H:i:s] ') . "Delete ID $id failed: $error\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $error]);
    }
} 
else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
