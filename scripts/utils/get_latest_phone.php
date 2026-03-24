<?php
include '../../includes/core/config.php';
$res = $conn->query('SELECT phone FROM users ORDER BY created_at DESC LIMIT 1');
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo $row['phone'];
}
?>
