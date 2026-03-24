<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Kurwa/kurwa-system/modules/user/login.php");
    exit();
}

// Ensure full_name, hospital_id, profile_picture, role, and location_id are available
if (!isset($_SESSION['full_name']) || !isset($_SESSION['hospital_id']) || !isset($_SESSION['profile_picture']) || !isset($_SESSION['role']) || !isset($_SESSION['location_id'])) {
    require_once SITE_ROOT . '/includes/core/config.php';
    $uid = $_SESSION['user_id'];
    $u_res = $conn->query("SELECT full_name, hospital_id, profile_picture, role, location_id FROM users WHERE id = $uid");
    if ($u_res && $u_res->num_rows > 0) {
        $u_data = $u_res->fetch_assoc();
        $_SESSION['full_name'] = $u_data['full_name'];
        $_SESSION['hospital_id'] = $u_data['hospital_id'];
        $_SESSION['profile_picture'] = $u_data['profile_picture'];
        $_SESSION['role'] = $u_data['role'];
        $_SESSION['location_id'] = $u_data['location_id'];
    }
}

// Redirect to onboarding if hospital_id is not set
if (!isset($_SESSION['hospital_id']) || empty($_SESSION['hospital_id'])) {
    $current_uri = $_SERVER['REQUEST_URI'];
    if (strpos($current_uri, 'onboarding.php') === false && strpos($current_uri, 'get_hospitals.php') === false) {
        header("Location: /Kurwa/kurwa-system/modules/user/onboarding.php");
        exit();
    }
}
?>
