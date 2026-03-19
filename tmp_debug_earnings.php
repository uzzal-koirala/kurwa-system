<?php
require_once 'includes/core/config.php';

echo "<h2>Debug Info</h2>";

// Check if balance exists in caretakers
$res1 = $conn->query("DESCRIBE caretakers");
if($res1) {
    echo "<h3>Caretakers Columns:</h3><ul>";
    while($row = $res1->fetch_assoc()) echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    echo "</ul>";
} else {
    echo "Error describing caretakers: " . $conn->error . "<br>";
}

// Check if caretaker_id exists in transactions
$res2 = $conn->query("DESCRIBE transactions");
if($res2) {
    echo "<h3>Transactions Columns:</h3><ul>";
    while($row = $res2->fetch_assoc()) echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    echo "</ul>";
} else {
    echo "Error describing transactions: " . $conn->error . "<br>";
}

// Check if total_earnings query works
$caretaker_id = $_SESSION['caretaker_id'] ?? 0;
if($caretaker_id) {
    $q = "SELECT SUM(total_price) as total FROM caretaker_bookings WHERE caretaker_id = $caretaker_id AND status = 'completed'";
    $res3 = $conn->query($q);
    if($res3) {
        $data = $res3->fetch_assoc();
        echo "<h3>Lifetime Earnings for ID $caretaker_id: " . $data['total'] . "</h3>";
    } else {
        echo "Error in lifetime earnings query: " . $conn->error . "<br>";
    }
} else {
    echo "No caretaker_id in session.<br>";
}
?>
