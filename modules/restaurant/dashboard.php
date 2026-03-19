<?php
require_once '../../includes/core/config.php';

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px 40px;
            transition: all 0.3s ease;
        }

        /* Dashboard Main Layout */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 3.2fr 1fr;
            gap: 25px;
            align-items: start;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #1b2559 0%, #2f3cff 100%);
            border-radius: 20px;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 15px 35px rgba(47, 60, 255, 0.2);
            position: relative;
            overflow: hidden;
            margin-bottom: 25px;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }

        .welcome-text h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 8px 0;
        }

        .welcome-text p {
            margin: 0;
            font-size: 15px;
            color: rgba(255,255,255,0.8);
        }

        .welcome-illustration {
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            border: 1px solid rgba(255,255,255,0.2);
            transform: rotate(10deg);
        }

        /* Widgets Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 boxes in a row */
            gap: 15px;
            margin-bottom: 25px;
        }

        .metric-card {
            background: var(--white);
            border-radius: 16px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            transition: 0.3s;
            border: 1px solid #f1f5f9;
        }

        .metric-card:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.06); }

        .metric-icon {
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 24px;
            flex-shrink: 0;
        }

        .icon-orange { background: #fff2ed; color: var(--rest-primary); }
        .icon-blue { background: #eef2ff; color: var(--rest-secondary); }
        .icon-green { background: #f0fdf4; color: #22c55e; }
        .icon-purple { background: #faf5ff; color: #a855f7; }

        .metric-info h3 { margin: 0; font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        .metric-info p { margin: 2px 0 0 0; font-size: 18px; font-weight: 800; color: var(--rest-secondary-dark); white-space: nowrap; }

        .panel {
            background: var(--white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .panel-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }

        .panel-title { font-size: 17px; font-weight: 800; color: var(--rest-secondary-dark); margin: 0; }

        .chart-container {
            width: 100%;
            height: 300px;
            position: relative;
        }

        /* Top Products */
        .top-products { display: flex; flex-direction: column; gap: 15px; }
        .product-item {
            display: flex; align-items: center; gap: 15px; padding: 10px 0; border-bottom: 1px solid #f1f5f9;
        }
        .product-item:last-child { border-bottom: none; }
        .product-img { width: 50px; height: 50px; border-radius: 12px; object-fit: cover; background: #eef2ff; }
        .product-info { flex-grow: 1; }
        .product-info h4 { margin: 0; font-size: 14px; font-weight: 700; color: var(--text-main); }
        .product-info p { margin: 2px 0 0 0; font-size: 12px; color: var(--text-muted); }
        .product-sales { font-weight: 800; color: var(--rest-primary); font-size: 14px; }

        /* Order List */
        .order-list { display: flex; flex-direction: column; gap: 15px; }
        .order-item {
            display: flex; align-items: center; gap: 15px; padding: 15px; border-radius: 16px; background: #f8fafc; border: 1px solid #f1f5f9; transition: 0.2s;
        }
        .order-item:hover { background: #fff; border-color: var(--rest-primary-light); }
        .order-avatar { width: 45px; height: 45px; border-radius: 12px; background: var(--rest-secondary); color: white; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; }
        .order-details { flex-grow: 1; overflow: hidden; }
        .order-details h4 { margin: 0 0 3px 0; font-size: 14px; font-weight: 700; color: var(--text-main); }
        .order-details p { margin: 0; font-size: 12px; color: var(--text-muted); }
        .order-amount { font-size: 15px; font-weight: 800; color: var(--rest-secondary-dark); text-align: right; }
        .status-badge { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 700; display: inline-block; margin-top: 5px; }
        .status-pending { background: #fffbeb; color: #f59e0b; }
        .status-completed { background: #f0fdf4; color: #22c55e; }

        @media (max-width: 1200px) {
            .dashboard-layout { grid-template-columns: 1fr; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .metrics-grid { grid-template-columns: 1fr; }
            .main-content { padding: 20px; margin-left: 0; }
            .welcome-banner { flex-direction: column; text-align: center; gap: 20px; }
            .welcome-illustration { transform: none; }
        }
    </style>
</head>
<body class="restaurant-body">

<?php include '../../includes/components/restaurant_sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    <div class="page-header">
        <i class="ri-menu-2-line mobile-toggle" id="openSidebarUniversal" style="font-size: 24px; color: #1b2559; cursor: pointer; display: none;"></i>
        <div style="flex-grow: 1;"></div>
        <button style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 50%; width: 45px; height: 45px; cursor: pointer; color: var(--text-main); transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02)">
            <i class="ri-notification-3-line" style="font-size: 20px;"></i>
        </button>
    </div>

    <!-- Main 2-Column Dashboard Structure -->
    <div class="dashboard-layout">
        
        <!-- Left Column -->
        <div class="left-col">
            
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h1>Welcome back, <?= htmlspecialchars($restaurant['name']) ?></h1>
                    <p>Here is what is happening with your restaurant today.</p>
                </div>
                <div class="welcome-illustration">
                    <i class="ri-store-3-line" style="color: white; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));"></i>
                </div>
            </div>

            <!-- Metrics -->
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
                    <div class="metric-icon icon-green"><i class="ri-store-3-fill"></i></div>
                    <div class="metric-info">
                        <h3>Total Products</h3>
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

            <!-- Quick Actions Banner -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                <a href="menu.php" style="background: white; border: 1px solid #f1f5f9; border-radius: 16px; padding: 15px; display: flex; align-items: center; gap: 15px; text-decoration: none; color: var(--text-main); box-shadow: 0 4px 10px rgba(0,0,0,0.02); transition: 0.2s; cursor: pointer;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #fff2ed; color: var(--rest-primary); display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="ri-add-line"></i></div>
                    <div><h4 style="margin:0; font-size: 14px; font-weight: 700;">New Product</h4><p style="margin:0; font-size:11px; color: var(--text-muted);">Add to store</p></div>
                </a>
                <a href="#" style="background: white; border: 1px solid #f1f5f9; border-radius: 16px; padding: 15px; display: flex; align-items: center; gap: 15px; text-decoration: none; color: var(--text-main); box-shadow: 0 4px 10px rgba(0,0,0,0.02); transition: 0.2s; cursor: pointer;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #eef2ff; color: var(--rest-secondary); display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="ri-coupon-3-line"></i></div>
                    <div><h4 style="margin:0; font-size: 14px; font-weight: 700;">Promotions</h4><p style="margin:0; font-size:11px; color: var(--text-muted);">Create offers</p></div>
                </a>
                <a href="settings.php" style="background: white; border: 1px solid #f1f5f9; border-radius: 16px; padding: 15px; display: flex; align-items: center; gap: 15px; text-decoration: none; color: var(--text-main); box-shadow: 0 4px 10px rgba(0,0,0,0.02); transition: 0.2s; cursor: pointer;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #f0fdf4; color: #22c55e; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="ri-store-2-line"></i></div>
                    <div><h4 style="margin:0; font-size: 14px; font-weight: 700;">Timing</h4><p style="margin:0; font-size:11px; color: var(--text-muted);">Store hours</p></div>
                </a>
            </div>

            <!-- Revenue Chart -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Revenue Analytics</h2>
                    <select style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 5px 10px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text-main); outline: none;">
                        <option>This Week</option>
                        <option>This Month</option>
                        <option>This Year</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Recent Orders</h2>
                    <a href="orders.php" style="color: var(--rest-primary); font-weight: 600; font-size: 13px; text-decoration: none;">View All</a>
                </div>
                <div class="order-list">
                    <?php
                    $recent = $conn->query("SELECT ro.*, u.full_name as customer_name FROM restaurant_orders ro JOIN users u ON ro.user_id = u.id WHERE ro.restaurant_id = $restaurant_id ORDER BY ro.created_at DESC LIMIT 4");
                    if ($recent && $recent->num_rows > 0):
                        while ($o = $recent->fetch_assoc()):
                    ?>
                    <div class="order-item">
                        <div class="order-avatar"><?= strtoupper(substr($o['customer_name'], 0, 1)) ?></div>
                        <div class="order-details">
                            <h4><?= htmlspecialchars($o['customer_name']) ?></h4>
                            <p><?= date('M d, H:i', strtotime($o['created_at'])) ?> • <span style="color: var(--rest-primary);">#ORD-<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></span></p>
                            <span class="status-badge <?= $o['status'] === 'pending' ? 'status-pending' : 'status-completed' ?>"><?= ucfirst($o['status']) ?></span>
                        </div>
                        <div class="order-amount">Rs. <?= number_format($o['total_amount'], 2) ?></div>
                    </div>
                    <?php endwhile; else: ?>
                    <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                        <i class="ri-shopping-basket-2-line" style="font-size: 30px; opacity: 0.5;"></i>
                        <p style="margin-top: 10px; font-size: 14px;">No orders available.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>

        <!-- Right Side Column -->
        <div class="right-col">
            
            <!-- Customer Satisfaction -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Customer Satisfaction</h2>
                    <i class="ri-more-2-fill" style="color: var(--text-muted); cursor: pointer; transition: 0.2s;" onmouseover="this.style.color='var(--rest-primary)'" onmouseout="this.style.color='var(--text-muted)'"></i>
                </div>
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="font-size: 48px; font-weight: 800; color: var(--rest-secondary-dark); margin: 0; line-height: 1;"><?= number_format($restaurant['rating'] > 0 ? $restaurant['rating'] : 4.8, 1) ?></h1>
                    <div style="color: #f59e0b; font-size: 20px; margin: 10px 0;">
                        <i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-half-fill"></i>
                    </div>
                    <p style="color: var(--text-muted); font-size: 13px; margin: 0; font-weight: 500;">Based on overall reviews</p>
                </div>
                <div style="border-top: 1px dashed #e2e8f0; padding-top: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                        <span style="color: var(--text-main); font-weight: 600;">Food Quality</span>
                        <span style="color: var(--rest-secondary-dark); font-weight: 800;">4.9</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                        <span style="color: var(--text-main); font-weight: 600;">Delivery Time</span>
                        <span style="color: var(--rest-secondary-dark); font-weight: 800;">4.6</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px;">
                        <span style="color: var(--text-main); font-weight: 600;">Packaging</span>
                        <span style="color: var(--rest-secondary-dark); font-weight: 800;">4.8</span>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Top Selling Items</h2>
                </div>
                <div class="top-products">
                    <?php
                    $top_items = $conn->query("SELECT m.name, m.image_url, COUNT(roi.id) as sales FROM restaurant_order_items roi JOIN restaurant_menu m ON roi.menu_item_id = m.id WHERE m.restaurant_id = $restaurant_id GROUP BY m.id ORDER BY sales DESC LIMIT 5");
                    
                    if ($top_items && $top_items->num_rows > 0):
                        while ($top = $top_items->fetch_assoc()):
                    ?>
                    <div class="product-item">
                        <?php if(!empty($top['image_url'])): ?>
                            <img src="<?= htmlspecialchars($top['image_url']) ?>" class="product-img">
                        <?php else: ?>
                            <div class="product-img" style="display:flex; align-items:center; justify-content:center; color: var(--rest-primary);"><i class="ri-restaurant-line" style="font-size: 20px;"></i></div>
                        <?php endif; ?>
                        <div class="product-info">
                            <h4><?= htmlspecialchars($top['name']) ?></h4>
                            <p>Popular choice</p>
                        </div>
                        <div class="product-sales"><?= $top['sales'] ?> Sales</div>
                    </div>
                    <?php endwhile; else: ?>
                    <p style="text-align: center; font-size: 13px; color: var(--text-muted); padding: 10px 0;">Not enough data.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notice Panel -->
            <div class="panel" style="background: linear-gradient(135deg, #fff2ed 0%, #ffffff 100%);">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--rest-primary); color: white; display:flex; align-items:center; justify-content:center; font-size: 20px; margin-bottom: 15px;">
                    <i class="ri-megaphone-fill"></i>
                </div>
                <h4 style="margin: 0 0 5px 0; font-size: 15px; font-weight: 700; color: var(--rest-secondary-dark);">Update your Products visually!</h4>
                <p style="margin: 0; font-size: 13px; color: var(--text-muted); line-height: 1.5;">Stores with images on their products receive up to <strong style="color: var(--rest-primary);">65% more orders</strong>. Go to your settings to upload images.</p>
            </div>
            
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    // Initialize Chart.js
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(255, 126, 95, 0.5)');
    gradient.addColorStop(1, 'rgba(255, 126, 95, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Revenue (Rs)',
                data: [1200, 1900, 1400, 2100, 1800, 3200, 2800], // Mock Data
                borderColor: '#ff7e5f',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#ff7e5f',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1b2559',
                    titleFont: { family: 'Poppins', size: 13 },
                    bodyFont: { family: 'Poppins', size: 14, weight: 'bold' },
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Rs. ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: '#f1f5f9', drawBorder: false },
                    ticks: { font: { family: 'Poppins', size: 12 }, color: '#64748b' }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { family: 'Poppins', size: 12 }, color: '#64748b' }
                }
            }
        }
    });
</script>
</body>
</html>
