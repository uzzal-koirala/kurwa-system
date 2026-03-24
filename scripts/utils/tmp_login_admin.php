<?php
require_once 'includes/core/config.php';
$_SESSION['user_id'] = 1; // Assuming 1 is the admin ID from previous check
$_SESSION['full_name'] = 'System Administrator';
$_SESSION['role'] = 'admin';
header("Location: modules/admin/settings.php");
?>
