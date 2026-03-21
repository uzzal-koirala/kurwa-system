<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['delivery_id'])) {
    header("Location: login.php");
    exit();
}
$current_page = 'earnings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earnings | Rider Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/delivery_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/delivery_dashboard.css">
    <style>
        .earnings-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 30px; }
        .e-card { background: white; padding: 25px; border-radius: 24px; box-shadow: var(--rider-shadow); border: 1px solid rgba(0,0,0,0.02); }
        .e-label { font-size: 12px; font-weight: 700; color: var(--rider-text-muted); text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; }
        .e-value { font-size: 28px; font-weight: 800; color: var(--rider-secondary); margin-top: 8px; }
        .e-trend { font-size: 12px; font-weight: 600; color: #10b981; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
        
        .payout-panel { display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; border: none; }
        .payout-info h3 { font-size: 24px; font-weight: 800; margin: 4px 0; }
        .payout-info p { font-size: 13px; opacity: 0.9; }
        .btn-withdraw { padding: 12px 24px; background: white; color: #10b981; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-withdraw:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

        .transaction-list { margin-top: 30px; }
        .t-item { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #f8fafc; }
        .t-icon { width: 40px; height: 40px; border-radius: 10px; background: #f0fdf4; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .t-info h5 { font-size: 14px; font-weight: 700; color: var(--rider-secondary); margin: 0; }
        .t-info p { font-size: 12px; color: var(--rider-text-muted); margin: 2px 0 0; }
        .t-amount { font-weight: 700; color: #10b981; }
        .t-amount.payout { color: #f59e0b; }

        @media (max-width: 900px) {
            .earnings-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <?php include "../../includes/components/delivery_sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="welcome-msg">
                <h1>Rider Earnings</h1>
                <p>Track your payouts and commissions</p>
            </div>
            
            <div class="header-actions">
                <div class="status-pill online">
                    <div class="status-dot"></div>
                    <span>Online</span>
                </div>
            </div>
        </header>

        <div class="rider-panel payout-panel">
            <div class="payout-info">
                <p>Available for Payout</p>
                <h3>Rs. 4,850.00</h3>
                <p>Next automatic payout: Monday, 23 Mar</p>
            </div>
            <button class="btn-withdraw">Request Early Payout</button>
        </div>

        <div class="earnings-grid">
            <div class="e-card">
                <p class="e-label">Today</p>
                <h4 class="e-value">Rs. 1,450</h4>
                <div class="e-trend"><i class="ri-arrow-up-line"></i> 12% vs yesterday</div>
            </div>
            <div class="e-card">
                <p class="e-label">This Week</p>
                <h4 class="e-value">Rs. 8,920</h4>
                <div class="e-trend"><i class="ri-arrow-up-line"></i> 8% vs last week</div>
            </div>
            <div class="e-card">
                <p class="e-label">Total Earned</p>
                <h4 class="e-value">Rs. 42,600</h4>
                <div class="e-trend" style="color:#64748b">Since joining Kurwa</div>
            </div>
        </div>

        <div class="rider-panel transaction-list">
            <h4 class="panel-title">Recent Transactions</h4>
            <div class="t-item">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div class="t-icon"><i class="ri-money-dollar-circle-line"></i></div>
                    <div class="t-info">
                        <h5>Delivery Commission #ORD-9821</h5>
                        <p>20 Mar, 2:15 PM</p>
                    </div>
                </div>
                <div class="t-amount">+Rs. 120.00</div>
            </div>
            <div class="t-item">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div class="t-icon" style="background:#fffbeb; color:#f59e0b;"><i class="ri-bank-card-line"></i></div>
                    <div class="t-info">
                        <h5>Weekly Payout Transferred</h5>
                        <p>19 Mar, 11:00 AM</p>
                    </div>
                </div>
                <div class="t-amount payout">-Rs. 5,200.00</div>
            </div>
            <div class="t-item">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div class="t-icon"><i class="ri-money-dollar-circle-line"></i></div>
                    <div class="t-info">
                        <h5>Delivery Commission #ORD-9818</h5>
                        <p>19 Mar, 10:40 AM</p>
                    </div>
                </div>
                <div class="t-amount">+Rs. 180.00</div>
            </div>
        </div>
    </main>
</body>
</html>
