<?php
require_once '../includes/core/config.php';

// First, check what we have
$res = $conn->query("DESCRIBE caretaker_bookings");
$cols = [];
while($row = $res->fetch_assoc()) {
    $cols[] = $row['Field'];
}

echo "Current columns: " . implode(", ", $cols) . "\n";

// Ensure required columns exist
$required = ['start_date', 'end_date', 'total_price'];
foreach ($required as $col) {
    if (!in_array($col, $cols)) {
        echo "Adding missing column: $col...\n";
        $type = ($col === 'total_price') ? "DECIMAL(10,2) NOT NULL" : "DATE NOT NULL";
        $conn->query("ALTER TABLE caretaker_bookings ADD COLUMN `$col` $type");
    }
}

// Check again
$res = $conn->query("DESCRIBE caretaker_bookings");
$cols = [];
while($row = $res->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo "New columns: " . implode(", ", $cols) . "\n";
?>
