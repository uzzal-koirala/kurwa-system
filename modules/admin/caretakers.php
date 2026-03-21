<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "caretakers";

$caretakers = $conn->query("SELECT * FROM caretakers ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caretaker Management | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <h1>Caretaker Directory</h1>
        <button style="background:var(--admin-primary); border:none; color:white; padding:10px 25px; border-radius:12px; font-weight:700; cursor:pointer;">
            <i class="ri-add-line"></i> Add New Expert
        </button>
    </div>

    <div class="admin-stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
        <div class="admin-stat-card" style="padding: 15px;">
            <div class="admin-stat-info">
                <span>Total Experts</span>
                <h2><?= $caretakers->num_rows ?></h2>
            </div>
        </div>
        <div class="admin-stat-card" style="padding: 15px;">
            <div class="admin-stat-info">
                <span>Avg Rating</span>
                <h2>4.8</h2>
            </div>
        </div>
        <div class="admin-stat-card" style="padding: 15px;">
            <div class="admin-stat-info">
                <span>Verified</span>
                <h2>100%</h2>
            </div>
        </div>
        <div class="admin-stat-card" style="padding: 15px;">
            <div class="admin-stat-info">
                <span>On Duty</span>
                <h2>12</h2>
            </div>
        </div>
    </div>

    <div class="admin-panel-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Expert</th>
                    <th>Category</th>
                    <th>Experience</th>
                    <th>Rate</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($ct = $caretakers->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <img src="<?= htmlspecialchars($ct['image_url']) ?>" style="width:45px; height:45px; border-radius:14px; object-fit:cover;">
                            <div>
                                <div style="font-weight:700;"><?= htmlspecialchars($ct['full_name']) ?></div>
                                <div style="font-size:12px; color:var(--admin-primary); font-weight:600;"><?= htmlspecialchars($ct['specialization']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="admin-badge" style="background:rgba(99, 102, 241, 0.1); color:#818cf8;"><?= htmlspecialchars($ct['category']) ?></span></td>
                    <td><?= $ct['experience_years'] ?> Years</td>
                    <td style="font-weight:700;">Rs. <?= number_format($ct['price_per_day']) ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button style="background:rgba(255,255,255,0.05); border:1px solid var(--admin-border); color:white; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-pencil-line"></i></button>
                            <button style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); color:#ef4444; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
