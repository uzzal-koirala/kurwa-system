<?php
require_once '../../includes/core/config.php';

if (isset($_SESSION['restaurant_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($cpassword) || empty($address)) {
        $error = "All fields are required.";
    } elseif ($password !== $cpassword) {
        $error = "Passwords do not match.";
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM restaurants WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO restaurants (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $address, $hashed);
            if ($stmt->execute()) {
                $_SESSION['restaurant_id'] = $stmt->insert_id;
                $_SESSION['restaurant_name'] = $name;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Partner Signup | Kurwa Food</title>
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
<body class="bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden overflow-y-auto py-10">
    <div class="max-w-4xl w-full mx-4 flex bg-white rounded-3xl overflow-hidden shadow-2xl relative z-10">
        
        <!-- Left Side -->
        <div class="hidden md:flex flex-col md:w-5/12 bg-gradient-rest p-10 text-white justify-between relative overflow-hidden">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-rest-primary font-bold text-xl shadow-lg">
                        <i class="ri-store-3-fill"></i>
                    </div>
                    <span class="text-2xl font-bold tracking-tight">Kurwa Food</span>
                </div>
                
                <h1 class="text-3xl font-extrabold mb-4 leading-tight">Grow your business with us.</h1>
                <p class="text-white text-opacity-90 text-md">Partner with Kurwa Food to increase your orders and expand your reach instantly.</p>
            </div>
            
            <div class="relative z-10 mt-auto">
                <div class="bg-white bg-opacity-20 p-5 rounded-2xl backdrop-blur-md border border-white border-opacity-30">
                    <div class="flex items-center gap-2 mb-2 text-yellow-300 text-lg">
                        <i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i>
                    </div>
                    <p class="text-sm font-medium leading-relaxed italic">"Since joining Kurwa Food, our restaurant sales have increased by 45%. The platform is incredibly easy to use."</p>
                    <p class="text-xs font-bold mt-3 uppercase tracking-wider">— Royal Cafe</p>
                </div>
            </div>
        </div>

        <!-- Right Side -->
        <div class="w-full md:w-7/12 p-8 md:p-10 flex flex-col justify-center">
            <div class="mb-8 text-center md:text-left">
                <h2 class="text-2xl font-extrabold text-gray-900 mb-1">Create an Account</h2>
                <p class="text-gray-500 text-sm">Register your restaurant to start accepting orders.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-6 text-sm flex items-center gap-2 font-medium">
                    <i class="ri-error-warning-line text-lg"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST" class="space-y-4">
                <div class="relative">
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Restaurant Name</label>
                    <div class="relative">
                        <i class="ri-restaurant-line absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" name="name" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass" placeholder="Your Restaurant Name" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Email Address</label>
                        <div class="relative">
                            <i class="ri-mail-line absolute left-4 top-3.5 text-gray-400"></i>
                            <input type="email" name="email" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass" placeholder="restaurant@email.com" required>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Phone Number</label>
                        <div class="relative">
                            <i class="ri-phone-line absolute left-4 top-3.5 text-gray-400"></i>
                            <input type="text" name="phone" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass" placeholder="Phone Number" required>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Physical Address</label>
                    <div class="relative">
                        <i class="ri-map-pin-line absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" name="address" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass" placeholder="123 Main Street, City" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Password</label>
                        <div class="relative">
                            <i class="ri-lock-password-line absolute left-4 top-3.5 text-gray-400"></i>
                            <input type="password" name="password" id="password" class="w-full pl-11 pr-10 py-3 rounded-xl input-glass" placeholder="••••••••" required>
                            <button type="button" onclick="togglePassword('password', 'eye-icon-1')" class="absolute right-3 top-3.5 text-gray-400 hover:text-rest-primary">
                                <i class="ri-eye-off-line" id="eye-icon-1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Confirm Password</label>
                        <div class="relative">
                            <i class="ri-lock-password-line absolute left-4 top-3.5 text-gray-400"></i>
                            <input type="password" name="cpassword" id="cpassword" class="w-full pl-11 pr-10 py-3 rounded-xl input-glass" placeholder="••••••••" required>
                            <button type="button" onclick="togglePassword('cpassword', 'eye-icon-2')" class="absolute right-3 top-3.5 text-gray-400 hover:text-rest-primary">
                                <i class="ri-eye-off-line" id="eye-icon-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-4 text-white rounded-xl font-bold text-lg btn-rest flex justify-center items-center gap-2">
                        Register Restaurant <i class="ri-arrow-right-line"></i>
                    </button>
                </div>
            </form>

            <p class="text-center text-gray-500 mt-6 font-medium text-sm">
                Already registered? 
                <a href="login.php" class="text-rest-secondary font-bold hover:underline">Sign in here</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
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
