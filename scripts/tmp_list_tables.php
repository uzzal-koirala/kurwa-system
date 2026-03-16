<?php
require_once __DIR__ . '/../includes/config.php';
$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_array()) {
    echo $row[0] . "\n";
}
?>
