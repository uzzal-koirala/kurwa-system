<?php
require_once 'includes/core/config.php';
$pass = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$pass', role = 'admin' WHERE email = 'koiralasujjwals@gmail.com'");
echo "Password updated for koiralasujjwals@gmail.com to admin123\n";
?>
