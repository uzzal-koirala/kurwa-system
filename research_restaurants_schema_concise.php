<?php
require_once 'includes/core/config.php';
$res = $conn->query("DESCRIBE restaurants");
echo "Schema for restaurants:\n";
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
