<?php
// =============================================
// Kurwa System - Caretaker OTP Verification
// =============================================

include '../../includes/core/config.php';

// Redirect if accessed directly
if (!isset($_GET['email']) || empty($_GET['email'])) {
    header("Location: signup.php");
    exit;
}

$email = $_GET['email'];
$message = "";
$error = false;

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $otp_input = implode('', $_POST['otp']);

    if (!empty($otp_input)) {
        // Check OTP in caretakers table
        $stmt = $conn->prepare("SELECT * FROM caretakers WHERE email = ? AND otp = ?");
        $stmt->bind_param("ss", $email, $otp_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $caretaker = $result->fetch_assoc();
            
            // Mark as verified and clear OTP
            $update = $conn->prepare("UPDATE caretakers SET verified = 1, otp = NULL WHERE email = ?");
            $update->bind_param("s", $email);
            $update->execute();

            // Clear any user session just in case
            session_unset();
            
            // Start caretaker session
            $_SESSION['caretaker_id'] = $caretaker['id'];
            $_SESSION['caretaker_name'] = $caretaker['full_name'];
            $_SESSION['role'] = 'caretaker';

            $message = "<p class='text-green-600 text-sm font-medium text-center mb-3'>
                            OTP verified successfully! Redirecting to dashboard...
                        </p>";

            echo "<script>
                    setTimeout(() => { window.location.href = 'dashboard.php'; }, 1500);
                  </script>";
        } else {
            $message = "<p class='text-red-600 text-sm font-medium text-center mb-3'>
                            Invalid OTP. Please try again.
                        </p>";
            $error = true;
        }
    } else {
        $message = "<p class='text-red-600 text-sm font-medium text-center mb-3'>
                      Please enter your OTP code.
                    </p>";
        $error = true;
    }
}

// Handle Resend OTP
if (isset($_POST['resend'])) {
    $new_otp = rand(100000, 999999);
    $stmt = $conn->prepare("UPDATE caretakers SET otp = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_otp, $email);
    $stmt->execute();

    // Fetch phone and name to send SMS
    $stmt = $conn->prepare("SELECT full_name, phone FROM caretakers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $caretaker = $stmt->get_result()->fetch_assoc();

    if ($caretaker && !empty($caretaker['phone'])) {
        $sms_message = "Dear " . explode(' ', $caretaker['full_name'])[0] . ", your new Kurwa verification code is: $new_otp.";
        send_sms($caretaker['phone'], $sms_message);
    }

    $message = "<p class='text-green-600 text-sm font-medium text-center mb-3'>
                    A new OTP has been sent to your phone!
                </p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Caretaker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .otp-input {
            width: 45px; height: 55px;
            text-align: center; font-size: 1.5rem; font-weight: 700;
            border: 2px solid #e2e8f0; border-radius: 12px;
            background: #f8fafc; transition: all 0.3s ease;
        }
        .otp-input:focus { border-color: #2F3CFF; background: #fff; outline: none; box-shadow: 0 0 0 4px rgba(47,60,255,0.1); }
        .otp-error { border-color: #ef4444 !important; background: #fef2f2 !important; }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#f1f5f9] font-[Poppins]">
    <div class="bg-white shadow-2xl rounded-3xl p-8 w-full max-w-md text-center">
        <div class="mb-8">
            <div class="w-16 h-16 bg-blue-50 text-[#2F3CFF] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="ri-shield-check-line text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-1">Caretaker Verification</h2>
            <p class="text-gray-500 text-sm">We've sent a code to your phone. Enter it below to verify your account.</p>
        </div>

        <?php if (!empty($message)) echo $message; ?>

        <form method="POST" id="otpForm" class="flex justify-center gap-2 mb-8">
            <input type="text" name="otp[]" maxlength="1" class="otp-input <?php echo $error ? 'otp-error' : ''; ?>" required autocomplete="off">
            <input type="text" name="otp[]" maxlength="1" class="otp-input <?php echo $error ? 'otp-error' : ''; ?>" required autocomplete="off">
            <input type="text" name="otp[]" maxlength="1" class="otp-input <?php echo $error ? 'otp-error' : ''; ?>" required autocomplete="off">
            <input type="text" name="otp[]" maxlength="1" class="otp-input <?php echo $error ? 'otp-error' : ''; ?>" required autocomplete="off">
            <input type="text" name="otp[]" maxlength="1" class="otp-input <?php echo $error ? 'otp-error' : ''; ?>" required autocomplete="off">
            <input type="text" name="otp[]" maxlength="1" class="otp-input <?php echo $error ? 'otp-error' : ''; ?>" required autocomplete="off">
        </form>

        <div class="flex flex-col gap-3">
            <button type="submit" name="verify" form="otpForm"
                class="w-full bg-[#2F3CFF] hover:bg-[#1A237E] text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-200 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                Verify & Join
            </button>

            <form method="POST">
                <button type="submit" name="resend" class="text-sm text-[#2F3CFF] font-semibold hover:underline">
                    Didn't receive code? Resend
                </button>
            </form>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.otp-input');
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        // Paste support
        inputs[0].addEventListener('paste', (e) => {
            e.preventDefault();
            const data = e.clipboardData.getData('text').trim();
            if (data.length === inputs.length) {
                data.split('').forEach((char, i) => {
                    inputs[i].value = char;
                });
                inputs[inputs.length - 1].focus();
            }
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
</body>
</html>
