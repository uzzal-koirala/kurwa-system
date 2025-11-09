<?php
include '../../includes/config.php';

$message = "";
$error = false;

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
  $email = $_GET['email'] ?? '';
  $otp_input = implode('', $_POST['otp']);

  if (!empty($email) && !empty($otp_input)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND otp = ?");
    $stmt->bind_param("ss", $email, $otp_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      // Mark user as verified and clear OTP
      $update = $conn->prepare("UPDATE users SET verified = 1, otp = NULL WHERE email = ?");
      $update->bind_param("s", $email);
      $update->execute();

      $message = "<p class='text-green-600 text-sm font-medium text-center mb-3'>OTP verified successfully! Redirecting...</p>";
      echo "<script>
              setTimeout(() => { window.location.href = 'login.php'; }, 1500);
            </script>";
    } else {
      $message = "<p class='text-red-600 text-sm font-medium text-center mb-3'>Invalid OTP. Please try again.</p>";
      $error = true;
    }
  } else {
    $message = "<p class='text-red-600 text-sm font-medium text-center mb-3'>Please enter your OTP code.</p>";
    $error = true;
  }
}

// Handle Resend Code
if (isset($_POST['resend'])) {
  $email = $_GET['email'] ?? '';
  if (!empty($email)) {
    $new_otp = rand(100000, 999999);
    $stmt = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_otp, $email);
    $stmt->execute();

    $message = "<p class='text-green-600 text-sm font-medium text-center mb-3'>A new OTP has been generated! Please check.</p>";
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

  <!-- Google Font: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #E8EAF6;
    }

    .otp-input {
      width: 3rem;
      height: 3rem;
      text-align: center;
      font-size: 1.2rem;
      border: 2px solid #4C5BFF;
      border-radius: 6px;
      outline: none;
      transition: 0.2s;
    }

    .otp-input:focus {
      border-color: #2430D8;
      box-shadow: 0 0 0 2px rgba(47, 60, 255, 0.3);
    }

    .otp-error {
      border-color: #FF4C4C !important;
      box-shadow: 0 0 0 1.5px rgba(255, 76, 76, 0.5) !important;
    }

    .btn-primary {
      background-color: #2F3CFF;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #2430D8;
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(36, 48, 216, 0.25);
    }

    .btn-secondary {
      color: #2F3CFF;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-secondary:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen px-4">

  <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md text-center">
    <h2 class="text-2xl font-bold mb-1">Account Verification</h2>
    <p class="text-gray-600 text-sm mb-6">Enter your OTP code here</p>

    <?php if (!empty($message)) echo $message; ?>

    <form method="POST" id="otpForm" class="flex justify-center gap-2 mb-6">
      <?php for ($i = 1; $i <= 6; $i++): ?>
        <input type="text" name="otp[]" maxlength="1" class="otp-input <?php echo $error ? 'otp-error' : ''; ?>" required />
      <?php endfor; ?>
    </form>

    <div class="flex flex-col gap-3">
      <button type="submit" name="verify" form="otpForm"
        class="btn-primary text-white py-3 rounded-lg font-medium text-lg w-full">
        Verify Code
      </button>

      <form method="POST">
        <button type="submit" name="resend" class="btn-secondary text-sm">
          Resend code
        </button>
      </form>
    </div>
  </div>

  <script>
    const inputs = document.querySelectorAll(".otp-input");

    // Auto move cursor between boxes
    inputs.forEach((input, index) => {
      input.addEventListener("input", (e) => {
        const value = e.target.value;
        if (value.length === 1 && index < inputs.length - 1) {
          inputs[index + 1].focus();
        }
      });
      input.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && input.value === "" && index > 0) {
          inputs[index - 1].focus();
        }
      });
    });

    // Paste full OTP at once
    inputs[0].addEventListener("paste", (e) => {
      e.preventDefault();
      const pasteData = (e.clipboardData || window.clipboardData).getData("text").trim();
      if (pasteData.length === inputs.length) {
        pasteData.split("").forEach((char, i) => {
          if (inputs[i]) inputs[i].value = char;
        });
      }
    });
  </script>
</body>
</html>
