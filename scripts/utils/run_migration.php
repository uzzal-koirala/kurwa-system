<?php
$conn = new mysqli('localhost', 'root', '', 'kurwa_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = file_get_contents('../../database/onboarding_schema.sql');
if ($conn->multi_query($sql)) {
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Migration successful!\n";
} else {
    echo "Migration failed: " . $conn->error . "\n";
}
?>
