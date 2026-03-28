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
            
            // Redirect after a short delay or show success message
            header("refresh:3;url=login.php");
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
    body {
      background: #0f172a;
      background-image: 
        radial-gradient(at 0% 0%, rgba(30, 58, 138, 0.3) 0, transparent 50%), 
        radial-gradient(at 100% 100%, rgba(30, 58, 138, 0.3) 0, transparent 50%);
    }
    .glass {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .input-glass {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: white;
    }
    .input-glass:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    .strength-bar {
      transition: width 0.3s ease, background-color 0.3s ease;
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen px-4 font-[Poppins]">

  <!-- Background Decoration -->
  <div class="fixed inset-0 overflow-hidden -z-10 pointer-events-none">
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/10 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-600/10 rounded-full blur-[120px]"></div>
  </div>

  <div class="glass shadow-2xl rounded-[2.5rem] overflow-hidden max-w-md w-full p-8 md:p-12 relative">
    
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600/10 border border-blue-500/20 text-blue-400 rounded-3xl mb-6 shadow-glow">
            <i data-lucide="lock" class="w-10 h-10"></i>
        </div>
        <h2 class="text-3xl font-bold text-white tracking-tight">Set New Password</h2>
        <p class="text-slate-400 mt-3 text-sm leading-relaxed">Almost there! Choose a strong password for your account.</p>
    </div>

    <?php if ($success): ?>
      <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-6 py-5 rounded-2xl text-sm mb-8 flex flex-col items-center gap-3 text-center animate-pulse">
          <i data-lucide="check-circle" class="w-8 h-8"></i>
          <div>
              <p class="font-bold text-base">Security Updated!</p>
              <p class="opacity-80">Ready to go. Redirecting in 3s...</p>
          </div>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 px-5 py-4 rounded-2xl text-sm mb-8 flex items-center gap-3">
          <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
          <p><?php echo $message; ?></p>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6" id="resetForm" <?php if ($success) echo 'style="display:none;"'; ?>>
        <div class="space-y-5">
            <!-- New Password -->
            <div class="space-y-2">
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider ml-1">Secure Password</label>
                <div class="relative group">
                    <input type="password" name="new_password" id="new_password" placeholder="Create new password"
                        class="input-glass w-full rounded-2xl p-4 focus:outline-none transition-all pl-12 pr-12" required minlength="6" />
                    <i data-lucide="key-round" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors"></i>
                    <button type="button" onclick="togglePass('new_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                        <i data-lucide="eye" class="w-5 h-5"></i>
                    </button>
                </div>
                <!-- Strength Indicator -->
                <div class="px-1 pt-1">
                    <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                        <div id="strength-bar" class="strength-bar h-full w-0 bg-slate-600"></div>
                    </div>
                    <p id="strength-text" class="text-[10px] text-slate-500 mt-1.5 font-medium uppercase tracking-tight">Enter at least 6 characters</p>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider ml-1">Confirm Identity</label>
                <div class="relative group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Verify your password"
                        class="input-glass w-full rounded-2xl p-4 focus:outline-none transition-all pl-12 pr-12" required minlength="6" />
                    <i data-lucide="shield-check" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors"></i>
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-5 rounded-2xl shadow-xl shadow-blue-900/40 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-3">
            Secure My Account
            <i data-lucide="sparkles" class="w-5 h-5 text-blue-200"></i>
        </button>

        <div class="text-center pt-2">
            <a href="login.php" class="text-slate-500 hover:text-white text-sm transition-colors flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Login
            </a>
        </div>
    </form>
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

    const passInput = document.getElementById('new_password');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    passInput.addEventListener('input', () => {
        const val = passInput.value;
        let score = 0;
        
        if (val.length >= 6) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const widths = ['0%', '20%', '40%', '60%', '80%', '100%'];
        const colors = ['#475569', '#f43f5e', '#f59e0b', '#3b82f6', '#10b981', '#10b981'];
        const labels = [
            'Too short', 
            'Weak', 
            'Fair', 
            'Good', 
            'Strong', 
            'Excellent'
        ];

        strengthBar.style.width = widths[score];
        strengthBar.style.backgroundColor = colors[score];
        strengthText.innerText = labels[score];
        strengthText.style.color = colors[score];
    });
  </script>
</body>
</html>
