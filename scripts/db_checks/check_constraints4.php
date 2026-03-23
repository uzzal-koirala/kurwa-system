<?php
require_once 'includes/core/config.php';

// First try to just get the MySQL error when deleting caretaker ID 12 (or highest ID).
$res = $conn->query("SELECT id FROM caretakers ORDER BY id DESC LIMIT 1");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $id = $row['id'];
    
    echo "Attempting to delete ID: $id\n";
    
    // Simulate what the API does
    $conn->query("DELETE FROM caretaker_favorites WHERE caretaker_id = $id");
    $conn->query("DELETE FROM caretaker_reviews WHERE caretaker_id = $id");
    $conn->query("DELETE FROM caretaker_bookings WHERE caretaker_id = $id");

    $sql = "DELETE FROM caretakers WHERE id = $id";
    if ($conn->query($sql)) {
        echo "Deleted successfully!\n";
    } else {
        echo "MySQL Error: " . $conn->error . "\n";
    }
} else {
    echo "No caretakers found.\n";
}
?>
