<?php
// =============================================
// Kurwa System - Pharmacy OTP Verification
// =============================================

include '../../includes/core/config.php';

// Redirect if accessed directly
if (!isset($_GET['email']) || empty($_GET['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_GET['email'];
$message = "";
$error = false;

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $otp_input = implode('', $_POST['otp']);

    if (!empty($otp_input)) {
        // Check OTP in pharmacies table
        $stmt = $conn->prepare("SELECT * FROM pharmacies WHERE email = ? AND otp = ?");
        $stmt->bind_param("ss", $email, $otp_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $pharmacy = $result->fetch_assoc();
            
            // Mark as verified and clear OTP
            $update = $conn->prepare("UPDATE pharmacies SET verified = 1, otp = NULL WHERE email = ?");
            $update->bind_param("s", $email);
            $update->execute();

            // Start pharmacy session
            session_start();
            $_SESSION['pharmacy_id'] = $pharmacy['id'];
            $_SESSION['pharmacy_name'] = $pharmacy['name'];
            $_SESSION['role'] = 'pharmacy';

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
    $stmt = $conn->prepare("UPDATE pharmacies SET otp = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_otp, $email);
    $stmt->execute();

    // Fetch phone and name to send SMS
    $stmt = $conn->prepare("SELECT name, phone FROM pharmacies WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $pharma = $stmt->get_result()->fetch_assoc();

    if ($pharma && !empty($pharma['phone'])) {
        $sms_message = "Dear " . $pharma['name'] . ", your pharmacy verification code for Kurwa is: $new_otp.";
        send_sms($pharma['phone'], $sms_message);
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
    <title>Verify OTP - Pharmacy Partner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .otp-input {
            width: 45px; height: 55px;
            text-align: center; font-size: 1.5rem; font-weight: 700;
            border: 2px solid #e2e8f0; border-radius: 12px;
            background: #f8fafc; transition: all 0.3s ease;
        }
        .otp-input:focus { border-color: #059669; background: #fff; outline: none; box-shadow: 0 0 0 4px rgba(5,150,105,0.1); }
        .otp-error { border-color: #ef4444 !important; background: #fef2f2 !important; }
        .btn-pharmacy {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#f0fdf4] font-[Poppins]">
    <div class="bg-white shadow-2xl rounded-3xl p-8 w-full max-w-md text-center border border-green-50">
        <div class="mb-8">
            <div class="w-16 h-16 bg-green-50 text-[#059669] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="ri-shield-check-line text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-1">Pharmacy Verification</h2>
            <p class="text-gray-500 text-sm">Enter the code sent to your phone to verify your store.</p>
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
                class="w-full btn-pharmacy text-white font-bold py-4 rounded-xl shadow-lg shadow-green-100 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                Verify Pharmacy
            </button>

            <form method="POST">
                <button type="submit" name="resend" class="text-sm text-[#059669] font-semibold hover:underline">
                    Resend code
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
    </script>
</body>
</html>
