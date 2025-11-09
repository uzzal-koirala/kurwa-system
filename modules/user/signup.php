<?php 
include '../../includes/config.php';

$error = "";

// Retain input data
$fname = $lname = $email = $phone = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $fname = trim($_POST['fname']);
  $lname = trim($_POST['lname']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $password = $_POST['password'];
  $cpassword = $_POST['cpassword'];
  $otp = rand(100000, 999999);

  // Validate phone number (allow +977 or 10 digits)
  if (!preg_match("/^(\+977)?[0-9]{10}$/", $phone)) {
    $error = "Please enter a valid phone number with or without country code (+977).";
  }
  // Validate password match
  elseif ($password !== $cpassword) {
    $error = "Passwords do not match. Please try again.";
  } 
  else {
    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $fullname = $fname . " " . $lname;

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, otp) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $email, $phone, $hashed_password, $otp);

    if ($stmt->execute()) {
      header("Location: verify_otp.php?email=$email");
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
  <title>User Signup - Kurwa System</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Google Font: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- External stylesheet -->
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="flex items-center justify-center min-h-screen px-4">

  <!-- Outer Container -->
  <div class="bg-white shadow-lg rounded-2xl flex flex-col md:flex-row overflow-hidden max-w-4xl w-full">

    <!-- Left Blue Section -->
    <div class="hidden md:flex bg-[#2F3CFF] text-white flex-col justify-center p-10 w-[45%]">
      <div class="flex flex-col justify-center h-full">
        <h2 class="text-[20px] font-bold mb-2 leading-snug heading-desktop">
          Make Your <br> Hospital Life Easier.
        </h2>
        <p class="text-[13px] opacity-90 leading-relaxed mt-1">
          Nepal’s first online platform for on-demand caretaker and hospital support services.
        </p>
      </div>

      <div class="bg-[#3845FF] p-5 rounded-lg mt-10 flex items-center gap-4">
        <img src="../../assets/images/signup-review-img.png" alt="User Review" class="w-10 h-10 rounded-full object-cover" />
        <div>
          <p class="text-sm leading-tight">
            Such a wonderful platform that’s easy to use and work with. Highly recommended!
          </p>
          <p class="text-xs font-semibold mt-3">
            Sujal Bardewa <span class="font-normal text-gray-200">| Patient</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Mobile Top Blue Section -->
    <div class="md:hidden bg-[#2F3CFF] text-white text-center pt-10 pb-16 px-6 rounded-b-[80px]">
      <h2 class="text-[20px] font-bold mb-2 leading-snug">
        Make Your Hospital Life Easier.
      </h2>
      <p class="text-sm opacity-90 leading-relaxed">
        Nepal’s first online platform for on demand <br>caretaker and hospital support services.
      </p>
    </div>

    <!-- Right Form Section -->
    <div class="p-8 md:p-10 md:w-[55%] w-full flex flex-col justify-center">
      <h2 class="text-3xl font-bold mb-2 md:text-left text-center">
        Sign up to proceed
      </h2>
      <p class="text-sm mb-6 md:text-left text-center">
        Already have an account?
        <a href="login.php" class="text-[#2F3CFF] font-medium hover:underline">
          Login here
        </a>
      </p>

      <?php if (!empty($error)): ?>
        <div class="text-red-500 text-sm mb-4 text-center md:text-left font-medium">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <input type="text" name="fname" placeholder="First name"
          value="<?php echo htmlspecialchars($fname); ?>"
          class="border border-gray-300 rounded-lg p-3 focus:outline-none w-full sm:col-span-1" required />

        <input type="text" name="lname" placeholder="Last name"
          value="<?php echo htmlspecialchars($lname); ?>"
          class="border border-gray-300 rounded-lg p-3 focus:outline-none w-full sm:col-span-1" required />

        <input type="email" name="email" placeholder="Email address"
          value="<?php echo htmlspecialchars($email); ?>"
          class="col-span-2 border border-gray-300 rounded-lg p-3 focus:outline-none" required />

        <input type="text" name="phone" placeholder="Phone number (e.g. +9779812345678)"
          value="<?php echo htmlspecialchars($phone); ?>"
          class="col-span-2 border border-gray-300 rounded-lg p-3 focus:outline-none" required />

        <!-- Password field -->
        <div class="relative col-span-2">
          <input type="password" name="password" id="password"
            placeholder="Password"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none w-full pr-10" required />
          <button type="button" onclick="togglePassword('password', 'eye1')" 
            class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
            <svg id="eye1" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
        </div>

        <!-- Confirm Password field -->
        <div class="relative col-span-2">
          <input type="password" name="cpassword" id="cpassword"
            placeholder="Confirm password"
            class="border border-gray-300 rounded-lg p-3 focus:outline-none w-full pr-10" required />
          <button type="button" onclick="togglePassword('cpassword', 'eye2')" 
            class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
            <svg id="eye2" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
        </div>

        <button type="submit"
          class="col-span-2 btn-primary text-white py-3 rounded-lg font-medium text-lg w-full mt-2">
          Sign Up
        </button>
      </form>
    </div>
  </div>

  <!-- Password show/hide script -->
  <script>
    function togglePassword(id, eyeId) {
      const input = document.getElementById(id);
      const eye = document.getElementById(eyeId);

      if (input.type === "password") {
        input.type = "text";
        eye.innerHTML = `
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.979 9.979 0 012.431-4.362M9.88 9.88a3 3 0 104.24 4.24" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 3l18 18" />`;
      } else {
        input.type = "password";
        eye.innerHTML = `
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
      }
    }
  </script>
</body>
</html>
