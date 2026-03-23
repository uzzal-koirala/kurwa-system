<?php
require_once 'includes/core/config.php';

// First, find a caretaker ID to test with that has dependent records, or just check the schema.
$res = $conn->query("SHOW CREATE TABLE caretaker_bookings");
if ($res) {
    $row = $res->fetch_assoc();
    echo "Bookings Table:\n" . $row['Create Table'] . "\n\n";
}

$res = $conn->query("SHOW CREATE TABLE caretaker_reviews");
if ($res) {
    $row = $res->fetch_assoc();
    echo "Reviews Table:\n" . $row['Create Table'] . "\n\n";
}

$res = $conn->query("SHOW CREATE TABLE caretaker_favorites");
if ($res) {
    $row = $res->fetch_assoc();
    echo "Favorites Table:\n" . $row['Create Table'] . "\n\n";
}
?>
