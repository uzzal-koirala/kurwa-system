<?php
require_once 'includes/core/config.php';

$tables = ['caretakers', 'users', 'restaurants', 'pharmacies'];
$schema = [];

foreach ($tables as $table) {
    if ($result = $conn->query("DESCRIBE $table")) {
        while ($row = $result->fetch_assoc()) {
            $schema[$table][] = $row;
        }
    } else {
        $schema[$table] = "Error or table does not exist: " . $conn->error;
    }
}

echo json_encode($schema, JSON_PRETTY_PRINT);
?>
