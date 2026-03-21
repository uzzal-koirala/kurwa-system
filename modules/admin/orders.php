<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "orders";

// Unified Service Tracking (Across all categories)
$orders_sql = "
    (SELECT 'Caretaker' as category, b.id, b.created_at, c.full_name as provider, u.full_name as customer, b.total_price as amount, b.status 
     FROM caretaker_bookings b 
     JOIN caretakers c ON b.caretaker_id = c.id 
     JOIN users u ON b.user_id = u.id)
    UNION ALL
    (SELECT 'Pharmacy' as category, m.id, m.created_at, p.name as provider, u.full_name as customer, m.total_price as amount, m.status 
     FROM medicine_orders m 
     JOIN pharmacies p ON m.pharmacy_id = p.id 
     JOIN users u ON m.user_id = u.id)
    UNION ALL
    (SELECT 'Canteen' as category, o.id, o.order_date as created_at, cn.name as provider, u.full_name as customer, o.total_amount as amount, o.status 
     FROM food_orders o 
     JOIN canteens cn ON o.canteen_id = cn.id 
     JOIN users u ON o.user_id = u.id)
    ORDER BY created_at DESC LIMIT 20";

$orders = $conn->query($orders_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Orders | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <h1>Global Service Orders</h1>
        <div style="display:flex; gap:15px;">
             <div class="stat-badge" style="background:var(--admin-card-bg); padding:10px 20px; border-radius:12px; border:1px solid var(--admin-border);">
                <span style="color:var(--admin-text-muted); font-size:12px;">Processable:</span> <span style="font-weight:700;">12</span>
             </div>
        </div>
    </div>

    <div class="admin-panel-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Customer</th>
                    <th>Provider</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && $orders->num_rows > 0): while($ord = $orders->fetch_assoc()): ?>
                <tr>
                    <td><span style="font-family:monospace; color:var(--admin-primary);">#ORD-<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($ord['customer']) ?></td>
                    <td><?= htmlspecialchars($ord['provider']) ?></td>
                    <td>
                        <?php 
                        $icon = "ri-pulse-line";
                        if($ord['category'] == 'Pharmacy') $icon = "ri-capsule-line";
                        if($ord['category'] == 'Canteen') $icon = "ri-restaurant-line";
                        ?>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <i class="<?= $icon ?>" style="color:var(--admin-text-muted);"></i>
                            <span><?= $ord['category'] ?></span>
                        </div>
                    </td>
                    <td style="font-weight:700;">Rs. <?= number_format($ord['amount']) ?></td>
                    <td>
                        <span class="admin-badge <?= (strtolower($ord['status']) == 'confirmed' || strtolower($ord['status']) == 'delivered' || strtolower($ord['status']) == 'completed') ? 'admin-badge-success' : 'admin-badge-warning' ?>">
                            <?= ucfirst($ord['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--admin-text-muted);">No orders found in system.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
