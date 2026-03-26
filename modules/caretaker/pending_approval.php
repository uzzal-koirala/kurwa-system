<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

$status = $_GET['status'] ?? 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Status | Kurwa System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="flex items-center justify-center min-h-screen bg-[#f8fafc] font-[Poppins] px-4">

    <div class="max-w-md w-full bg-white rounded-[2.5rem] p-10 shadow-2xl shadow-blue-100 text-center border border-white">
        
        <?php if($status === 'failed'): ?>
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <i class="ri-error-warning-fill text-4xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-3">Verification Failed</h1>
            <p class="text-gray-500 mb-8 leading-relaxed">
                Unfortunately, your expert profile could not be verified at this time. Please contact support for more details.
            </p>
        <?php else: ?>
            <div class="w-20 h-20 bg-blue-50 text-[#2F3CFF] rounded-3xl flex items-center justify-center mx-auto mb-6">
                <i class="ri-time-fill text-4xl animate-pulse"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-3">Verification Pending</h1>
            <p class="text-gray-500 mb-8 leading-relaxed">
                Your profile is currently being reviewed by our administration. We will notify you via SMS once your account is activated.
            </p>
        <?php endif; ?>

        <a href="../user/logout.php" class="inline-flex items-center gap-2 text-gray-500 font-bold hover:text-gray-800 transition-all">
            <i class="ri-logout-box-line"></i> Logout
        </a>
    </div>

</body>
</html>
