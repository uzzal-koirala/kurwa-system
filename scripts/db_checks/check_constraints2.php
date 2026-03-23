<?php
require_once 'includes/core/config.php';

// Find all foreign keys referencing caretakers
$sql = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_SCHEMA = 'kurwa_db' AND REFERENCED_TABLE_NAME = 'caretakers'";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    echo "Foreign Keys Referencing Caretakers:\n";
    while ($row = $res->fetch_assoc()) {
        echo "- Table `" . $row['TABLE_NAME'] . "`, Column `" . $row['COLUMN_NAME'] . "`\n";
    }
} else {
    echo "No explicit foreign key constraints referencing caretakers found.\n";
    // Check tables heuristically
    $tables = $conn->query("SHOW TABLES");
    echo "\nTables possibly containing caretaker_id:\n";
    while($t = $tables->fetch_array()) {
        $table = $t[0];
        $cols = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'caretaker_id'");
        if ($cols && $cols->num_rows > 0) {
            echo "- `$table`\n";
        }
    }
}

// Check PHP error log
if(file_exists('C:\\xampp\\php\\logs\\php_error_log')) {
    echo "\n\nRecent PHP Errors:\n";
    system('tail -n 20 C:\xampp\php\logs\php_error_log');
}
?>
