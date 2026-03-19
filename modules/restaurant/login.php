<?php
session_start();
require_once '../../includes/core/config.php';

if (isset($_SESSION['restaurant_id'])) {
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
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $restaurant = $result->fetch_assoc();
            if (password_verify($password, $restaurant['password'])) {
                $_SESSION['restaurant_id'] = $restaurant['id'];
                $_SESSION['restaurant_name'] = $restaurant['name'];
                $_SESSION['restaurant_image'] = $restaurant['image_url'] ?? null;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Portal Login | Kurwa Food</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .bg-gradient-rest {
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
        }
        .text-rest-primary { color: #ff7e5f; }
        .text-rest-secondary { color: #2f3cff; }
        .btn-rest {
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
            transition: all 0.3s transform;
        }
        .btn-rest:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 126, 95, 0.3);
        }
        .input-glass {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid #edf2f7;
            transition: all 0.3s;
        }
        .input-glass:focus {
            border-color: #ff7e5f;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(255, 126, 95, 0.1);
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute top-0 left-0 w-64 h-64 border-4 border-orange-200 rounded-full mix-blend-multiply opacity-50 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 translate-x-1/3 translate-y-1/3"></div>

    <div class="max-w-4xl w-full mx-4 flex bg-white rounded-3xl overflow-hidden shadow-2xl relative z-10">
        <!-- Left Side -->
        <div class="hidden md:flex flex-col md:w-1/2 bg-gradient-rest p-12 text-white justify-between relative overflow-hidden">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-rest-primary font-bold text-xl shadow-lg">
                        <i class="ri-restaurant-fill"></i>
                    </div>
                    <span class="text-2xl font-bold tracking-tight">Kurwa Food</span>
                </div>
                
                <h1 class="text-4xl font-extrabold mb-4 leading-tight">Manage your restaurant with ease.</h1>
                <p class="text-white text-opacity-90 text-lg">Join the fastest growing food network and reach thousands of hungry customers today.</p>
            </div>
            
            <div class="relative z-10 mt-auto">
                <div class="flex -space-x-3 mb-4">
                    <img class="w-10 h-10 rounded-full border-2 border-white object-cover" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100&q=80" alt="">
                    <img class="w-10 h-10 rounded-full border-2 border-white object-cover" src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=100&q=80" alt="">
                    <img class="w-10 h-10 rounded-full border-2 border-white object-cover" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="">
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-white text-rest-primary flex items-center justify-center text-xs font-bold">+5k</div>
                </div>
                <p class="text-sm font-medium">Trusted by 5,000+ restaurant partners.</p>
            </div>
        </div>

        <!-- Right Side -->
        <div class="w-full md:w-1/2 p-8 md:p-12 lg:p-16 flex flex-col justify-center">
            <div class="mb-10 text-center md:text-left">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Welcome back</h2>
                <p class="text-gray-500">Please enter your details to sign in.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-6 text-sm flex items-center gap-2 font-medium">
                    <i class="ri-error-warning-line text-lg"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-5">
                <div class="relative">
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Email Address</label>
                    <div class="relative">
                        <i class="ri-mail-line absolute left-4 top-3.5 text-gray-400 text-lg"></i>
                        <input type="email" name="email" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass" placeholder="restaurant@example.com" required>
                    </div>
                </div>

                <div class="relative">
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Password</label>
                    <div class="relative">
                        <i class="ri-lock-password-line absolute left-4 top-3.5 text-gray-400 text-lg"></i>
                        <input type="password" name="password" id="password" class="w-full pl-11 pr-12 py-3 rounded-xl input-glass" placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-3.5 text-gray-400 hover:text-rest-primary transition-colors text-lg">
                            <i class="ri-eye-off-line" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-2 mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 rounded text-rest-primary focus:ring-rest-primary border-gray-300">
                        <span class="text-sm text-gray-600 font-medium">Remember me</span>
                    </label>
                    <a href="#" class="text-sm font-bold text-rest-primary hover:text-orange-600 transition-colors">Forgot password?</a>
                </div>

                <button type="submit" class="w-full py-4 text-white rounded-xl font-bold text-lg btn-rest flex justify-center items-center gap-2">
                    Sign In <i class="ri-arrow-right-line font-bold"></i>
                </button>
            </form>

            <p class="text-center text-gray-500 mt-8 font-medium">
                Don't have a restaurant account? 
                <a href="signup.php" class="text-rest-secondary font-bold hover:underline">Sign up now</a>
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
