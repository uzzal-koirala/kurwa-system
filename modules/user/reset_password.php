<?php
require_once '../../includes/core/config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$phone = $_SESSION['reset_phone'] ?? '';
$verified = $_SESSION['otp_verified'] ?? false;

if (empty($phone) || !$verified) {
    header("Location: forgot_password.php");
    exit;
}

$message = "";
$error = false;
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $error = true;
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $error = true;
    } else {
        // Hash and update the password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL WHERE phone = ?");
        $stmt->bind_param("ss", $hashed_password, $phone);
        
        if ($stmt->execute()) {
            $success = true;
            // Clear session data
            unset($_SESSION['reset_phone']);
            unset($_SESSION['otp_verified']);
            
            // Redirect after a short delay
            header("refresh:2;url=login.php");
        } else {
            $message = "An error occurred while resetting your password. Please try again.";
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
  <title>Reset Password - Kurwa System</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- Google Font: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  
  <style>
    .strength-bar {
      transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.4s ease;
    }
    .input-focus:focus-within {
        border-color: #2F3CFF;
        box-shadow: 0 0 0 4px rgba(47, 60, 255, 0.05);
    }
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
    .compact-container {
        max-height: 420px;
    }
    @media (max-width: 768px) {
        .compact-container {
            max-height: none;
        }
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#F0F2F5] font-[Poppins]">

  <!-- Main Container -->
  <div class="hidden md:flex bg-white shadow-2xl rounded-[1.5rem] overflow-hidden max-w-3xl w-full border border-gray-100 compact-container">
    
    <!-- Left Section (Form) -->
    <div class="p-8 w-[55%] flex flex-col justify-center relative bg-white">
      <div class="mb-5">
          <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-3">
              <i data-lucide="shield-check" class="w-5 h-5"></i>
          </div>
          <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Set New Password</h2>
          <p class="text-gray-500 mt-2 text-[15px] font-medium tracking-tight leading-relaxed">Choose a strong password for your account.</p>
      </div>

      <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-2 rounded-xl text-xs mb-4 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
            <p class="font-bold">Success! Redirecting...</p>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-2 rounded-xl text-xs mb-4 flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
            <p><?php echo $message; ?></p>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-3.5" <?php if ($success) echo 'style="display:none;"'; ?>>
          <!-- Password Input -->
          <div class="relative group input-focus border-2 border-gray-100 rounded-xl bg-gray-50/30 transition-all">
              <input type="password" name="new_password" id="new_password_desktop" placeholder="New Password"
                  class="w-full bg-transparent py-4 focus:outline-none pl-12 pr-12 text-gray-800 text-[15px] font-medium placeholder:text-gray-400" required minlength="6" />
              <i data-lucide="key" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
              <button type="button" onclick="togglePass('new_password_desktop', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <i data-lucide="eye" class="w-5 h-5"></i>
              </button>
          </div>
          
          <!-- Strength Bar -->
          <div class="px-1 -mt-2">
              <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                  <div id="strength-bar-desktop" class="strength-bar h-full w-0 bg-gray-200"></div>
              </div>
          </div>

          <!-- Confirm Password -->
          <div class="relative group input-focus border-2 border-gray-100 rounded-xl bg-gray-50/30 transition-all">
              <input type="password" name="confirm_password" placeholder="Confirm Password"
                  class="w-full bg-transparent py-4 focus:outline-none pl-12 text-gray-800 text-[15px] font-medium placeholder:text-gray-400" required minlength="6" />
              <i data-lucide="lock" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
          </div>

          <button type="submit"
              class="w-full bg-[#2F3CFF] hover:bg-[#2430D8] text-white font-extrabold py-4 rounded-xl shadow-lg shadow-blue-100 transition-all flex items-center justify-center gap-2 mt-2 transform active:scale-[0.98] text-lg tracking-wide">
              Confirm Reset
          </button>

      </form>
    </div>
    <div class="bg-[#2F3CFF] text-white flex flex-col justify-center items-center p-8 w-[45%] relative overflow-hidden">
      <!-- Icon Composition -->
      <div class="relative z-10 text-center flex flex-col items-center">
          <div class="relative mb-6 floating-icon">
              <div class="w-20 h-20 glass-icon rounded-[2rem] flex items-center justify-center shadow-2xl relative">
                  <i data-lucide="fingerprint" class="w-10 h-10 text-white/80"></i>
              </div>
              <div class="absolute -top-2 -right-2 w-8 h-8 bg-white text-blue-600 rounded-xl shadow-lg flex items-center justify-center transform rotate-12">
                  <i data-lucide="shield-check" class="w-4 h-4"></i>
              </div>
              <div class="absolute -bottom-1 -left-1 w-6 h-6 bg-blue-400/50 backdrop-blur-sm text-white rounded-lg flex items-center justify-center">
                  <i data-lucide="lock" class="w-3 h-3"></i>
              </div>
          </div>
          <h2 class="text-2xl font-bold mb-2 tracking-tight text-white">Identity Secured.</h2>
          <p class="text-white/90 text-[13px] leading-snug max-w-[240px] mx-auto font-semibold tracking-tight">
              Your security is our priority. Please choose a strong, unique password.
          </p>
      </div>
    </div>
  </div>

  <!-- Mobile Layout -->
  <div class="md:hidden w-full max-w-sm bg-white shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
    <div class="bg-[#2F3CFF] text-white text-center pt-8 pb-12 px-6 rounded-b-[3rem] relative overflow-hidden">
      <h2 class="text-xl font-bold tracking-tight">Set New Password</h2>
      <p class="text-blue-100/60 text-[11px] font-light mt-1">Identity verified successfully.</p>
    </div>

    <div class="p-7 -mt-8 relative z-10 bg-white rounded-t-[2.5rem]">
      <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-xl text-xs mb-5 flex items-center justify-center italic">
            Redirecting to login...
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4" <?php if ($success) echo 'style="display:none;"'; ?>>
          <div class="relative group input-focus border-2 border-gray-100 rounded-xl">
              <input type="password" name="new_password" id="new_password_mobile" placeholder="New Password"
                  class="w-full bg-transparent py-3 focus:outline-none pl-11 pr-11 text-[13px]" required minlength="6" />
              <i data-lucide="key" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
          </div>

          <div class="relative group input-focus border-2 border-gray-100 rounded-xl transition-all">
              <input type="password" name="confirm_password" placeholder="Confirm Password"
                  class="w-full bg-transparent py-3 focus:outline-none pl-11 text-[13px]" required minlength="6" />
              <i data-lucide="lock" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
          </div>

          <button type="submit"
              class="w-full bg-[#2F3CFF] hover:bg-[#2430D8] text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-100 transition-all flex items-center justify-center gap-2">
              Update Password
          </button>
      </form>
    </div>
  </div>

  <script>
    lucide.createIcons();

    function togglePass(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }

    const initStrength = (id, barId) => {
        const input = document.getElementById(id);
        const bar = document.getElementById(barId);
        if (!input || !bar) return;

        input.addEventListener('input', () => {
            const val = input.value;
            let score = 0;
            if (val.length >= 6) score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;

            const widths = ['0%', '20%', '40%', '70%', '100%'];
            const colors = ['#E5E7EB', '#EF4444', '#F59E0B', '#3B82F6', '#10B981'];

            bar.style.width = widths[score];
            bar.style.backgroundColor = colors[score];
        });
    };

    initStrength('new_password_desktop', 'strength-bar-desktop');
  </script>
</body>
</html>
