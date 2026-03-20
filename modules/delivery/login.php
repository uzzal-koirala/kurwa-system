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
        // Mock authentication for development
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
    <title>Delivery Partner Login | Kurwa</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/delivery_login.css">
</head>
<body class="flex items-center justify-center min-h-screen relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-green-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>

    <div class="max-w-4xl w-full mx-4 flex bg-white rounded-3xl overflow-hidden shadow-2xl relative z-10 login-card">
        <!-- Left Banner -->
        <div class="hidden md:flex flex-col md:w-2/5 bg-delivery p-10 text-white justify-between">
            <div>
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-green-600 shadow-xl">
                        <i class="ri-moped-fill text-2xl"></i>
                    </div>
                    <span class="text-2xl font-bold tracking-tight">Kurwa <span class="font-normal text-white text-opacity-80">Rider</span></span>
                </div>
                
                <h1 class="text-3xl font-extrabold mb-6 leading-tight">Deliver Smarter,<br>Earn Faster.</h1>
                <p class="text-white text-opacity-80 text-sm leading-relaxed mb-8">Join the elite Kurwa Rider network. Real-time routing, instant payouts, and flexible shifts.</p>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <i class="ri-checkbox-circle-fill text-xl text-white"></i>
                        <span class="text-sm font-medium">Automatic Routing</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="ri-checkbox-circle-fill text-xl text-white"></i>
                        <span class="text-sm font-medium">Daily Earnings Payout</span>
                    </div>
                </div>
            </div>
            
            <div class="delivery-badge w-fit">
                V 2.1.0 PARTNER
            </div>
        </div>

        <!-- Login Form Container -->
        <div class="w-full md:w-3/5 p-8 md:p-12 flex flex-col justify-center">
            <div class="mb-10">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Rider Login</h2>
                <p class="text-gray-500 font-medium">Ready for your first shift of the day?</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl mb-8 flex items-center gap-3 animate-pulse">
                    <i class="ri-error-warning-fill text-xl"></i>
                    <span class="text-sm font-bold"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Rider ID / Email</label>
                    <div class="relative">
                        <i class="ri-user-6-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></i>
                        <input type="email" name="email" class="w-full pl-12 pr-4 py-4 rounded-2xl input-field" placeholder="rider@kurwa.com" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Passcode</label>
                    <div class="relative">
                        <i class="ri-lock-2-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></i>
                        <input type="password" name="password" id="password" class="w-full pl-12 pr-12 py-4 rounded-2xl input-field" placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 transition-colors">
                            <i class="ri-eye-off-line text-xl" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between py-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" class="w-5 h-5 rounded-lg text-green-600 focus:ring-green-500 border-gray-300 transition-all">
                        <span class="text-sm text-gray-600 font-semibold group-hover:text-gray-900">Keep me active</span>
                    </label>
                    <a href="#" class="text-sm font-bold text-green-600 hover:text-green-700 underline underline-offset-4">Need help?</a>
                </div>

                <button type="submit" class="w-full py-4 text-white rounded-2xl font-bold text-lg btn-delivery flex justify-center items-center gap-3">
                    Start My Shift <i class="ri-flashlight-fill"></i>
                </button>
            </form>

            <div class="mt-10 pt-8 border-t border-gray-100 text-center">
                <p class="text-gray-500 font-semibold text-sm">
                    Not a partner yet? 
                    <a href="#" class="text-blue-600 font-black hover:underline ml-1">Join the Team</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ri-eye-line text-xl';
            } else {
                input.type = 'password';
                icon.className = 'ri-eye-off-line text-xl';
            }
        }
    </script>
</body>
</html>
