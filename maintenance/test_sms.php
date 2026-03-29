<?php
require_once 'includes/core/config.php';
require_once 'includes/core/sms_helper.php';

$test_phone = "9842426806"; // My best guess or a test number
$test_message = "Kurwa System Test OTP: 123456";

echo "Sending SMS to $test_phone...\n";
$result = send_sms($test_phone, $test_message);

echo "Result:\n";
print_r($result);
?>
