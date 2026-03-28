<?php
include '../../includes/core/config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $identifier = trim($_POST['identifier']);
  $password = trim($_POST['password']);

  if (empty($identifier) || empty($password)) {
    $error = "Please fill in all fields.";
  } else {
    $stmt = $conn->prepare("SELECT * FROM caretakers WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $caretaker = $result->fetch_assoc();
      if (password_verify($password, $caretaker['password'])) {
        $_SESSION['user_id'] = $caretaker['id'];
        $_SESSION['full_name'] = $caretaker['full_name'];
        $_SESSION['email'] = $caretaker['email'];
        $_SESSION['role'] = 'caretaker';
        $_SESSION['phone'] = $caretaker['phone'];

        // Enforce OTP Check
        if ($caretaker['verified'] == 0) {
            header("Location: verify_otp.php?email=" . urlencode($caretaker['email']));
            exit;
        }

        // Onboarding Check
        if ($caretaker['onboarding_completed'] == 0) {
             header("Location: onboarding.php");
             exit;
        }

        // Approval Check
        if ($caretaker['status'] == 'pending') {
            header("Location: pending_approval.php");
            exit;
        }

        if ($caretaker['status'] == 'disapproved') {
            header("Location: pending_approval.php?status=failed");
            exit;
        }

        header("Location: dashboard.php");
        exit;
      } else {
        $error = "Invalid email/phone or password.";
      }
    } else {
      $error = "No account found with this email or phone number.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Caretaker Login - Kurwa System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#f8fafc] font-[Poppins] relative overflow-hidden">
  <!-- Background Decorative Elements -->
  <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-200/30 rounded-full blur-[120px] pointer-events-none"></div>
  <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-200/30 rounded-full blur-[120px] pointer-events-none"></div>

  <div class="bg-white shadow-2xl rounded-3xl flex flex-col md:flex-row overflow-hidden max-w-4xl w-full translate-y-[-20px] md:translate-y-0 relative z-10">
    
    <!-- Left Section (Form) -->
    <div class="p-8 md:p-10 md:w-[55%] flex flex-col justify-center bg-white">
      <div class="mb-6">
          <h2 class="text-4xl font-extrabold text-[#1e293b] mb-2">Welcome Back</h2>
          <p class="text-gray-500">Log in to manage your caretaker dashboard.</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="bg-red-50 text-red-500 p-4 rounded-xl text-sm mb-6 flex items-center gap-3">
          <i class="ri-error-warning-line text-lg"></i>
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="flex flex-col gap-4">
        <div>
            <div class="relative">
                <i class="ri-user-line absolute left-4 top-3 text-gray-400"></i>
                <input type="text" name="identifier" placeholder="Email or Phone Number"
                  class="w-full bg-gray-50 border border-gray-100 rounded-xl py-3 px-4 pl-12 focus:ring-1 focus:ring-[#2F3CFF] focus:bg-white transition-all outline-none placeholder:text-gray-400" required />
            </div>
        </div>

        <div>
            <div class="relative">
                <i class="ri-lock-password-line absolute left-4 top-3 text-gray-400"></i>
                <input type="password" name="password" id="password" placeholder="••••••••"
                  class="w-full bg-gray-50 border border-gray-100 rounded-xl py-3 px-4 pl-12 pr-12 focus:ring-1 focus:ring-[#2F3CFF] focus:bg-white transition-all outline-none" required />
                <button type="button" onclick="togglePassword('password', 'eye-icon')" class="absolute right-4 top-3 text-gray-400">
                    <i id="eye-icon" class="ri-eye-line"></i>
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2 mb-1">
          <input type="checkbox" id="remember" class="w-4 h-4 rounded border-gray-300 text-[#2F3CFF] focus:ring-[#2F3CFF]">
          <label for="remember" class="text-sm text-gray-600">Remember me for 30 days</label>
        </div>

        <button type="submit"
          class="bg-[#2F3CFF] hover:bg-[#1A237E] text-white py-3 rounded-xl font-bold text-lg w-full transition-all transform hover:scale-[1.01] active:scale-[0.99] shadow-xl shadow-blue-100">
          Sign In
        </button>

        <p class="text-center text-sm mt-2 text-gray-600">
          Don’t have an account?
          <a href="signup.php" class="text-[#2F3CFF] font-bold hover:underline">Sign Up</a>
        </p>
      </form>
    </div>

    <!-- Right Section (Decorative) -->
    <div class="hidden md:flex bg-gradient-to-br from-[#2F3CFF]/90 to-[#1A237E] backdrop-blur-2xl text-white flex-col justify-center items-center p-14 w-[45%] relative overflow-hidden border-l border-white/20 shadow-[-20px_0_50px_rgba(47,60,255,0.1)]">
      <!-- Glow effects -->
      <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/20 rounded-full blur-3xl text-white"></div>
      <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl"></div>
      
      <div class="z-10 text-center">
          <div class="w-24 h-24 bg-white/20 backdrop-blur-xl rounded-3xl flex items-center justify-center mb-8 mx-auto border border-white/30 shadow-2xl">
              <i class="ri-user-heart-fill text-5xl text-white"></i>
          </div>
          <h2 class="text-3xl font-bold mb-4 text-white">Empowering Care</h2>
          <p class="text-sm text-white/90 leading-relaxed max-w-[260px] mx-auto font-medium">
              Join the largest network of verified healthcare professionals in Nepal.
          </p>
      </div>

    </div>
  </div>

  <script>
    function togglePassword(inputId, iconId) {
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("ri-eye-line", "ri-eye-off-line");
      } else {
        input.type = "password";
        icon.classList.replace("ri-eye-off-line", "ri-eye-line");
      }
    }
  </script>
</body>
</html>
