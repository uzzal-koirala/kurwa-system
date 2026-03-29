<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "partners";

$pharmacies = $conn->query("SELECT * FROM pharmacies ORDER BY id DESC");
$canteens = $conn->query("SELECT * FROM canteens ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Management | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <h1>Partners & Stores</h1>
        <div style="display:flex; gap:10px;">
            <button style="background:var(--admin-primary); border:none; color:white; padding:10px 20px; border-radius:12px; font-weight:700; cursor:pointer;"><i class="ri-add-line"></i> Add Pharmacy</button>
            <button style="background:var(--success); border:none; color:white; padding:10px 20px; border-radius:12px; font-weight:700; cursor:pointer;"><i class="ri-restaurant-line"></i> Add Canteen</button>
        </div>
    </div>

    <div class="admin-content-layout" style="grid-template-columns: 1fr 1fr;">
        <!-- Pharmacies -->
        <div class="admin-panel-box">
             <div class="admin-panel-header">
                <h3>Pharmacy Partners</h3>
                <span style="font-size:12px; color:var(--admin-text-muted);"><?= $pharmacies->num_rows ?> Registered</span>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Store Name</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $pharmacies->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:700;"><?= htmlspecialchars($p['name']) ?></td>
                        <td style="font-size:12px;"><?= htmlspecialchars($p['address']) ?></td>
                        <td><button style="background:none; border:none; color:var(--admin-primary); cursor:pointer;"><i class="ri-settings-4-line"></i></button></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Canteens -->
        <div class="admin-panel-box">
             <div class="admin-panel-header">
                <h3>Canteen Partners</h3>
                <span style="font-size:12px; color:var(--admin-text-muted);"><?= $canteens->num_rows ?> Registered</span>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Canteen Name</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($c = $canteens->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:700;"><?= htmlspecialchars($c['name']) ?></td>
                        <td style="font-size:12px;"><?= htmlspecialchars($c['address'] ?? $c['type'] ?? 'N/A') ?></td>
                        <td><button style="background:none; border:none; color:var(--success); cursor:pointer;"><i class="ri-settings-4-line"></i></button></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
