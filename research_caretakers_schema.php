<?php
require_once 'includes/core/config.php';
$res = $conn->query("DESCRIBE caretakers");
while($row = $res->fetch_array()) {
    echo $row[0] . " (" . $row[1] . ")\n";
}
?>
