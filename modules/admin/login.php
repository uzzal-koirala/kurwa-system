<?php
require_once '../../includes/core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Super Admin Login - Kurwa System</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- External CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#f8fafc] font-[Poppins]">

  <!-- Desktop Layout -->
  <div class="hidden md:flex bg-white shadow-2xl rounded-2xl overflow-hidden max-w-4xl w-full border border-gray-100">
    <!-- Left Section (Form) -->
    <div class="p-8 md:p-10 w-[55%] flex flex-col justify-center">
      <div class="flex items-center gap-2 mb-1">
          <i data-lucide="shield-check" class="w-8 h-8 text-[#0f172a]"></i>
          <h2 class="text-3xl font-bold text-[#0f172a]">Super Admin</h2>
      </div>
      <p class="text-gray-500 text-sm mb-6 flex items-center gap-1 font-medium"><i data-lucide="lock" class="w-3 h-3"></i> Authorized personnel only. System entry.</p>

      <?php if (!empty($error)): ?>
        <div class="text-red-500 text-sm mb-4 bg-red-50 p-3 flex rounded-lg items-center gap-2 border border-red-100">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i> <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="flex flex-col gap-4">
        <div class="relative">
            <input type="email" name="email" placeholder="Admin Email"
              class="border border-gray-300 rounded-lg p-3 w-full focus:outline-none focus:border-[#0f172a] focus:ring-1 focus:ring-[#0f172a] pl-10 transition" required autofocus />
            <i data-lucide="mail" class="w-5 h-5 absolute left-3 top-3.5 text-gray-400"></i>
        </div>

        <div class="relative">
          <input type="password" name="password" placeholder="Secret Key"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#0f172a] focus:ring-1 focus:ring-[#0f172a] w-full pl-10 pr-10 transition" required />
          <i data-lucide="key" class="w-5 h-5 absolute left-3 top-3.5 text-gray-400"></i>
          
          <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600">
            <i data-lucide="eye" class="w-5 h-5"></i>
          </button>
        </div>

        <button type="submit"
          class="bg-[#0f172a] hover:bg-[#1e293b] text-white py-3 mt-2 rounded-lg font-semibold text-lg w-full transition flex items-center justify-center gap-2 shadow-lg shadow-[#0f172a]/20">
          Authenticate Access <i data-lucide="arrow-right" class="w-5 h-5"></i>
        </button>

        <p class="text-center text-sm mt-4">
          <a href="../user/login.php" class="text-gray-500 font-medium hover:text-[#0f172a] transition flex items-center justify-center gap-1">
             <i data-lucide="log-out" class="w-4 h-4"></i> Return to User Portal
          </a>
        </p>
      </form>
    </div>

    <!-- Right Section (Image) -->
    <div class="bg-[#0f172a] text-white flex flex-col justify-center items-center p-10 w-[45%] relative overflow-hidden">
      <!-- Decorative background elements -->
      <div class="absolute inset-0 bg-gradient-to-br from-[#1e293b] to-[#0f172a]"></div>
      <div class="absolute -top-24 -right-24 w-64 h-64 bg-[#334155] rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
      
      <div class="relative z-10 text-center flex flex-col items-center">
        <div class="w-20 h-20 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mb-6 shadow-xl border border-white/20">
             <i data-lucide="server" class="w-10 h-10 text-blue-400"></i>
        </div>
        <h2 class="text-[24px] font-bold mb-3 tracking-wide">Command Center</h2>
        <p class="text-[14px] text-gray-300 leading-relaxed font-light">
          Monitor advanced system metrics, manage user access, oversee financial operations, and control platform workflows.
        </p>
      </div>
    </div>
  </div>

  <!-- Mobile Layout -->
  <div class="md:hidden w-full max-w-md bg-white shadow-2xl rounded-2xl overflow-hidden relative border border-gray-100 mx-4">
    <div class="bg-[#0f172a] text-white text-center pt-10 pb-16 px-6 rounded-b-[60px] relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-[#1e293b] to-[#0f172a]"></div>
      <div class="relative z-10 flex flex-col items-center">
          <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center mb-4 shadow-xl border border-white/20">
               <i data-lucide="server" class="w-8 h-8 text-blue-400"></i>
          </div>
          <h2 class="text-[20px] font-bold mb-2 tracking-wide leading-tight">System<br>Command Center</h2>
      </div>
    </div>

    <div class="p-8 -mt-8 relative z-10 bg-white rounded-t-[30px] border-t border-gray-100 shadow-[0_-15px_20px_-15px_rgba(0,0,0,0.1)]">
      <div class="flex items-center justify-center gap-2 mb-2">
          <i data-lucide="shield-check" class="w-6 h-6 text-[#0f172a]"></i>
          <h2 class="text-2xl font-bold text-center text-[#0f172a]">Super Admin</h2>
      </div>
      <p class="text-gray-500 text-sm text-center mb-6 flex items-center justify-center gap-1"><i data-lucide="lock" class="w-3 h-3"></i> Authorized personnel only.</p>

      <?php if (!empty($error)): ?>
        <div class="text-red-500 text-sm mb-4 text-center bg-red-50 p-3 rounded-lg flex items-center justify-center gap-2 border border-red-100">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i> <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="flex flex-col gap-4">
        <div class="relative">
            <input type="email" name="email" placeholder="Admin Email"
              class="border border-gray-300 rounded-lg p-3 w-full focus:outline-none focus:border-[#0f172a] focus:ring-1 focus:ring-[#0f172a] pl-10" required />
            <i data-lucide="mail" class="w-5 h-5 absolute left-3 top-3.5 text-gray-400"></i>
        </div>

        <div class="relative">
          <input type="password" name="password" placeholder="Secret Key"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#0f172a] focus:ring-1 focus:ring-[#0f172a] w-full pl-10 pr-10" required />
          <i data-lucide="key" class="w-5 h-5 absolute left-3 top-3.5 text-gray-400"></i>
          <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600">
            <i data-lucide="eye" class="w-5 h-5"></i>
          </button>
        </div>

        <button type="submit"
          class="bg-[#0f172a] hover:bg-[#1e293b] text-white py-3 mt-2 rounded-lg font-semibold text-lg w-full transition flex items-center justify-center gap-2 shadow-lg shadow-[#0f172a]/20">
          Authenticate <i data-lucide="arrow-right" class="w-5 h-5"></i>
        </button>

        <p class="text-center text-sm mt-4">
          <a href="../user/login.php" class="text-gray-500 font-medium hover:text-[#0f172a] transition flex items-center justify-center gap-1">
             <i data-lucide="log-out" class="w-4 h-4"></i> Return to User Portal
          </a>
        </p>
      </form>
    </div>
  </div>

  <script>
    lucide.createIcons();

    function togglePassword(btn) {
      const input = btn.previousElementSibling.previousElementSibling; // Because the <i icon> is between input and button
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
