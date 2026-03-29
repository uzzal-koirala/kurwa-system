<?php
require_once 'includes/core/config.php';

function describeTable($conn, $table) {
    echo "--- Table: $table ---\n";
    $res = $conn->query("DESCRIBE $table");
    if ($res) {
        while($row = $res->fetch_array()) { echo "  " . $row[0] . " (" . $row[1] . ")\n"; }
    } else { echo "  Failed to describe table.\n"; }
}

describeTable($conn, 'restaurants');
describeTable($conn, 'canteens');
describeTable($conn, 'restaurant_categories');
?>
