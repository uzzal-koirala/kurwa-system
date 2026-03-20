<?php
require_once '../../includes/core/config.php';

if (isset($_SESSION['delivery_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $vehicle = $_POST['vehicle_type'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // In a real app, we would insert into the database here
        $success = "Application submitted! We will contact you soon.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join as a Rider | Kurwa</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/delivery_login.css">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden py-10">
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-green-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 translate-x-1/3 translate-y-1/3"></div>

    <div class="max-w-5xl w-full mx-4 flex bg-white rounded-3xl overflow-hidden shadow-2xl relative z-10">
        <!-- Left Side (Same as Login) -->
        <div class="hidden md:flex flex-col md:w-[35%] bg-gradient-delivery p-8 text-white justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-delivery-primary font-bold text-xl shadow-lg">
                        <i class="ri-moped-fill"></i>
                    </div>
                    <span class="text-2xl font-bold tracking-tight">Kurwa Rider</span>
                </div>
                
                <h1 class="text-3xl font-bold mb-4 leading-tight">Be your own<br>Boss today.</h1>
                <p class="text-white text-opacity-90 text-sm">Join Nepal's most reliable delivery network and start earning on your own schedule.</p>
                
                <div class="mt-10 space-y-4">
                    <div class="flex items-center gap-3 p-3 bg-white bg-opacity-10 rounded-xl">
                        <i class="ri-shield-check-fill text-xl"></i>
                        <span class="text-xs font-semibold">Insurance Coverage</span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-white bg-opacity-10 rounded-xl">
                        <i class="ri-flashlight-fill text-xl"></i>
                        <span class="text-xs font-semibold">Instant Onboarding</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Signup Form -->
        <div class="w-full md:w-[65%] p-8 md:p-10 flex flex-col justify-center">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Apply to Deliver</h2>
                <p class="text-sm text-gray-500 font-medium">Fill in your details to start your journey.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-6 text-sm flex items-center gap-2 font-medium">
                    <i class="ri-error-warning-line text-lg"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 text-sm flex items-center gap-2 font-bold border border-green-100">
                    <i class="ri-checkbox-circle-line text-lg"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-700 ml-1">Full Name</label>
                    <div class="relative">
                        <i class="ri-user-line absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" name="full_name" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass text-sm" placeholder="John Doe" required>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-700 ml-1">Email Address</label>
                    <div class="relative">
                        <i class="ri-mail-line absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="email" name="email" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass text-sm" placeholder="rider@example.com" required>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-700 ml-1">Phone Number</label>
                    <div class="relative">
                        <i class="ri-phone-line absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="tel" name="phone" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass text-sm" placeholder="98XXXXXXXX" required>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-700 ml-1">Vehicle Type</label>
                    <div class="relative">
                        <i class="ri-motorbike-line absolute left-4 top-3.5 text-gray-400"></i>
                        <select name="vehicle_type" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass text-sm appearance-none" required>
                            <option value="bike">Motorcycle / Scooty</option>
                            <option value="bicycle">Bicycle</option>
                            <option value="electric">Electric Scooter</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-700 ml-1">Password</label>
                    <div class="relative">
                        <i class="ri-lock-line absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="password" name="password" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass text-sm" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-700 ml-1">Confirm Password</label>
                    <div class="relative">
                        <i class="ri-lock-check-line absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="password" name="confirm_password" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass text-sm" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="md:col-span-2 pt-2">
                    <button type="submit" class="w-full py-3.5 text-white rounded-xl font-bold text-lg btn-delivery flex justify-center items-center gap-2">
                        Create Rider Account <i class="ri-arrow-right-line font-bold"></i>
                    </button>
                </div>
            </form>

            <p class="text-center text-gray-500 mt-8 font-medium text-sm">
                Already part of the team? 
                <a href="login.php" class="text-delivery-primary font-bold hover:underline">Sign in instead</a>
            </p>
        </div>
    </div>
</body>
</html>
