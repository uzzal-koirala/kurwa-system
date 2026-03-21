<?php
session_start();
require_once '../../includes/core/config.php';

// Check if pharmacy is logged in
if (!isset($_SESSION['pharmacy_id'])) {
    header("Location: login.php");
    exit();
}

$pharmacy_id = $_SESSION['pharmacy_id'];
$current_page = 'dashboard';

// Fetch Pharmacy Data
$pharmacy_query = $conn->query("SELECT * FROM pharmacies WHERE id = $pharmacy_id");
$pharmacy = $pharmacy_query->fetch_assoc();

// Statistics Queries
$total_earnings_query = $conn->query("SELECT SUM(total_amount) as total FROM pharmacy_orders WHERE pharmacy_id = $pharmacy_id AND status = 'completed'");
$total_earnings = $total_earnings_query->fetch_assoc()['total'] ?? 0;

$pending_orders_query = $conn->query("SELECT COUNT(*) as count FROM pharmacy_orders WHERE pharmacy_id = $pharmacy_id AND status = 'pending'");
$pending_orders = $pending_orders_query->fetch_assoc()['count'];

$total_medicines_query = $conn->query("SELECT COUNT(*) as count FROM medicines WHERE pharmacy_id = $pharmacy_id");
$total_medicines = $total_medicines_query->fetch_assoc()['count'];

// Recent Orders
$recent_orders = [];
$orders_sql = "
    SELECT id, user_id, total_amount, status, created_at 
    FROM pharmacy_orders 
    WHERE pharmacy_id = $pharmacy_id 
    ORDER BY created_at DESC LIMIT 5
";
$result = $conn->query($orders_sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Fetch user name
        $user_res = $conn->query("SELECT full_name FROM users WHERE id = {$row['user_id']}");
        $row['customer_name'] = $user_res->fetch_assoc()['full_name'] ?? 'Unknown Patient';
        $recent_orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Dashboard | Kurwa System</title>
    
    <!-- Using Google Fonts matching the login -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/restaurant_sidebar.css"> <!-- Reusing structure of restaurant sidebar, but overriding colors -->
    <link rel="stylesheet" href="../../assets/css/pharmacy_dashboard.css">
</head>
<body>

    <!-- Sidebar component -->
    <?php include '../../includes/components/pharmacy_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content" id="mainContent">
        <div class="dashboard-header">
            <div>
                <h1>Welcome back, <?= htmlspecialchars($pharmacy['name']) ?></h1>
                <p>Here's what's happening at your pharmacy today.</p>
            </div>
            <div class="header-actions">
                <span class="status-badge <?= $pharmacy['status'] === 'open' ? 'status-open' : 'status-closed' ?>">
                    <div class="status-indicator"></div>
                    Store is <?= ucfirst($pharmacy['status']) ?>
                </span>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="m-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="ri-wallet-3-fill"></i>
                </div>
                <div class="m-info">
                    <p>Total Revenue</p>
                    <h3>Rs. <?= number_format($total_earnings, 2) ?></h3>
                </div>
            </div>

            <div class="metric-card">
                <div class="m-icon" style="background: #fffbeb; color: #d97706;">
                    <i class="ri-time-line"></i>
                </div>
                <div class="m-info">
                    <p>Pending Orders</p>
                    <h3><?= $pending_orders ?></h3>
                </div>
            </div>

            <div class="metric-card">
                <div class="m-icon" style="background: #f0fdfa; color: #0d9488;">
                    <i class="ri-medicine-bottle-line"></i>
                </div>
                <div class="m-info">
                    <p>Total Medicines</p>
                    <h3><?= $total_medicines ?></h3>
                </div>
            </div>

            <div class="metric-card">
                <div class="m-icon" style="background: #fdf4ff; color: #c026d3;">
                    <i class="ri-star-smile-fill"></i>
                </div>
                <div class="m-info">
                    <p>Store Rating</p>
                    <h3><?= number_format($pharmacy['rating'], 1) ?> <i class="ri-star-s-fill" style="color: #f59e0b; font-size:16px;"></i></h3>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Recent Orders Section -->
            <div class="card recent-orders-card">
                <div class="card-header">
                    <h2>Recent Orders</h2>
                    <a href="orders.php" class="view-all">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <div class="empty-state">
                            <i class="ri- inbox-archive-line"></i>
                            <p>No orders received yet.</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Patient</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $o): ?>
                                    <tr>
                                        <td style="font-weight:600;">ORD-<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="patient-cell">
                                                <div class="patient-avatar"><?= substr($o['customer_name'], 0, 1) ?></div>
                                                <?= htmlspecialchars($o['customer_name']) ?>
                                            </div>
                                        </td>
                                        <td style="font-weight: 600; color: #334155;">Rs. <?= number_format($o['total_amount'], 2) ?></td>
                                        <td>
                                            <?php 
                                            // Handle Status Tags
                                            $tag_class = 'status-tag-pending';
                                            if ($o['status'] === 'preparing') $tag_class = 'status-tag-preparing';
                                            if ($o['status'] === 'out_for_delivery') $tag_class = 'status-tag-delivery';
                                            if ($o['status'] === 'completed') $tag_class = 'status-tag-completed';
                                            if ($o['status'] === 'cancelled') $tag_class = 'status-tag-cancelled';
                                            ?>
                                            <span class="status-tag <?= $tag_class ?>"><?= ucfirst(str_replace('_', ' ', $o['status'])) ?></span>
                                        </td>
                                        <td style="color:#64748b; font-size:13px;">
                                            <?= date('h:i A', strtotime($o['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions / Alerts -->
            <div class="card side-card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="card-body action-grid">
                    <a href="inventory.php" class="action-btn">
                        <i class="ri-add-box-line"></i>
                        <span>Add New Medicine</span>
                    </a>
                    <a href="orders.php" class="action-btn">
                        <i class="ri-file-list-3-line"></i>
                        <span>Manage Pending Orders</span>
                    </a>
                    <a href="settings.php" class="action-btn">
                        <i class="ri-store-3-line"></i>
                        <span>Update Store Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
