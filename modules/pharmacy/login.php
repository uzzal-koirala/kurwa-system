<?php
session_start();
require_once '../../includes/core/config.php';

// Redirect if already logged in as a pharmacy
if(isset($_SESSION['pharmacy_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, name, password, status FROM pharmacies WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $pharmacy = $result->fetch_assoc();
        
        if (password_verify($password, $pharmacy['password'])) {
            if ($pharmacy['status'] !== 'open') {
                $error = "Your pharmacy account is currently " . htmlspecialchars($pharmacy['status']) . ". Please contact admin.";
            } else {
                $_SESSION['pharmacy_id'] = $pharmacy['id'];
                $_SESSION['pharmacy_name'] = $pharmacy['name'];
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Partner Log In | Kurwa System</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/pharmacy_login.css">
</head>
<body>

    <div class="login-wrapper">
        <div class="left-panel">
            <div class="left-content">
                <div class="brand">
                    <i class="ri-capsule-fill brand-icon"></i>
                    Kurwa Pharmacy
                </div>
                <h1>Empower Your <br>Medical Storefront</h1>
                <p>Join the largest network of verified healthcare and medicine providers in Nepal. Deliver health seamlessly.</p>
                
                <div class="stats-row">
                    <div class="stat-item">
                        <h3>10k+</h3>
                        <span>Active Patients</span>
                    </div>
                    <div class="stat-item">
                        <h3>Fast</h3>
                        <span>Reliable Delivery</span>
                    </div>
                    <div class="stat-item">
                        <h3>Secure</h3>
                        <span>Payment Processing</span>
                    </div>
                </div>

            </div>
            <div class="left-bg-pattern"></div>
        </div>

        <div class="right-panel">
            <div class="login-box">
                <div class="login-header">
                    <h2>Pharmacy Partner Log in</h2>
                    <p>Enter your credentials to access your dashboard</p>
                </div>

                <?php if($error): ?>
                    <div class="error-alert">
                        <i class="ri-error-warning-fill"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Email address</label>
                        <div class="input-wrapper">
                            <i class="ri-mail-line"></i>
                            <input type="email" name="email" placeholder="pharmacy@kurwa.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <i class="ri-lock-password-line"></i>
                            <input type="password" name="password" id="passwordField" placeholder="••••••••" required>
                            <i class="ri-eye-line toggle-password" onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <button type="submit" class="login-btn">
                        Sign In to Dashboard <i class="ri-arrow-right-line"></i>
                    </button>
                    
                    <a href="../../login.php" class="back-link">
                        <i class="ri-arrow-left-line"></i> Back to Main Portal
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const field = document.getElementById('passwordField');
            const icon = document.querySelector('.toggle-password');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            } else {
                field.type = 'password';
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            }
        }
    </script>
</body>
</html>
