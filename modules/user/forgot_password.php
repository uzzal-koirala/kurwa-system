<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/sms_helper.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$message = "";
$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $phone = trim($_POST['phone']);

  if (empty($phone)) {
    $message = "<p class='text-red-600 text-sm text-center mb-3'>Please enter your phone number.</p>";
    $error = true;
  } else {
    // Check if user exists by phone
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $user_data = $result->fetch_assoc();
      
      // Generate OTP for password reset
      $otp = rand(100000, 999999);
      $update = $conn->prepare("UPDATE users SET otp = ? WHERE phone = ?");
      $update->bind_param("ss", $otp, $phone);
      $update->execute();

      // Send OTP via SMS
      $sms_message = "Dear User, your Kurwa System verification code (Password Reset) is: $otp. Please do not share this code with anyone for security reasons.";
      $sms_sent = send_sms($phone, $sms_message);

      if ($sms_sent['success'] !== false) {
          // Store phone in session for verification step
          $_SESSION['reset_phone'] = $phone;
          header("Location: verify_reset_otp.php");
          exit;
      } else {
          $message = "<p class='text-red-600 text-sm text-center mb-3'>Failed to send SMS. Please try again later.</p>";
          $error = true;
      }
    } else {
      $message = "<p class='text-red-600 text-sm text-center mb-3'>No account found with that phone number.</p>";
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
  <style>
    .floating-icon {
        animation: floating 3s ease-in-out infinite;
    }
    @keyframes floating {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    .glass-icon {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .input-focus:focus-within {
        border-color: #2F3CFF;
        box-shadow: 0 0 0 4px rgba(47, 60, 255, 0.05);
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#F0F2F5] font-[Poppins]">

  <!-- Desktop Layout -->
  <div class="hidden md:flex bg-white shadow-2xl rounded-[1.5rem] overflow-hidden max-w-4xl w-full border border-gray-100">
    <!-- Left Section (Form) -->
    <div class="p-8 md:p-12 w-[55%] flex flex-col justify-center text-center md:text-left bg-white">
      <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-1">Forgot your password?</h2>
      <p class="text-gray-500 text-[15px] font-medium tracking-tight leading-relaxed mb-8">Enter your registered phone number to reset your password.</p>

      <?php if (!empty($message)) echo $message; ?>

      <form method="POST" class="flex flex-col gap-5">
        <div class="relative group input-focus border-2 border-gray-100 rounded-xl bg-gray-50/30 transition-all">
          <input type="text" name="phone" placeholder="Phone Number (e.g. 98XXXXXXXX)"
            class="w-full bg-transparent py-3.5 focus:outline-none pl-12 pr-4 text-gray-800 text-[14px] font-medium placeholder:text-gray-400" required />
          <i data-lucide="phone" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
        </div>

        <button type="submit"
          class="bg-[#2F3CFF] hover:bg-[#2430D8] text-white py-3.5 rounded-xl font-extrabold text-lg w-full transition-all shadow-lg shadow-blue-100 transform active:scale-[0.98]">
          Send Reset Code
        </button>

        <p class="text-center text-sm mt-3 font-medium text-gray-600">
          Remembered your password?
          <a href="login.php" class="text-[#2F3CFF] font-bold hover:underline">Login here</a>
        </p>
      </form>
    </div>

    <!-- Right Blue Section -->
    <div class="bg-[#2F3CFF] text-white flex flex-col justify-center items-center p-10 w-[45%] relative overflow-hidden">
      <!-- Glow effects -->
      <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl"></div>

      <!-- Icon Composition -->
      <div class="relative z-10 text-center flex flex-col items-center">
          <div class="relative mb-8 floating-icon">
              <div class="w-24 h-24 glass-icon rounded-[2.5rem] flex items-center justify-center shadow-2xl relative">
                  <i data-lucide="life-buoy" class="w-12 h-12 text-white/90"></i>
              </div>
              <div class="absolute -top-2 -right-2 w-10 h-10 bg-white text-blue-600 rounded-2xl shadow-lg flex items-center justify-center transform rotate-12">
                  <i data-lucide="shield-question" class="w-5 h-5"></i>
              </div>
              <div class="absolute -bottom-2 -left-2 w-8 h-8 bg-blue-400/50 backdrop-blur-sm text-white rounded-xl flex items-center justify-center transform -rotate-12">
                  <i data-lucide="key" class="w-4 h-4"></i>
              </div>
          </div>
          <h2 class="text-2xl font-bold mb-3 tracking-tight">Need a Hand?</h2>
          <p class="text-white/90 text-[14px] leading-relaxed max-w-[320px] mx-auto font-medium opacity-90 tracking-tight">
            Don't worry, even the best of us forget. Let's get you back into your account safely.
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
      <p class="text-gray-600 text-sm text-center mb-6">Enter your phone number to reset your password.</p>

      <?php if (!empty($message)) echo $message; ?>

      <form method="POST" class="flex flex-col gap-4">
        <div class="relative">
          <input type="text" name="phone" placeholder="Phone Number"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-[#4C5BFF] w-full pl-10" required />
          <i data-lucide="phone" class="w-5 h-5 absolute left-3 top-3.5 text-gray-500"></i>
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
