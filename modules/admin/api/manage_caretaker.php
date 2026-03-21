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
    $video_url = $conn->real_escape_string($_POST['video_url']);
    
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
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} 
else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
