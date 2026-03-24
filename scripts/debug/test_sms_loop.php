<?php
// Test different Sender IDs
$api_token = "nsms_live_e843cefc31e8fe75c1ab559efb5a1d9675fef69d9e9f0fb7e335f718122e8e0f";
$url = "https://auth.nestsms.com/api/v1/sms/send";

$sender_ids = ["Kurwa", "TEST", "SMS", "Info", "YourBrand", "MyBrand", "NestSMS", "Default"];
$phone = "+9779829399342";
$message = "Test message from API";

foreach ($sender_ids as $sid) {
    echo "Trying Sender ID: $sid\n";
    $data = [
        "to" => $phone,
        "message" => $message,
        "sender_id" => $sid
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_token",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    
    echo "Response: $response\n\n";
    $result = json_decode($response, true);
    if (isset($result['success']) && $result['success'] == true) {
        echo "SUCCESS! Valid sender ID is: $sid\n";
        break;
    }
}
?>
