<?php
include '../../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$error = "";

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  if (empty($email) || empty($password)) {
    $error = "Please fill in all fields.";
  } else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();

      if ($user['verified'] == 0) {
        $error = "Your account is not verified. Please verify your email first.";
      } elseif (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        header("Location: user_dashboard.php");
        exit;
      } else {
        $error = "Invalid email or password.";
      }
    } else {
      $error = "No account found with this email.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Login - Kurwa System</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Google Font: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- External CSS -->
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#E8EAF6] font-[Poppins]">

  <!-- Desktop Layout -->
  <div class="hidden md:flex bg-white shadow-lg rounded-2xl overflow-hidden max-w-4xl w-full">
    <!-- Left Section (Form) -->
    <div class="p-8 md:p-10 w-[55%] flex flex-col justify-center">
      <h2 class="text-3xl font-bold mb-1">Sign up to processed</h2>
      <p class="text-gray-600 text-sm mb-6">Welcome back! Please log in.</p>

      <?php if (!empty($error)): ?>
        <div class="text-red-500 text-sm mb-4"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" class="flex flex-col gap-4">
        <input type="email" name="email" placeholder="Email address"
          class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#4C5BFF]" required />

        <div class="relative">
          <input type="password" name="password" placeholder="Password"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#4C5BFF] w-full pr-10" required />
          <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-3.5 text-gray-500">
            <i data-lucide="eye" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="flex justify-between items-center text-sm">
          <label class="flex items-center gap-2">
            <input type="checkbox" class="w-4 h-4" /> Remember me
          </label>
          <a href="#" class="text-[#2F3CFF] font-medium hover:underline">Forgot password?</a>
        </div>

        <button type="submit"
          class="bg-[#2F3CFF] hover:bg-[#2430D8] text-white py-3 rounded-lg font-medium text-lg w-full transition">
          Sign In
        </button>

        <p class="text-center text-sm mt-3">
          Don’t have an account?
          <a href="signup.php" class="text-[#2F3CFF] font-medium hover:underline">Sign Up</a>
        </p>
      </form>
    </div>

    <!-- Right Section (Image) -->
    <div class="bg-[#2F3CFF] text-white flex flex-col justify-center items-center p-10 w-[45%]">
      <img src="../../assets/images/login-side-img.jpg" alt="Hospital Support" class="rounded-lg mb-6 shadow-md" />
      <div class="text-center">
        <h2 class="text-[20px] font-bold mb-2">Make Your Hospital Life Easier.</h2>
        <p class="text-[13px] opacity-90 leading-relaxed">
          Nepal’s first online platform for on demand caretaker and hospital support services.
        </p>
      </div>
    </div>
  </div>

  <!-- Mobile Layout -->
  <div class="md:hidden w-full max-w-md bg-white shadow-lg rounded-2xl overflow-hidden">
    <div class="bg-[#2F3CFF] text-white text-center pt-10 pb-16 px-6 rounded-b-[80px]">
      <h2 class="text-[20px] font-bold mb-2 leading-snug">Make Your Hospital Life Easier.</h2>
      <p class="text-sm opacity-90 leading-relaxed">
        Nepal’s first online platform for on demand caretaker and hospital support services.
      </p>
    </div>

    <div class="p-8">
      <h2 class="text-2xl font-bold text-center mb-2">Sign up to processed</h2>
      <p class="text-gray-600 text-sm text-center mb-6">Welcome back! Please log in.</p>

      <?php if (!empty($error)): ?>
        <div class="text-red-500 text-sm mb-4 text-center"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" class="flex flex-col gap-4">
        <input type="email" name="email" placeholder="Email address"
          class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#4C5BFF]" required />

        <div class="relative">
          <input type="password" name="password" placeholder="Password"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#4C5BFF] w-full pr-10" required />
          <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-3.5 text-gray-500">
            <i data-lucide="eye" class="w-5 h-5"></i>
          </button>
        </div>

        <div class="flex justify-between items-center text-sm">
          <label class="flex items-center gap-2">
            <input type="checkbox" class="w-4 h-4" /> Remember me
          </label>
          <a href="#" class="text-[#2F3CFF] font-medium hover:underline">Forgot password?</a>
        </div>

        <button type="submit"
          class="bg-[#2F3CFF] hover:bg-[#2430D8] text-white py-3 rounded-lg font-medium text-lg w-full transition">
          Sign In
        </button>

        <p class="text-center text-sm mt-3">
          Don’t have an account?
          <a href="signup.php" class="text-[#2F3CFF] font-medium hover:underline">Sign Up</a>
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
