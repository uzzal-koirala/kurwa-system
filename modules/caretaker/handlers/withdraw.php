<?php
require_once '../../../includes/core/config.php';

if (!isset($_SESSION['caretaker_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caretaker_id = $_SESSION['caretaker_id'];
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $method = $_POST['method'] ?? 'esewa';
    $target = $_POST['target'] ?? '';

    if ($amount < 500) {
        header("Location: ../earnings.php?error=Minimum withdrawal is Rs. 500");
        exit;
    }

    // Check balance
    $caretaker = $conn->query("SELECT balance FROM caretakers WHERE id = $caretaker_id")->fetch_assoc();
    if ($caretaker['balance'] < $amount) {
        header("Location: ../earnings.php?error=Insufficient balance");
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Deduct balance
        $stmt1 = $conn->prepare("UPDATE caretakers SET balance = balance - ?, patients_helped = patients_helped + 0 WHERE id = ?");
        $stmt1->bind_param("di", $amount, $caretaker_id);
        $stmt1->execute();

        // 2. Record transaction
        $desc = "Withdrawal to " . ($method === 'esewa' ? 'eSewa' : 'Bank') . " ($target)";
        $transaction_id = uniqid('WDR-');
        $stmt2 = $conn->prepare("INSERT INTO transactions (caretaker_id, type, amount, description, status, transaction_id) VALUES (?, 'withdrawal', ?, ?, 'completed', ?)");
        $stmt2->bind_param("idss", $caretaker_id, $amount, $desc, $transaction_id);
        $stmt2->execute();

        $conn->commit();
        header("Location: ../earnings.php?success=Withdrawal completed");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../earnings.php?error=Withdrawal failed: " . $e->getMessage());
    }
}
?>
