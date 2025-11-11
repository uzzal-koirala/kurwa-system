<?php
include '../../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - Kurwa System</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #E8EAF6;
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen">
  <div class="bg-white p-10 rounded-2xl shadow-lg text-center w-full max-w-md">
    <h2 class="text-2xl font-bold mb-3 text-[#2F3CFF]">Welcome, <?php echo $_SESSION['full_name']; ?> 👋</h2>
    <p class="text-gray-600 mb-6">You are successfully logged in to your dashboard.</p>
    
    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white py-2 px-6 rounded-lg transition">Logout</a>
  </div>
</body>
</html>
