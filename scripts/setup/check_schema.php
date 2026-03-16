<?php
require_once '../../includes/core/config.php';
$res = $conn->query("DESCRIBE transactions");
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}
?>
