<?php
require_once '../includes/core/config.php';
$result = $conn->query("DESCRIBE caretaker_bookings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error describing caretaker_bookings: " . $conn->error;
}

$result2 = $conn->query("DESCRIBE messages");
if ($result2) {
    while ($row = $result2->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error describing messages: " . $conn->error;
}
?>
