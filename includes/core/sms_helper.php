<?php
// =============================================
// Kurwa System - SMS API Integration Helper
// =============================================

/**
 * Send an SMS using NestSMS API
 * 
 * @param string|array $to The recipient's phone number or array of numbers
 * @param string $message The SMS message content
 * @return array The response from the API, decoded as an associative array
 */
function send_sms($to, $message) {
    // API Configuration
    $api_token = "nsms_live_e843cefc31e8fe75c1ab559efb5a1d9675fef69d9e9f0fb7e335f718122e8e0f";
    $sender_id = null; // Set to null to use the account's default Sender ID
    $url = "https://auth.nestsms.com/api/v1/sms/send";

    // Normalize phone number (Remove '+', ensure '977' prefix if 10 digits)
    $to = str_replace('+', '', $to);
    if (strlen($to) === 10 && strpos($to, '9') === 0) {
        $to = "977" . $to;
    }

    // Prepare data
    $data = [
        "to" => $to,
        "message" => $message,
        "sender_id" => $sender_id
    ];

    $payload = json_encode($data);

    // Init cURL
    $ch = curl_init($url);
    
    // Set options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_token",
        "Content-Type: application/json"
    ]);

    // Execute request
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);

    if ($error) {
        error_log("SMS Send Error: " . $error);
        return [
            "success" => false,
            "error" => $error
        ];
    }

    $result = json_decode($response, true);
    
    // Check for API errors or HTTP errors
    if ($http_code >= 400 || (isset($result['success']) && $result['success'] === false)) {
       $error_msg = date('[Y-m-d H:i:s] ') . "SMS API Failure: " . $response . "\n";
       file_put_contents(dirname(__DIR__, 2) . '/logs/sms_debug.log', $error_msg, FILE_APPEND);
       return [
            "success" => false,
            "error" => "API Error $http_code. Response: $response",
            "details" => $result
       ]; 
    }

    return $result;
}
?>
