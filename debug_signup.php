<?php
require_once 'includes/core/config.php';
require_once 'includes/core/sms_helper.php';

$full_name = "Test Caretaker";
$email = "test_" . time() . "@example.com";
$phone = "9842426806"; // Test number
$category = "Elderly Care";
$specialization = "Specialist";
$password = "testpass";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$otp = rand(100000, 999999);

echo "Attempting to insert test caretaker...\n";
$stmt = $conn->prepare("INSERT INTO caretakers (full_name, email, phone, category, specialization, password, otp, verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");

if (!$stmt) {
    die("Prepare failed: " . $conn->error . "\n");
}

$stmt->bind_param("sssssss", $full_name, $email, $phone, $category, $specialization, $hashed_password, $otp);

if ($stmt->execute()) {
    echo "Insert successful! OTP is: $otp\n";
    echo "Sending SMS...\n";
    $sms_message = "Dear " . explode(' ', $full_name)[0] . ", your Kurwa System test code is: $otp.";
    $sms_res = send_sms($phone, $sms_message);
    print_r($sms_res);
} else {
    echo "Execute failed: " . $stmt->error . "\n";
}
?>
