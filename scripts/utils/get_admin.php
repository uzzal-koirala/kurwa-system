<?php
require_once '../../includes/core/config.php';
$result = $conn->query("SELECT email FROM users WHERE role = 'admin' LIMIT 1");
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "Admin Email: " . $user['email'];
} else {
    echo "No admin user found.";
}
?>
