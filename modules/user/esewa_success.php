<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

if (isset($_GET['data'])) {
    $encoded_data = $_GET['data'];
    $decoded_data = json_decode(base64_decode($encoded_data), true);
    
    if ($decoded_data && $decoded_data['status'] === 'COMPLETE') {
        $transaction_uuid = $decoded_data['transaction_uuid'];
        $amount = $decoded_data['total_amount'];
        $ref_id = $decoded_data['transaction_code'];

        // 1. Check if transaction exists and is pending
        $check_sql = "SELECT * FROM transactions WHERE transaction_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $transaction_uuid);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $txn = $res->fetch_assoc();
            $user_id = $txn['user_id'];

            // 2. Start Transaction
            $conn->begin_transaction();
            try {
                // Update Transaction Status
                $update_txn = "UPDATE transactions SET status = 'completed', description = 'eSewa Top Up (Ref: $ref_id)' WHERE id = ?";
                $stmt_up = $conn->prepare($update_txn);
                $stmt_up->bind_param("i", $txn['id']);
                $stmt_up->execute();

                // Update User Balance
                $update_bal = "UPDATE users SET balance = balance + ? WHERE id = ?";
                $stmt_bal = $conn->prepare($update_bal);
                $stmt_bal->bind_param("di", $amount, $user_id);
                $stmt_bal->execute();

                $conn->commit();
                header("Location: payments.php?success=Top up successful! Rs. " . number_format($amount, 2) . " has been added to your wallet.");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                header("Location: payments.php?error=Balance update failed. Please contact support with Ref: $ref_id");
                exit;
            }
        } else {
            header("Location: payments.php?error=Transaction already processed or not found.");
            exit;
        }
    }
}

header("Location: payments.php?error=Payment verification failed.");
exit;
?>
