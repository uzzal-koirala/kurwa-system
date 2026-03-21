<?php
require_once '../../includes/core/config.php';

session_start();

// If already logged in as admin, redirect
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email' AND role = 'admin'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid administrator credentials.";
        }
    } else {
        $error = "Access denied. Administrator account not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login | Kurwa</title>
    <link rel="stylesheet" href="../../assets/css/admin_login.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-login-body">

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="admin-logo">
                    <i class="ri-shield-flash-line"></i>
                </div>
                <h2>Super Admin</h2>
                <p>Authorized personnel only. System entry.</p>
            </div>

            <?php if($error): ?>
                <div style="background:rgba(239, 68, 68, 0.1); color:#ef4444; padding:12px; border-radius:10px; margin-bottom:20px; font-size:13px; text-align:center; border:1px solid rgba(239, 68, 68, 0.2);">
                    <i class="ri-error-warning-line"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Admin Email</label>
                    <div class="input-wrapper">
                        <i class="ri-mail-line"></i>
                        <input type="email" name="email" class="form-control" placeholder="admin@kurwa.com" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Secret Key</label>
                    <div class="input-wrapper">
                        <i class="ri-lock-2-line"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="login-btn">Authenticate Access</button>
            </form>

            <div class="login-footer">
                <a href="../user/login.php"><i class="ri-arrow-left-line"></i> Back to User Portal</a>
            </div>
        </div>
    </div>

</body>
</html>
