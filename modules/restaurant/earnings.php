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
    <link rel="stylesheet" href="../../assets/css/restaurant_earnings.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Small local overrides if needed */
        .mobile-toggle { display: block !important; }
    </style>
</head>
<body class="restaurant-body">

<?php include '../../includes/components/restaurant_sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    <div class="page-header">
        <div class="flex items-center gap-4">
            <i class="ri-menu-line mobile-toggle" id="openSidebarUniversal" style="font-size: 26px; color: var(--rest-secondary-dark); cursor: pointer; display: none;"></i>
            <div>
                <h1 class="page-title">Earnings</h1>
                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Track your revenue and manage payouts.</p>
            </div>
        </div>
    </div>

    <!-- Earnings Hero Section -->
    <div class="earnings-hero">
        <!-- Left: Premium Balance Card -->
        <div class="balance-card-large">
            <div class="balance-header">
                <div class="chip-icon"></div>
                <div class="brand-logo">KURWA PARTNER</div>
            </div>
            <div class="balance-body">
                <span class="label">Available Balance</span>
                <h2 class="amount">Rs. <?= number_format($available_balance, 2) ?></h2>
            </div>
            <div class="balance-footer">
                <div class="card-holder">
                    <span class="label">Restaurant ID</span>
                    <span class="name">#RST-<?= str_pad($restaurant_id, 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div style="opacity: 0.8; font-weight: 800; font-size: 20px;">VISA</div>
            </div>
        </div>

        <!-- Right: Payout Form -->
        <div class="payout-card">
            <h2>Quick Payout</h2>
            <div class="amount-selector">
                <div class="amount-preset" onclick="setPayoutAmount(5000)">Rs. 5k</div>
                <div class="amount-preset active" onclick="setPayoutAmount(10000)">Rs. 10k</div>
                <div class="amount-preset" onclick="setPayoutAmount(25000)">Rs. 25k</div>
                <div class="amount-preset" onclick="setPayoutAmount(50000)">Rs. 50k</div>
            </div>

            <div class="custom-amount">
                <label>Or Enter Custom Amount</label>
                <div class="input-wrapper">
                    <span>Rs.</span>
                    <input type="number" id="payoutAmount" value="10000" min="1000">
                </div>
            </div>

            <button class="payout-submit-btn" onclick="requestPayout()">
                <i class="ri-send-plane-fill"></i> Request Quick Payout
            </button>
        </div>
    </div>

    <!-- Refined Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card-refined">
            <div class="icon-box" style="background: #fff2ed; color: #ff7e5f;"><i class="ri-money-rupee-circle-fill"></i></div>
            <div style="flex-grow: 1;">
                <div class="stat-label">Net Earnings</div>
                <div class="stat-val">Rs. <?= number_format($total_earnings, 2) ?></div>
            </div>
        </div>
        <div class="stat-card-refined">
            <div class="icon-box" style="background: #eef2ff; color: #2f3cff;"><i class="ri-time-line"></i></div>
            <div style="flex-grow: 1;">
                <div class="stat-label">Pending Clearance</div>
                <div class="stat-val">Rs. <?= number_format($pending_clearance, 2) ?></div>
            </div>
        </div>
        <div class="stat-card-refined">
            <div class="icon-box" style="background: #f0fdf4; color: #22c55e;"><i class="ri-hand-coin-fill"></i></div>
            <div style="flex-grow: 1;">
                <div class="stat-label">Total Payouts</div>
                <div class="stat-val">Rs. <?= number_format($total_withdrawals, 2) ?></div>
            </div>
        </div>
    </div>

    <!-- History Panel -->
    <div class="history-section">
        <h2 class="history-header">Recent Transactions</h2>
        <div style="overflow-x: auto;">
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Transaction Detail</th>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th style="text-align: right;">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $trans = $conn->query("SELECT id, total_amount, created_at, status FROM restaurant_orders WHERE restaurant_id = $restaurant_id ORDER BY created_at DESC LIMIT 5");
                    if($trans && $trans->num_rows > 0):
                        while($t = $trans->fetch_assoc()):
                    ?>
                    <tr>
                        <td>
                            <div class="type-cell">
                                <div class="type-icon earning"><i class="ri-arrow-left-down-line"></i></div>
                                <div class="type-info">
                                    <h4>Order Revenue</h4>
                                    <p>Order #<?= str_pad($t['id'], 6, '0', STR_PAD_LEFT) ?></p>
                                </div>
                            </div>
                        </td>
                        <td style="font-size: 13px; font-weight: 500; color: var(--text-main);">
                            <?= date('M d, Y', strtotime($t['created_at'])) ?><br>
                            <span style="font-size: 11px; color: var(--text-muted);"><?= date('h:i A', strtotime($t['created_at'])) ?></span>
                        </td>
                        <td><span style="font-weight: 700; font-size: 12px; color: var(--rest-secondary); background: #eef2ff; padding: 4px 10px; border-radius: 6px;">EARNING</span></td>
                        <td class="amount-cell positive">+ Rs. <?= number_format($t['total_amount'], 2) ?></td>
                        <td>
                            <span class="status-tag <?= $t['status'] === 'completed' ? 'success' : 'pending' ?>">
                                <?= ucfirst($t['status'] === 'completed' ? 'Cleared' : 'Pending') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted); font-weight: 600;">No transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="chart-panel">
        <h2 class="history-header">Revenue Trend (Last 6 Months)</h2>
        <div style="height: 350px;">
            <canvas id="earningsChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../assets/js/sidebar.js"></script>
<script>
    function setPayoutAmount(amount) {
        document.getElementById('payoutAmount').value = amount;
        document.querySelectorAll('.amount-preset').forEach(p => {
            if (p.innerText.includes(amount >= 1000 ? (amount/1000) + 'k' : amount)) {
                p.classList.add('active');
            } else {
                p.classList.remove('active');
            }
        });
    }

    function requestPayout() {
        const amount = document.getElementById('payoutAmount').value;
        if(amount < 1000) {
            Swal.fire('Oops!', 'Minimum payout amount is Rs. 1,000', 'warning');
            return;
        }

        Swal.fire({
            title: 'Request Payout?',
            text: `You are about to request a payout of Rs. ${parseFloat(amount).toLocaleString()}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2f3cff',
            confirmButtonText: 'Yes, Request Payout'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Request Sent!', 'Your payout request has been sent for processing.', 'success');
            }
        });
    }

    const ctx = document.getElementById('earningsChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(47, 60, 255, 0.15)');
    gradient.addColorStop(1, 'rgba(47, 60, 255, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue',
                data: [45000, 52000, 48000, 61000, 55000, 67000],
                borderColor: '#2f3cff',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2f3cff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1b2559',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: false
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { borderDash: [5, 5], color: '#f1f5f9', drawBorder: false },
                    ticks: { color: '#94a3b8', font: { size: 12, weight: '600' } }
                },
                x: { 
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 12, weight: '600' } }
                }
            }
        }
    });
</script>
</body>
</html>
