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
    <title>Earnings | Kurwa Partner</title>
    <link rel="stylesheet" href="../../assets/css/restaurant_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/restaurant_earnings.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="restaurant-body">

<?php include '../../includes/components/restaurant_sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    <div class="page-header">
        <div class="flex items-center gap-4">
            <i class="ri-menu-line mobile-toggle" id="openSidebarUniversal" style="font-size: 26px; color: var(--rest-secondary-dark); cursor: pointer;"></i>
            <h1 class="page-title">Revenue & Payouts</h1>
        </div>
    </div>

    <!-- Proportional Hero Section -->
    <div class="earnings-hero">
        <!-- Balance Card -->
        <div class="balance-card-refined">
            <div class="bal-header">
                <span>Kurwa Wallet</span>
                <img src="../../assets/images/logo-white.png" alt="" style="height: 20px; opacity: 0.6;">
            </div>
            <div class="bal-amount">
                <span style="font-size: 14px; opacity: 0.7; font-weight: 500;">Available Balance</span>
                <h1>Rs. <?= number_format($available_balance, 2) ?></h1>
            </div>
            <div class="bal-footer">
                <div>
                    <div class="label">Partner Since</div>
                    <div class="val">Mar 2024</div>
                </div>
                <div style="text-align: right;">
                    <div class="label">Identity</div>
                    <div class="val">#RST-<?= str_pad($restaurant_id, 5, '0', STR_PAD_LEFT) ?></div>
                </div>
            </div>
        </div>

        <!-- Payout Panel -->
        <div class="payout-panel">
            <h3>Quick Payout</h3>
            <div class="amount-grid">
                <div class="amt-btn" onclick="setPayoutAmt(2000)">Rs. 2,000</div>
                <div class="amt-btn active" onclick="setPayoutAmt(5000)">Rs. 5,000</div>
                <div class="amt-btn" onclick="setPayoutAmt(10000)">Rs. 10,000</div>
                <div class="amt-btn" onclick="setPayoutAmt(20000)">Rs. 20,000</div>
            </div>
            <div class="payout-input-box">
                <span>Rs.</span>
                <input type="number" id="payoutInput" value="5000" min="1000">
            </div>
            <button class="btn-request-payout" onclick="submitPayout()">
                <i class="ri-arrow-right-up-line"></i> Withdraw Funds
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid-refined">
        <div class="stat-box">
            <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;"><i class="ri-bank-card-line"></i></div>
            <div>
                <div class="stat-label">Net Revenue</div>
                <div class="stat-value">Rs. <?= number_format($total_earnings, 2) ?></div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: #fff1f2; color: #e11d48;"><i class="ri-flashlight-line"></i></div>
            <div>
                <div class="stat-label">New Revenue</div>
                <div class="stat-value">Rs. <?= number_format($total_earnings * 0.1, 2) ?></div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: #fff7ed; color: #ea580c;"><i class="ri-calendar-todo-line"></i></div>
            <div>
                <div class="stat-label">Uncleared</div>
                <div class="stat-value">Rs. <?= number_format($pending_clearance, 2) ?></div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;"><i class="ri-checkbox-circle-line"></i></div>
            <div>
                <div class="stat-label">Total Payouts</div>
                <div class="stat-value">Rs. <?= number_format($total_withdrawals, 2) ?></div>
            </div>
        </div>
    </div>

    <!-- History -->
    <div class="history-section-refined">
        <h2>Recent Transactions</h2>
        <div style="overflow-x: auto;">
            <table class="trans-table">
                <thead>
                    <tr>
                        <th>Detail</th>
                        <th>Created</th>
                        <th>Type</th>
                        <th>Amount</th>
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
                            <div class="tx-detail">
                                <div class="tx-icon plus"><i class="ri-add-line"></i></div>
                                <div>
                                    <div style="font-weight: 700; color: #1e293b;">Order #<?= $t['id'] ?></div>
                                    <div style="font-size: 12px; color: #64748b;">Customer Sale</div>
                                </div>
                            </div>
                        </td>
                        <td style="color: #64748b; font-size: 13px;">
                            <?= date('M d, Y', strtotime($t['created_at'])) ?>
                        </td>
                        <td><span style="font-weight: 600; font-size: 11px; color: #4f46e5; background: #eef2ff; padding: 4px 10px; border-radius: 6px;">EARNING</span></td>
                        <td style="font-weight: 800; color: #1e293b;">+ Rs. <?= number_format($t['total_amount'], 2) ?></td>
                        <td>
                            <span class="tag-status <?= $t['status'] === 'completed' ? 'tag-success' : 'tag-pending' ?>">
                                <?= $t['status'] === 'completed' ? 'Cleared' : 'On Hold' ?>
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

    <!-- Chart -->
    <div class="chart-card-refined">
        <h2 style="font-size: 18px; font-weight: 800; color: var(--rest-secondary-dark); margin-bottom: 25px;">Revenue Overview</h2>
        <div style="height: 350px;">
            <canvas id="revChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../assets/js/sidebar.js"></script>
<script>
    function setPayoutAmt(val) {
        document.getElementById('payoutInput').value = val;
        document.querySelectorAll('.amt-btn').forEach(b => {
            if(b.innerText.includes(val.toLocaleString())) b.classList.add('active');
            else b.classList.remove('active');
        });
    }

    function submitPayout() {
        const amt = document.getElementById('payoutInput').value;
        if(amt < 1000) return Swal.fire('Error', 'Minimum withdrawal Rs. 1,000', 'error');
        
        Swal.fire({
            title: 'Confirm Withdrawal',
            text: `Withdraw Rs. ${parseFloat(amt).toLocaleString()} to your linked account?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1b2559'
        }).then(res => {
            if(res.isConfirmed) Swal.fire('Success', 'Payout request submitted!', 'success');
        });
    }

    const ctx = document.getElementById('revChart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 350);
    grad.addColorStop(0, 'rgba(43, 54, 116, 0.1)');
    grad.addColorStop(1, 'rgba(43, 54, 116, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [{
                data: [12000, 19000, 15000, 25000, 22000, 30000],
                borderColor: '#1b2559',
                backgroundColor: grad,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { borderDash: [5, 5], color: '#f1f5f9' }, ticks: { color: '#64748b' } },
                x: { grid: { display: false }, ticks: { color: '#64748b' } }
            }
        }
    });
</script>
</body>
</html>
