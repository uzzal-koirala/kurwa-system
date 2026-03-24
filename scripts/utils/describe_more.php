<?php
$conn = new mysqli('localhost', 'root', '', 'kurwa_db');
foreach(['pharmacies', 'restaurants'] as $table) {
    echo "--- $table ---\n";
    $res = $conn->query("DESCRIBE $table");
    if($res) while($row = $res->fetch_assoc()) echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
