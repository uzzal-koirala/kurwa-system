<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

// eSewa failure/cancel callback
$msg = "Payment failed or was canceled by the user.";
header("Location: payments.php?error=" . urlencode($msg));
exit;
?>
