<?php
require_once '../../../includes/core/config.php';
require_once '../../../includes/core/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    
    if ($amount < 100) {
        echo json_encode(['success' => false, 'message' => 'Minimum amount is Rs. 100']);
        exit;
    }

    // Generate a unique transaction ID
    $transaction_uuid = uniqid('TXN-', true);
    
    // Test Credentials from User
    $merchant_id = 'EPAYTEST';
    $secret_key = '8gBm/:&EnhH.1/q';
    
    // Required parameters for eSewa Epay-v2
    $total_amount = $amount;
    $transaction_uuid_esewa = str_replace('.', '-', $transaction_uuid); // eSewa expects alphanumeric/hyphen
    $product_code = $merchant_id;
    
    // Construct the signature string
    // total_amount,transaction_uuid,product_code
    $signature_string = "total_amount=$total_amount,transaction_uuid=$transaction_uuid_esewa,product_code=$product_code";
    
    // Generate HMAC-SHA256 signature
    $signature = base64_encode(hash_hmac('sha256', $signature_string, $secret_key, true));

    // Store the pending transaction in our database
    $insert_sql = "INSERT INTO transactions (user_id, type, amount, description, status, transaction_id) 
                   VALUES (?, 'topup', ?, 'eSewa Top Up (Pending)', 'pending', ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ids", $user_id, $amount, $transaction_uuid_esewa);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'url' => 'https://rc.esewa.com.np/api/epay/main/v2/form',
            'params' => [
                'amount' => $amount,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'transaction_uuid' => $transaction_uuid_esewa,
                'product_code' => $merchant_id,
                'product_service_charge' => 0,
                'product_delivery_charge' => 0,
                'success_url' => 'http://localhost/Kurwa/kurwa-system/modules/user/esewa_success.php',
                'failure_url' => 'http://localhost/Kurwa/kurwa-system/modules/user/esewa_failure.php',
                'signed_field_names' => 'total_amount,transaction_uuid,product_code',
                'signature' => $signature
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to initiate transaction']);
    }
    exit;
}
?>
