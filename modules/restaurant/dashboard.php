<?php
require_once '../../includes/core/config.php';
session_start();

if (!isset($_SESSION['restaurant_id'])) {
    header("Location: login.php");
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];
$restaurant_name = $_SESSION['restaurant_name'] ?? 'Restaurant';
$current_page = 'dashboard';

// Fetch basic restaurant data
$stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$restaurant = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch metrics (Mockdata for aesthetics, or real queries if records existed)
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM restaurant_orders WHERE restaurant_id = $restaurant_id AND status = 'completed'")->fetch_assoc()['total'] ?? 0.00;
$total_orders = $conn->query("SELECT COUNT(*) as count FROM restaurant_orders WHERE restaurant_id = $restaurant_id")->fetch_assoc()['count'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM restaurant_orders WHERE restaurant_id = $restaurant_id AND status = 'pending'")->fetch_assoc()['count'] ?? 0;
$menu_items = $conn->query("SELECT COUNT(*) as count FROM restaurant_menu WHERE restaurant_id = $restaurant_id")->fetch_assoc()['count'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Restaurant Partner</title>
    <link rel="stylesheet" href="../../assets/css/restaurant_sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--rest-secondary-dark);
            margin: 0;
            letter-spacing: -0.5px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px 50px;
            transition: all 0.3s ease;
        }

        /* Widgets Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: var(--shadow);
            transition: 0.3s;
            border: 1px solid transparent;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            border-color: #f1f5f9;
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }

        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .icon-orange { background: #fff2ed; color: var(--rest-primary); }
        .icon-blue { background: #eef2ff; color: var(--rest-secondary); }
        .icon-green { background: #f0fdf4; color: #22c55e; }
        .icon-purple { background: #faf5ff; color: #a855f7; }

        .metric-info h3 {
            margin: 0;
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-info p {
            margin: 5px 0 0 0;
            font-size: 24px;
            font-weight: 800;
            color: var(--rest-secondary-dark);
        }

        /* Chart & Orders Section */
        .dashboard-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .panel {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .panel-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--rest-secondary-dark);
            margin: 0;
        }

        .view-all {
            color: var(--rest-primary);
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            transition: 0.2s;
        }

        .view-all:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        /* Order List */
        .order-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            transition: 0.2s;
        }

        .order-item:hover {
            background: #fff;
            border-color: var(--rest-primary-light);
            box-shadow: 0 4px 15px rgba(255, 126, 95, 0.05);
        }

        .order-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: var(--rest-secondary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
        }

        .order-details { flex-grow: 1; overflow: hidden; }

        .order-details h4 {
            margin: 0 0 3px 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
        }

        .order-details p {
            margin: 0;
            font-size: 12px;
            color: var(--text-muted);
        }

        .order-amount {
            font-size: 15px;
            font-weight: 800;
            color: var(--rest-secondary-dark);
            text-align: right;
        }

        .order-status {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
            display: inline-block;
            margin-top: 5px;
        }

        .status-pending { background: #fffbeb; color: #f59e0b; }
        .status-completed { background: #f0fdf4; color: #22c55e; }

        .chart-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(180deg, rgba(255,126,95,0.05) 0%, rgba(255,255,255,0) 100%);
            border: 2px dashed #f1f5f9;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }

        .chart-placeholder i {
            font-size: 40px;
            color: var(--rest-primary-light);
            margin-bottom: 15px;
        }

        @media (max-width: 1200px) {
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
            .dashboard-content { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .metrics-grid { grid-template-columns: 1fr; }
            .main-content { padding: 20px; margin-left: 0; }
        }
    </style>
</head>
<body class="restaurant-body">

<?php include '../../includes/components/restaurant_sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    <div class="page-header">
        <div class="flex items-center gap-4">
            <i class="ri-menu-2-line mobile-toggle" id="openSidebarUniversal" style="font-size: 24px; color: #1b2559; cursor: pointer; display: none;"></i>
            <div>
                <h1 class="page-title">Dashboard Overview</h1>
                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Welcome back, <?= htmlspecialchars($restaurant['name']) ?></p>
            </div>
        </div>
        <div class="header-actions">
            <button style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 50%; width: 45px; height: 45px; cursor: pointer; color: var(--text-main); transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02)">
                <i class="ri-notification-3-line" style="font-size: 20px;"></i>
            </button>
        </div>
    </div>

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon icon-orange"><i class="ri-wallet-3-fill"></i></div>
            <div class="metric-info">
                <h3>Total Revenue</h3>
                <p>Rs. <?= number_format($total_revenue, 2) ?></p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon icon-blue"><i class="ri-shopping-bag-3-fill"></i></div>
            <div class="metric-info">
                <h3>Total Orders</h3>
                <p><?= number_format($total_orders) ?></p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon icon-green"><i class="ri-restaurant-fill"></i></div>
            <div class="metric-info">
                <h3>Menu Items</h3>
                <p><?= number_format($menu_items) ?></p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon icon-purple"><i class="ri-time-fill"></i></div>
            <div class="metric-info">
                <h3>Pending Orders</h3>
                <p><?= number_format($pending_orders) ?></p>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <!-- Revenue Chart -->
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Revenue Analytics</h2>
                <select style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 5px 10px; font-family: inherit; color: var(--text-muted); outline: none;">
                    <option>This Week</option>
                    <option>This Month</option>
                </select>
            </div>
            <div class="chart-placeholder">
                <i class="ri-bar-chart-grouprd-line" style="color: #ff7e5f; opacity: 0.5;"></i>
                <p>Not enough data to display chart</p>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Recent Orders</h2>
                <a href="orders.php" class="view-all">View All</a>
            </div>
            
            <div class="order-list">
                <?php
                $recent = $conn->query("SELECT ro.*, u.full_name as customer_name FROM restaurant_orders ro JOIN users u ON ro.user_id = u.id WHERE ro.restaurant_id = $restaurant_id ORDER BY ro.created_at DESC LIMIT 5");
                if ($recent && $recent->num_rows > 0):
                    while ($o = $recent->fetch_assoc()):
                ?>
                <div class="order-item">
                    <div class="order-avatar">
                        <?= strtoupper(substr($o['customer_name'], 0, 1)) ?>
                    </div>
                    <div class="order-details">
                        <h4><?= htmlspecialchars($o['customer_name']) ?></h4>
                        <p><?= date('M d, H:i', strtotime($o['created_at'])) ?></p>
                        <span class="order-status <?= $o['status'] === 'pending' ? 'status-pending' : 'status-completed' ?>">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </div>
                    <div class="order-amount">
                        Rs. <?= number_format($o['total_amount'], 2) ?>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                    <i class="ri-shopping-basket-2-line" style="font-size: 30px; opacity: 0.5;"></i>
                    <p style="margin-top: 10px; font-size: 14px;">No orders yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
