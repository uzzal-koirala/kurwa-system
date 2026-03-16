<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Kurwa/kurwa-system/modules/user/login.php");
    exit();
}

// Ensure full_name is available for global header
if (!isset($_SESSION['full_name'])) {
    require_once 'config.php';
    $uid = $_SESSION['user_id'];
    $u_res = $conn->query("SELECT full_name FROM users WHERE id = $uid");
    if ($u_res && $u_res->num_rows > 0) {
        $_SESSION['full_name'] = $u_res->fetch_assoc()['full_name'];
    }
}
?>
