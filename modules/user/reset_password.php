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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#f8fafc] font-[Poppins]">

  <div class="bg-white shadow-2xl rounded-3xl overflow-hidden max-w-md w-full border border-gray-100 p-8 md:p-10">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl mb-4">
            <i data-lucide="lock" class="w-8 h-8"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Set New Password</h2>
        <p class="text-gray-500 text-sm mt-2">Almost there! Choose a strong password for your account.</p>
    </div>

    <?php if ($success): ?>
      <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-4 rounded-xl text-sm mb-6 flex flex-col items-center gap-2 text-center">
          <i data-lucide="check-circle" class="w-6 h-6"></i>
          <div>
              <p class="font-bold">Password Reset Successful!</p>
              <p class="mt-1">Redirecting you to login in 3 seconds...</p>
          </div>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2">
          <i data-lucide="alert-circle" class="w-4 h-4"></i>
          <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6" id="resetForm" <?php if ($success) echo 'style="display:none;"'; ?>>
        <div class="space-y-4">
            <div class="relative">
                <input type="password" name="new_password" placeholder="New Password"
                    class="w-full border-2 border-gray-100 rounded-xl p-4 focus:outline-none focus:border-blue-500 transition-all pl-12" required minlength="6" />
                <i data-lucide="key" class="w-5 h-5 absolute left-4 top-4.5 text-gray-400"></i>
            </div>

            <div class="relative">
                <input type="password" name="confirm_password" placeholder="Confirm Password"
                    class="w-full border-2 border-gray-100 rounded-xl p-4 focus:outline-none focus:border-blue-500 transition-all pl-12" required minlength="6" />
                <i data-lucide="shield-check" class="w-5 h-5 absolute left-4 top-4.5 text-gray-400"></i>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2">
            Confirm Reset
            <i data-lucide="check" class="w-5 h-5"></i>
        </button>
    </form>
  </div>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>
