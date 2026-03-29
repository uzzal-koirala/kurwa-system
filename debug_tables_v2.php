<?php
require_once 'includes/core/config.php';
echo "Tables in DB:\n";
$res = $conn->query("SHOW TABLES");
if (!$res) {
    die("Query failed: " . $conn->error);
}
while($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
?>
