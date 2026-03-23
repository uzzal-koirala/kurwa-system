<?php
require_once 'includes/core/config.php';

// Find a caretaker with the most bookings or reviews
$res = $conn->query("SELECT caretaker_id, COUNT(*) as c FROM transactions GROUP BY caretaker_id ORDER BY c DESC LIMIT 1");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $id = $row['caretaker_id'];
    echo "Attempting to delete caretaker ID: $id (has transactions)\n";
    
    // Simulate what the API does
    $conn->query("DELETE FROM caretaker_favorites WHERE caretaker_id = $id");
    $conn->query("DELETE FROM caretaker_reviews WHERE caretaker_id = $id");
    $conn->query("DELETE FROM caretaker_bookings WHERE caretaker_id = $id");

    $sql = "DELETE FROM caretakers WHERE id = $id";
    if ($conn->query($sql)) {
        echo "Deleted successfully!\n";
    } else {
        echo "MySQL Error on DELETE caretakers: " . $conn->error . "\n";
    }
} else {
    echo "No transactions found.\n";
}
?>
