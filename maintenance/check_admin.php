<?php
require_once 'includes/core/config.php';
$res = $conn->query("SELECT email, role FROM users WHERE role = 'admin'");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo "Admin: " . $row['email'] . "\n";
    }
} else {
    echo "No admin found.\n";
}
?>
