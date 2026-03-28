<?php
require_once '../../includes/core/config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$phone = $_SESSION['reset_phone'] ?? '';

if (empty($phone)) {
    header("Location: forgot_password.php");
    exit;
}

$message = "";
$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Combine the 6-digit inputs
    $entered_otp = "";
    for ($i = 1; $i <= 6; $i++) {
        $entered_otp .= $_POST["otp_$i"] ?? "";
    }

    if (strlen($entered_otp) < 6) {
        $message = "Please enter all 6 digits of the OTP.";
        $error = true;
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ? AND otp = ?");
        $stmt->bind_param("ss", $phone, $entered_otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // OTP is correct
            $_SESSION['otp_verified'] = true;
            header("Location: reset_password.php");
            exit;
        } else {
            $message = "Invalid OTP. Please try again.";
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
  <title>Verify OTP - Kurwa System</title>

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
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl mb-4">
            <i data-lucide="shield-check" class="w-8 h-8"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Verify OTP</h2>
        <p class="text-gray-500 text-sm mt-2">We've sent a 6-digit code to <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($phone); ?></span></p>
    </div>

    <?php if ($error): ?>
      <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2">
          <i data-lucide="alert-circle" class="w-4 h-4"></i>
          <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8" id="otpForm">
        <div class="flex justify-between gap-2">
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <input type="text" name="otp_<?php echo $i; ?>" maxlength="1" 
                    class="otp-input w-12 h-14 text-center text-xl font-bold border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-50/50 transition-all"
                    required autocomplete="off" />
            <?php endfor; ?>
        </div>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2">
            Verify & Continue
            <i data-lucide="arrow-right" class="w-5 h-5"></i>
        </button>

        <div class="text-center">
            <p class="text-sm text-gray-500">Didn't receive the code?</p>
            <a href="forgot_password.php" class="text-blue-600 font-semibold hover:text-blue-700 text-sm mt-1 inline-block">Resend Code</a>
        </div>
    </form>
  </div>

  <script>
    lucide.createIcons();

    const inputs = document.querySelectorAll('.otp-input');
    
    inputs.forEach((input, index) => {
        // Handle numerical input and focus shifting
        input.addEventListener('input', (e) => {
            if (e.data && !/^\d+$/.test(e.data)) {
                input.value = '';
                return;
            }
            
            if (input.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        // Handle backspace
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        // Handle paste
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const data = e.clipboardData.getData('text').slice(0, 6);
            if (!/^\d+$/.test(data)) return;

            data.split('').forEach((char, i) => {
                if (inputs[i]) {
                    inputs[i].value = char;
                    if (inputs[i+1]) inputs[i+1].focus();
                }
            });
        });
    });
  </script>
</body>
</html>
