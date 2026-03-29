<?php
require_once 'includes/core/config.php';

function describeTable($conn, $table) {
    echo "--- Table: $table ---\n";
    $res = $conn->query("DESCRIBE $table");
    if ($res) {
        while($row = $res->fetch_array()) { echo "  " . $row[0] . " (" . $row[1] . ")\n"; }
    } else { echo "  Failed to describe table.\n"; }
    
    $res = $conn->query("SELECT * FROM $table LIMIT 2");
    if ($res && $res->num_rows > 0) {
        echo "  Sample Data:\n";
        while($row = $res->fetch_assoc()) { print_r($row); }
    } else { echo "  No data found.\n"; }
}

describeTable($conn, 'hospitals');
describeTable($conn, 'locations');
describeTable($conn, 'service_locations');
?>
