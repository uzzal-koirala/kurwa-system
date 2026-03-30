<?php
require_once 'includes/core/config.php';
$res = $conn->query("DESCRIBE caretakers");
echo "Schema for caretakers:\n";
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
