<?php
header('Content-Type: application/json');
require_once '../../../includes/core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['caretaker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$caretaker_id = $_SESSION['caretaker_id'];
$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $price_per_day = floatval($_POST['price_per_day'] ?? 0);
    $experience_years = intval($_POST['experience_years'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $about_text = trim($_POST['about_text'] ?? '');
    $location_id = intval($_POST['location_id'] ?? 0);
    $hospital_id = intval($_POST['hospital_id'] ?? 0);

    if (empty($full_name) || empty($email) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Name, Email, and Phone are required.']);
        exit;
    }

    // Handle Profile Photo Upload
    $image_query = "";
    $types = "sssdisssii";
    $params = [$full_name, $email, $phone, $price_per_day, $experience_years, $category, $specialization, $about_text, $location_id, $hospital_id];

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../../assets/uploads/caretakers/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = uniqid('caretaker_') . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $destination)) {
                $image_url_db = '../../assets/uploads/caretakers/' . $new_filename;
                $image_query = ", image_url = ?";
                $params[] = $image_url_db;
                $types .= "s";
                
                // Update session
                $_SESSION['caretaker_image'] = $image_url_db;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file format. Only JPG, PNG, GIF allowed.']);
            exit;
        }
    }

    $params[] = $caretaker_id;
    $types .= "i";

    $stmt = $conn->prepare("UPDATE caretakers 
                            SET full_name=?, email=?, phone=?, price_per_day=?, experience_years=?, category=?, specialization=?, about_text=?, location_id=?, hospital_id=?" . $image_query . "
                            WHERE id=?");
    
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['caretaker_name'] = $full_name;
        $_SESSION['caretaker_email'] = $email;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit;
}

if ($action === 'update_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
        exit;
    }

    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        exit;
    }

    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM caretakers WHERE id = ?");
    $stmt->bind_param("i", $caretaker_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (password_verify($current_password, $row['password'])) {
            // Update to new internal password
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE caretakers SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed, $caretaker_id);
            if ($update->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
