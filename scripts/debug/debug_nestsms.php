<?php
$api_token = "nsms_live_e843cefc31e8fe75c1ab559efb5a1d9675fef69d9e9f0fb7e335f718122e8e0f";
$url = "https://auth.nestsms.com/api/v1/sms/send";

$sender_ids = ["Default", "NestSMS", "Info", "SMS", "TEST", "Kurwa"];
$phones = ["9779829399342", "9829399342"]; // Test with and without 977
$message = "Debug OTP: 123456";

foreach ($sender_ids as $sid) {
    foreach ($phones as $phone) {
        echo "Testing Sender ID: $sid | Phone: $phone\n";
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
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "HTTP Code: $http_code\n";
        echo "Response: $response\n\n";
        
        $result = json_decode($response, true);
        if (isset($result['success']) && $result['success'] == true) {
            echo "MATCH FOUND! Sender ID: $sid, Phone Format: $phone\n";
            exit;
        }
    }
}
?>
