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

   <!-- External stylesheet for signup page -->
  <link rel="stylesheet" href="../../assets/css/otp.css">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Google Font: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <style>
   
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

<script src="../../assets/js/otp.js"></script>

</body>
</html>
