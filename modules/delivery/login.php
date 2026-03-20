<?php
require_once '../../includes/core/config.php';

if (isset($_SESSION['delivery_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        if (($email === 'delivery@kurwa.com' && $password === 'password') || ($email === 'rider@kurwa.com' && $password === 'rider123')) {
            $_SESSION['delivery_id'] = 1;
            $_SESSION['delivery_name'] = "Suraj Thapa";
            $_SESSION['delivery_status'] = "offline";
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Portal Login | Kurwa</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/delivery_login.css">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-green-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 translate-x-1/3 translate-y-1/3"></div>

    <div class="max-w-4xl w-full mx-4 flex bg-white rounded-3xl overflow-hidden shadow-2xl relative z-10">
        <!-- Left Side -->
        <div class="hidden md:flex flex-col md:w-[35%] bg-gradient-delivery p-6 text-white justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-delivery-primary font-bold text-xl shadow-lg">
                        <i class="ri-moped-fill"></i>
                    </div>
                    <span class="text-2xl font-bold tracking-tight">Kurwa Rider</span>
                </div>
                
                <h1 class="text-2xl font-bold mb-4 leading-tight">Deliver Smarter,<br>Earn Faster.</h1>
                <p class="text-white text-opacity-90 text-sm">Join the elite Kurwa Rider network. Real-time routing and flexible shifts.</p>
            </div>
        </div>

        <!-- Right Side -->
        <div class="w-full md:w-[65%] p-6 md:p-8 flex flex-col justify-center">
            <div class="mb-6 text-center md:text-left">
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Welcome back</h2>
                <p class="text-sm text-gray-500">Please enter your rider credentials to sign in.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-6 text-sm flex items-center gap-2 font-medium">
                    <i class="ri-error-warning-line text-lg"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-4">
                <div class="relative">
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Email Address</label>
                    <div class="relative">
                        <i class="ri-mail-line absolute left-4 top-3.5 text-gray-400 text-lg"></i>
                        <input type="email" name="email" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass" placeholder="rider@kurwa.com" required>
                    </div>
                </div>

                <div class="relative">
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Password</label>
                    <div class="relative">
                        <i class="ri-lock-password-line absolute left-4 top-3.5 text-gray-400 text-lg"></i>
                        <input type="password" name="password" id="password" class="w-full pl-11 pr-12 py-3 rounded-xl input-glass" placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-3.5 text-gray-400 hover:text-delivery-primary transition-colors text-lg">
                            <i class="ri-eye-off-line" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-2 mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 rounded text-delivery-primary focus:ring-delivery-primary border-gray-300">
                        <span class="text-sm text-gray-600 font-medium">Remember me</span>
                    </label>
                    <a href="#" class="text-sm font-bold text-delivery-primary hover:text-green-700 transition-colors">Forgot password?</a>
                </div>

                <button type="submit" class="w-full py-3.5 text-white rounded-xl font-bold text-lg btn-delivery flex justify-center items-center gap-2">
                    Start Shift <i class="ri-arrow-right-line font-bold"></i>
                </button>
            </form>

            <p class="text-center text-gray-500 mt-6 font-medium text-sm">
                Want to become a Kurwa Rider? 
                <a href="#" class="text-delivery-secondary font-bold hover:underline">Apply now</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ri-eye-line';
            } else {
                input.type = 'password';
                icon.className = 'ri-eye-off-line';
            }
        }
    </script>
</body>
</html>
