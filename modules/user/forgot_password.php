<?php
include '../../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$message = "";
$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email']);

  if (empty($email)) {
    $message = "<p class='text-red-600 text-sm text-center mb-3'>Please enter your email.</p>";
    $error = true;
  } else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      // Generate OTP for password reset
      $otp = rand(100000, 999999);
      $update = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
      $update->bind_param("ss", $otp, $email);
      $update->execute();

      // Redirect to OTP verification page
      header("Location: verify_reset_otp.php?email=$email");
      exit;
    } else {
      $message = "<p class='text-red-600 text-sm text-center mb-3'>No account found with that email.</p>";
      $error = true;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password - Kurwa System</title>

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
      <h2 class="text-3xl font-bold mb-1">Forgot your password?</h2>
      <p class="text-gray-600 text-sm mb-6">Enter your email to reset your password.</p>

      <?php if (!empty($message)) echo $message; ?>

      <form method="POST" class="flex flex-col gap-4">
        <div class="relative">
          <input type="email" name="email" placeholder="Email address"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#4C5BFF] w-full" required />
          <i data-lucide="mail" class="w-5 h-5 absolute right-3 top-3.5 text-gray-500"></i>
        </div>

        <button type="submit"
          class="bg-[#2F3CFF] hover:bg-[#2430D8] text-white py-3 rounded-lg font-medium text-lg w-full transition">
          Send Reset Code
        </button>

        <p class="text-center text-sm mt-3">
          Remembered your password?
          <a href="login.php" class="text-[#2F3CFF] font-medium hover:underline">Login here</a>
        </p>
      </form>
    </div>

    <!-- Right Blue Section -->
    <div class="bg-[#2F3CFF] text-white flex flex-col justify-center items-center p-10 w-[45%]">
      <img src="../../assets/images/login-side-img.png" alt="Hospital Support" class="rounded-lg mb-6 shadow-md" />
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
      <h2 class="text-2xl font-bold text-center mb-2">Forgot your password?</h2>
      <p class="text-gray-600 text-sm text-center mb-6">Enter your email to reset your password.</p>

      <?php if (!empty($message)) echo $message; ?>

      <form method="POST" class="flex flex-col gap-4">
        <div class="relative">
          <input type="email" name="email" placeholder="Email address"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#4C5BFF] w-full" required />
          <i data-lucide="mail" class="w-5 h-5 absolute right-3 top-3.5 text-gray-500"></i>
        </div>

        <button type="submit"
          class="bg-[#2F3CFF] hover:bg-[#2430D8] text-white py-3 rounded-lg font-medium text-lg w-full transition">
          Send Reset Code
        </button>

        <p class="text-center text-sm mt-3">
          Remembered your password?
          <a href="login.php" class="text-[#2F3CFF] font-medium hover:underline">Login here</a>
        </p>
      </form>
    </div>
  </div>

  <script>lucide.createIcons();</script>
</body>
</html>
