<?php
require_once '../includes/core/config.php';
$result = $conn->query("DESCRIBE caretaker_reviews");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error describing caretaker_reviews: " . $conn->error;
}
?>
