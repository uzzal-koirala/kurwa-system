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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pharmacy Partner Login - Kurwa System</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- External CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#f0fdf4] font-[Poppins]">

  <!-- Desktop Layout -->
  <div class="hidden md:flex bg-white shadow-lg rounded-2xl overflow-hidden max-w-4xl w-full">
    <!-- Left Section (Form) -->
    <div class="p-8 md:p-10 w-[55%] flex flex-col justify-center">
      <h2 class="text-3xl font-bold mb-1">Sign in to Pharmacy</h2>
      <p class="text-gray-600 text-sm mb-6">Welcome back! Manage your store inventory and orders.</p>

      <?php if (!empty($error)): ?>
        <div class="text-red-500 text-sm mb-4 bg-red-50 p-3 flex rounded-lg items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5"></i> <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="flex flex-col gap-4">
        <input type="email" name="email" placeholder="Email address"
          class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#059669]" required />

        <div class="relative">
          <input type="password" name="password" placeholder="Password"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#059669] w-full pr-10" required />
          <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-3.5 text-gray-500">
            <i data-lucide="eye" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="flex justify-between items-center text-sm">
          <label class="flex items-center gap-2">
            <input type="checkbox" class="w-4 h-4 accent-[#059669]" /> Remember me
          </label>
          <a href="#" class="text-[#059669] font-medium hover:underline">Forgot password?</a>
        </div>

        <button type="submit"
          class="bg-[#059669] hover:bg-[#047857] text-white py-3 rounded-lg font-medium text-lg w-full transition">
          Sign In
        </button>

        <p class="text-center text-sm mt-3">
          <a href="../../login.php" class="text-[#64748b] font-medium hover:underline flex items-center justify-center gap-1">
             <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Main Portal
          </a>
        </p>
      </form>
    </div>

    <!-- Right Section (Image) -->
    <div class="bg-[#059669] text-white flex flex-col justify-center items-center p-10 w-[45%]">
      <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=400&q=80" alt="Pharmacy Operations" class="rounded-lg mb-6 shadow-md object-cover h-[250px] w-[300px]" />
      <div class="text-center">
        <h2 class="text-[20px] font-bold mb-2">Empower Your Medical Storefront.</h2>
        <p class="text-[13px] opacity-90 leading-relaxed">
          Join the largest network of verified healthcare and medicine providers in Nepal.
        </p>
      </div>
    </div>
  </div>

  <!-- Mobile Layout -->
  <div class="md:hidden w-full max-w-md bg-white shadow-lg rounded-2xl overflow-hidden relative">
    <div class="bg-[#059669] text-white text-center pt-8 pb-16 px-6 rounded-b-[60px]">
      <h2 class="text-[20px] font-bold mb-2 leading-snug">Empower Your Medical Storefront.</h2>
      <p class="text-sm opacity-90 leading-relaxed">
        Join the largest network of verified healthcare and medicine providers in Nepal.
      </p>
    </div>

    <div class="p-8 -mt-8 relative z-10 bg-white rounded-t-[30px] border-t border-gray-100 shadow-[0_-15px_20px_-15px_rgba(0,0,0,0.1)]">
      <h2 class="text-2xl font-bold text-center mb-2">Sign in to Pharmacy</h2>
      <p class="text-gray-600 text-sm text-center mb-6">Welcome back! Manage your store.</p>

      <?php if (!empty($error)): ?>
        <div class="text-red-500 text-sm mb-4 text-center bg-red-50 p-3 rounded-lg flex items-center justify-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5"></i> <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="flex flex-col gap-4">
        <input type="email" name="email" placeholder="Email address"
          class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#059669]" required />

        <div class="relative">
          <input type="password" name="password" placeholder="Password"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#059669] w-full pr-10" required />
          <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-3.5 text-gray-500">
            <i data-lucide="eye" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="flex justify-between items-center text-sm">
          <label class="flex items-center gap-2">
            <input type="checkbox" class="w-4 h-4 accent-[#059669]" /> Remember me
          </label>
          <a href="#" class="text-[#059669] font-medium hover:underline">Forgot?</a>
        </div>

        <button type="submit"
          class="bg-[#059669] hover:bg-[#047857] text-white py-3 rounded-lg font-medium text-lg w-full transition">
          Sign In
        </button>

        <p class="text-center text-sm mt-3">
          <a href="../../login.php" class="text-[#64748b] font-medium hover:underline flex items-center justify-center gap-1">
             <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Main Portal
          </a>
        </p>
      </form>
    </div>
  </div>

  <script>
    lucide.createIcons();

    function togglePassword(btn) {
      const input = btn.previousElementSibling;
      const icon = btn.querySelector("i");
      if (input.type === "password") {
        input.type = "text";
        icon.setAttribute("data-lucide", "eye-off");
      } else {
        input.type = "password";
        icon.setAttribute("data-lucide", "eye");
      }
      lucide.createIcons();
    }
  </script>
</body>
</html>
