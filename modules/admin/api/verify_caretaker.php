<?php
require_once '../../../includes/core/config.php';
require_once '../../../includes/core/sms_helper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = intval($_POST['id']);
$action = $_POST['action'] ?? '';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Expert ID']);
    exit;
}

if ($action === 'approve') {
    $first_name = $_POST['name'] ?? 'Expert';
    $phone = $_POST['phone'] ?? '';
    
    $stmt = $conn->prepare("UPDATE caretakers SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Send SMS in Nepali
        if (!empty($phone)) {
            $nepali_msg = "नमस्ते $first_name, तपाइँको कुर्वा सिस्टम केयरटेकर खाता स्वीकृत भएको छ। तपाइँ अब काम सुरु गर्न सक्नुहुन्छ।";
            send_sms($phone, $nepali_msg);
        }
        echo json_encode(['success' => true, 'message' => 'Expert approved.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed.']);
    }
} 
elseif ($action === 'disapprove') {
    $stmt = $conn->prepare("UPDATE caretakers SET status = 'disapproved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Expert disapproved.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed.']);
    }
}
?>
