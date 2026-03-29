<?php
require_once 'includes/core/config.php';
$query = "
    SELECT h.*, l.location_name 
    FROM hospitals h 
    JOIN service_locations l ON h.location_id = l.id 
    ORDER BY l.location_name ASC, h.hospital_name ASC
";
$res = $conn->query($query);
if (!$res) {
    echo "QUERY FAILED: " . $conn->error . "\n";
} else {
    echo "QUERY SUCCESSFUL: " . $res->num_rows . " rows found.\n";
}
?>
