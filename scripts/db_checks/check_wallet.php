<?php
require_once 'includes/core/config.php';

$res = $conn->query("SHOW COLUMNS FROM users LIKE 'wallet_balance'");
if ($res && $res->num_rows > 0) {
    echo "wallet_balance exists in users!\n";
} else {
    echo "wallet_balance does NOT exist!\n";
}
?>
