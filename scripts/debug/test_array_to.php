<?php
$api_token = "nsms_live_e843cefc31e8fe75c1ab559efb5a1d9675fef69d9e9f0fb7e335f718122e8e0f";
$url = "https://auth.nestsms.com/api/v1/sms/send";
$to = ["9779804031626"]; // ARRAY format
$message = "Array Test OTP: 000000";

$senders = ["Kurwa", "NestSMS", "MyBrand", "9779804031626"];

foreach ($senders as $sid) {
    echo "Testing Sender ID: $sid with Array To\n";
    $data = [
        "to" => $to,
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

    echo "HTTP Code: $http_code | Response: $response\n\n";
}
?>
