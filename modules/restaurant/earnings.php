<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['restaurant_id'])) {
    header("Location: login.php");
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];
$restaurant_name = $_SESSION['restaurant_name'] ?? 'Restaurant';
$current_page = 'earnings';

// Mock DB queries for aesthetics, replace with actual logic if withdrawal table exists later
$total_earnings = $conn->query("SELECT SUM(total_amount) as total FROM restaurant_orders WHERE restaurant_id = $restaurant_id AND status = 'completed'")->fetch_assoc()['total'] ?? 0.00;
$total_withdrawals = 0; // Mock until withdrawal table is built
$available_balance = $total_earnings - $total_withdrawals;
$pending_clearance = $conn->query("SELECT SUM(total_amount) as total FROM restaurant_orders WHERE restaurant_id = $restaurant_id AND status = 'pending'")->fetch_assoc()['total'] ?? 0.00;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earnings | Restaurant Partner</title>
    <link rel="stylesheet" href="../../assets/css/restaurant_sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title { font-size: 26px; font-weight: 800; color: var(--rest-secondary-dark); margin: 0; letter-spacing: -0.5px; }
        .main-content { margin-left: var(--sidebar-width); padding: 40px 50px; transition: all 0.3s ease; }

        /* Balance Banner */
        .balance-banner {
            background: linear-gradient(135deg, #1b2559 0%, #2f3cff 100%);
            border-radius: 20px;
            padding: 40px;
            color: white;
            box-shadow: 0 15px 35px rgba(47, 60, 255, 0.2);
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .balance-banner::before {
            content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%); border-radius: 50%;
        }

        .balance-info h3 { margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: rgba(255,255,255,0.8); }
        .balance-info h1 { margin: 0; font-size: 48px; font-weight: 800; letter-spacing: -1px; }
        
        .btn-withdraw {
            background: white; color: var(--rest-secondary-dark); border: none; padding: 15px 30px; border-radius: 14px; font-weight: 800; font-size: 16px; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .btn-withdraw:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.2); }

        /* Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card {
            background: white; border-radius: 20px; padding: 25px; border: 1px solid #f1f5f9; box-shadow: 0 5px 20px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 20px;
        }
        .stat-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
        .icon-1 { background: #fff2ed; color: var(--rest-primary); }
        .icon-2 { background: #eef2ff; color: var(--rest-secondary); }
        .icon-3 { background: #f0fdf4; color: #22c55e; }
        
        .stat-info h4 { margin: 0 0 5px 0; font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }
        .stat-info h2 { margin: 0; font-size: 24px; font-weight: 800; color: var(--rest-secondary-dark); }

        /* History Table */
        .panel { background: white; border-radius: 20px; padding: 30px; border: 1px solid #f1f5f9; box-shadow: 0 5px 20px rgba(0,0,0,0.02); }
        .panel-header { margin-bottom: 25px; }
        .panel-title { font-size: 18px; font-weight: 800; color: var(--rest-secondary-dark); margin: 0; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        th { color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        td { color: var(--text-main); font-weight: 500; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .status-completed { background: #f0fdf4; color: #22c55e; }
        .status-pending { background: #fffbeb; color: #f59e0b; }

        @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) {
            .main-content { padding: 20px; margin-left: 0; }
            .balance-banner { flex-direction: column; text-align: center; gap: 25px; }
            .stats-grid { grid-template-columns: 1fr; }
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
                <h1 class="page-title">Earnings</h1>
                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Track your revenue and request payouts.</p>
            </div>
        </div>
    </div>

    <div class="balance-banner">
        <div class="balance-info">
            <h3>Available Balance</h3>
            <h1>Rs. <?= number_format($available_balance, 2) ?></h1>
        </div>
        <div>
            <button class="btn-withdraw"><i class="ri-bank-card-line" style="margin-right: 8px;"></i> Request Payout</button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-1"><i class="ri-money-rupee-circle-fill"></i></div>
            <div class="stat-info">
                <h4>Net Earnings</h4>
                <h2>Rs. <?= number_format($total_earnings, 2) ?></h2>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-2"><i class="ri-history-line"></i></div>
            <div class="stat-info">
                <h4>Pending Clearance</h4>
                <h2>Rs. <?= number_format($pending_clearance, 2) ?></h2>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-3"><i class="ri-check-double-line"></i></div>
            <div class="stat-info">
                <h4>Total Payouts</h4>
                <h2>Rs. <?= number_format($total_withdrawals, 2) ?></h2>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Transaction History</h2>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Displaying mock recent transactions for aesthetics
                    $trans = $conn->query("SELECT id, total_amount, created_at, status FROM restaurant_orders WHERE restaurant_id = $restaurant_id ORDER BY created_at DESC LIMIT 5");
                    if($trans && $trans->num_rows > 0):
                        while($t = $trans->fetch_assoc()):
                    ?>
                    <tr>
                        <td style="font-weight: 700;">#TXN-<?= str_pad($t['id'], 6, '0', STR_PAD_LEFT) ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($t['created_at'])) ?></td>
                        <td>Order Revenue</td>
                        <td style="font-weight: 800; color: var(--rest-secondary-dark);">+ Rs. <?= number_format($t['total_amount'], 2) ?></td>
                        <td>
                            <span class="status-badge <?= $t['status'] === 'completed' ? 'status-completed' : 'status-pending' ?>">
                                <?= ucfirst($t['status'] === 'completed' ? 'Cleared' : 'Pending') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">No transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <div class="panel" style="margin-top: 30px;">
        <div class="panel-header">
            <h2 class="panel-title">Revenue Trend</h2>
        </div>
        <div style="height: 300px; position: relative;">
            <canvas id="earningsChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/sidebar.js"></script>
<script>
    const ctx = document.getElementById('earningsChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(47, 60, 255, 0.2)');
    gradient.addColorStop(1, 'rgba(47, 60, 255, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Monthly Revenue',
                data: [45000, 52000, 48000, 61000, 55000, 67000],
                borderColor: '#2f3cff',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
</body>
</html>
