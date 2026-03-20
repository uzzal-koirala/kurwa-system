<?php
require_once '../../includes/core/config.php';
session_destroy();
header("Location: login.php");
exit;
?>
