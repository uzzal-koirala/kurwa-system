<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Determine path to login.php based on current file location
    // This assumes dashboard pages are in modules/user/ and login.php is also there
    header("Location: login.php");
    exit();
}
?>
