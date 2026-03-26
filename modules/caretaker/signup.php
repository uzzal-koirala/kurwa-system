<?php 
include '../../includes/core/config.php';

$error = "";

// Retain input data
$full_name = $email = $phone = $category = $specialization = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $full_name = trim($_POST['full_name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $category = trim($_POST['category']);
  $specialization = trim($_POST['specialization']);
  $password = $_POST['password'];
  $cpassword = $_POST['cpassword'];

  // Validate phone number
  if (!preg_match("/^(\+977)?[0-9]{10}$/", $phone)) {
    $error = "Please enter a valid phone number.";
  }
  // Validate password match
  elseif ($password !== $cpassword) {
    $error = "Passwords do not match.";
  } 
  else {
    // Hash password and insert caretaker
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate 6-digit OTP
    $otp = rand(100000, 999999);

    $stmt = $conn->prepare("INSERT INTO caretakers (full_name, email, phone, category, specialization, password, otp, verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssssss", $full_name, $email, $phone, $category, $specialization, $hashed_password, $otp);

    if ($stmt->execute()) {
        // Send OTP via SMS
        $sms_message = "Dear " . explode(' ', $full_name)[0] . ", your Kurwa System caretaker verification code is: $otp. Please do not share this code.";
        $sms_res = send_sms($phone, $sms_message);
        
        // Log to system log for debugging
        $log_file = dirname(__DIR__, 2) . '/logs/sms_debug.log';
        $log_entry = date('[Y-m-d H:i:s] ') . "Signup SMS Attempt for $phone | Success: " . ($sms_res['success'] ?? '0') . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        
        // Don't start session yet, wait for verification
        header("Location: verify_otp.php?email=" . urlencode($email));
        exit;
    } else {
      $error = "Email already exists or invalid data.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Caretaker Signup - Kurwa System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-[#f1f5f9] font-[Poppins]">
  <div class="bg-white shadow-2xl rounded-3xl flex flex-col md:flex-row overflow-hidden max-w-5xl w-full">
    
    <!-- Left Section -->
    <div class="hidden md:flex bg-gradient-to-br from-[#2F3CFF] to-[#1A237E] text-white flex-col justify-between p-12 w-[40%]">
      <div>
          <h2 class="text-4xl font-bold mb-6 leading-tight">Join as a Caretaker</h2>
          <p class="text-lg opacity-90 leading-relaxed">
            Become part of Nepal's first on-demand healthcare support platform. Help patients and earn while making a difference.
          </p>
      </div>
      
      <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/20">
          <div class="flex items-center gap-4 mb-3">
              <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-[#2F3CFF]">
                  <i class="ri-user-heart-line text-2xl"></i>
              </div>
              <div>
                  <h4 class="font-bold">Trust & Quality</h4>
                  <p class="text-xs opacity-75">Verified caregivers only</p>
              </div>
          </div>
          <p class="text-sm italic opacity-85">"The best platform to find meaningful work and support families in need."</p>
      </div>
    </div>

    <!-- Right Form Section -->
    <div class="p-8 md:p-12 md:w-[60%] w-full bg-blue-50/50">
      <div class="mb-8">
          <h2 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h2>
          <p class="text-gray-500">Already a member? <a href="login.php" class="text-[#2F3CFF] font-semibold hover:underline">Login here</a></p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="bg-red-50 text-red-500 p-4 rounded-xl text-sm mb-6 flex items-center gap-3">
          <i class="ri-error-warning-line text-lg"></i>
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <div class="relative">
                <i class="ri-user-line absolute left-4 top-3.5 text-gray-400"></i>
                <input type="text" name="full_name" placeholder="John Doe" value="<?php echo htmlspecialchars($full_name); ?>"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-1 focus:ring-[#2F3CFF] focus:bg-white transition-all outline-none" required />
            </div>
        </div>

        <div>
            <div class="relative">
                <i class="ri-mail-line absolute left-4 top-3.5 text-gray-400"></i>
                <input type="email" name="email" placeholder="john@example.com" value="<?php echo htmlspecialchars($email); ?>"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-1 focus:ring-[#2F3CFF] focus:bg-white transition-all outline-none" required />
            </div>
        </div>

        <div>
            <div class="relative">
                <i class="ri-phone-line absolute left-4 top-3.5 text-gray-400"></i>
                <input type="text" name="phone" placeholder="98XXXXXXXX" value="<?php echo htmlspecialchars($phone); ?>"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-1 focus:ring-[#2F3CFF] focus:bg-white transition-all outline-none" required />
            </div>
        </div>

        <div>
            <div class="relative">
                <i class="ri-briefcase-line absolute left-4 top-3.5 text-gray-400"></i>
                <select name="category" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-1 focus:ring-[#2F3CFF] focus:bg-white transition-all outline-none appearance-none" required>
                    <option value="" disabled selected>Select Category</option>
                    <?php 
                    $cat_query = $conn->query("SELECT name FROM caretaker_categories ORDER BY name ASC");
                    while($cat = $cat_query->fetch_assoc()): ?>
                        <option value="<?php echo $cat['name']; ?>" <?php echo ($category == $cat['name']) ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div>
            <div class="relative">
                <i class="ri-medal-line absolute left-4 top-3.5 text-gray-400"></i>
                <input type="text" name="specialization" placeholder="Your Specialization" value="<?php echo htmlspecialchars($specialization); ?>"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-1 focus:ring-[#2F3CFF] focus:bg-white transition-all outline-none" required />
            </div>
        </div>

        <div>
            <div class="relative">
                <i class="ri-lock-line absolute left-4 top-3.5 text-gray-400"></i>
                <input type="password" name="password" id="password" placeholder="••••••••"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 pr-11 focus:ring-1 focus:ring-[#2F3CFF] focus:bg-white transition-all outline-none" required />
                <button type="button" onclick="togglePassword('password', 'eye-icon')" class="absolute right-4 top-3.5 text-gray-400">
                    <i id="eye-icon" class="ri-eye-line"></i>
                </button>
            </div>
        </div>

        <div>
            <div class="relative">
                <i class="ri-lock-check-line absolute left-4 top-3.5 text-gray-400"></i>
                <input type="password" name="cpassword" id="cpassword" placeholder="••••••••"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 pr-11 focus:ring-1 focus:ring-[#2F3CFF] focus:bg-white transition-all outline-none" required />
            </div>
        </div>

        <div class="md:col-span-2 mt-4">
            <button type="submit" class="w-full bg-[#2F3CFF] hover:bg-[#1A237E] text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-200 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                Register as Caretaker
            </button>
        </div>
      </form>
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
