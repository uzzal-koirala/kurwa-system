<?php include '../../includes/config.php'; ?>

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

  <!-- External stylesheet for signup page -->
  <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body class="flex items-center justify-center min-h-screen px-4">

  <?php
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $otp = rand(100000, 999999);

    $query = "INSERT INTO users (full_name, email, password, otp) VALUES 
             ('$fname $lname', '$email', '$password', '$otp')";
    if ($conn->query($query)) {
      header("Location: verify_otp.php?email=$email");
      exit;
    } else {
      $error = "Email already exists or invalid data.";
    }
  }
  ?>

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
        Sign in to processed
      </h2>
      <p class="text-sm mb-6 md:text-left text-center">
        Already have an account?
        <a href="login.php" class="text-[#2F3CFF] font-medium hover:underline">
          Login here
        </a>
      </p>

      <?php if (!empty($error)): ?>
        <div class="text-red-500 text-sm mb-4 text-center md:text-left">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <input type="text" name="fname" placeholder="First name"
          class="border border-gray-300 rounded-lg p-3 focus:outline-none w-full sm:col-span-1" required />
        <input type="text" name="lname" placeholder="Last name"
          class="border border-gray-300 rounded-lg p-3 focus:outline-none w-full sm:col-span-1" required />
        <input type="email" name="email" placeholder="Email address"
          class="col-span-2 border border-gray-300 rounded-lg p-3 focus:outline-none" required />
        <input type="text" name="phone" placeholder="Phone number"
          class="col-span-2 border border-gray-300 rounded-lg p-3 focus:outline-none" required />
        <input type="password" name="password" placeholder="Password"
          class="col-span-2 border border-gray-300 rounded-lg p-3 focus:outline-none" required />
        <input type="password" name="cpassword" placeholder="Confirm password"
          class="col-span-2 border border-gray-300 rounded-lg p-3 focus:outline-none" required />
        <button type="submit"
          class="col-span-2 btn-primary text-white py-3 rounded-lg font-medium text-lg w-full mt-2">
          Sign Up
        </button>
      </form>
    </div>
  </div>

</body>
</html>
