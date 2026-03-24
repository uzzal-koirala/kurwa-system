<?php
$api_token = "nsms_live_e843cefc31e8fe75c1ab559efb5a1d9675fef69d9e9f0fb7e335f718122e8e0f";

echo "--- Testing NULL sender_id ---\n";
$url = "https://auth.nestsms.com/api/v1/sms/send";
$data = [
    "to" => "9779804031626",
    "message" => "Null Test OTP",
    "sender_id" => null
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $api_token", "Content-Type: application/json"]);
$response = curl_exec($ch);
echo "Response: $response\n\n";

echo "--- Trying to GET senders ---\n";
$url = "https://auth.nestsms.com/api/v1/sms/senders";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $api_token"]);
$response = curl_exec($ch);
echo "Response: $response\n";
?>
