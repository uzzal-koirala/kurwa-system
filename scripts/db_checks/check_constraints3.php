<?php
require_once 'includes/core/config.php';

$tables = $conn->query("SHOW TABLES");
echo "All tables with caretaker_id:\n";
while($t = $tables->fetch_array()) {
    $table = $t[0];
    $cols = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'caretaker_id'");
    if ($cols && $cols->num_rows > 0) {
        echo "- `$table`\n";
    }
}
?>
