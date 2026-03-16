<?php
require_once '../includes/core/config.php';
$caretaker_id = 1; // Sample ID
$booking_sql = "SELECT start_date, end_date FROM caretaker_bookings WHERE caretaker_id = ? AND status IN ('pending', 'confirmed')";
$b_stmt = $conn->prepare($booking_sql);
if ($b_stmt) {
    echo "Query prepared successfully!\n";
} else {
    echo "Query preparation FAILED!\n";
    echo "Error: " . $conn->error . "\n";
}
?>
