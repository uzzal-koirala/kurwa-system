<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['delivery_id'])) {
    header("Location: login.php");
    exit();
}
$current_page = 'orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Orders | Rider Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/delivery_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/delivery_dashboard.css">
    <style>
        .order-card { display: flex; align-items: flex-start; gap: 20px; padding: 25px; border-bottom: 1px solid #f1f5f9; position: relative; }
        .order-card:last-child { border-bottom: none; }
        .order-meta { flex-shrink: 0; text-align: center; width: 80px; }
        .order-time { font-size: 13px; font-weight: 700; color: var(--rider-secondary); }
        .order-dist { font-size: 11px; color: var(--rider-text-muted); margin-top: 4px; }
        
        .order-main { flex: 1; }
        .order-header { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .order-title { font-size: 16px; font-weight: 700; color: var(--rider-secondary); }
        .order-type { font-size: 11px; padding: 2px 8px; border-radius: 4px; font-weight: 600; }
        
        .route-path { position: relative; padding-left: 20px; }
        .route-line { position: absolute; left: 5px; top: 10px; bottom: 10px; width: 2px; background: #e2e8f0; }
        .route-pt { position: relative; margin-bottom: 15px; }
        .route-pt::before { content: ''; position: absolute; left: -20px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: white; border: 3px solid #cbd5e1; z-index: 1; }
        .route-pt.pickup::before { border-color: #3b82f6; }
        .route-pt.dropoff::before { border-color: #10b981; }
        
        .pt-label { font-size: 11px; font-weight: 700; color: var(--rider-text-muted); text-transform: uppercase; }
        .pt-addr { font-size: 14px; color: var(--rider-text-main); margin-top: 2px; }

        .order-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-accept { padding: 10px 20px; background: var(--rider-primary); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-details { padding: 10px 20px; background: #f1f5f9; color: var(--rider-text-main); border: none; border-radius: 10px; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>

    <?php include "../../includes/components/delivery_sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="welcome-msg">
                <h1>Active Assignments</h1>
                <p>New orders and active routes</p>
            </div>
        </header>

        <div class="rider-panel" style="padding: 0;">
            <div class="order-card">
                <div class="order-meta">
                    <div class="order-time">15:30</div>
                    <div class="order-dist">4.2 km</div>
                </div>
                <div class="order-main">
                    <div class="order-header">
                        <div class="order-title">Order #ORD-9901</div>
                        <span class="order-type" style="background:#fdf2f8; color:#db2777;">Food</span>
                    </div>
                    <div class="route-path">
                        <div class="route-line"></div>
                        <div class="route-pt pickup">
                            <div class="pt-label">Pickup From</div>
                            <div class="pt-addr">The Burger House, Jhamsikhel</div>
                        </div>
                        <div class="route-pt dropoff">
                            <div class="pt-label">Drop-off To</div>
                            <div class="pt-addr">Sanepa, Near British School</div>
                        </div>
                    </div>
                    <div class="order-actions">
                        <button class="btn-accept">Accept Order</button>
                        <button class="btn-details">View Map</button>
                    </div>
                </div>
            </div>

            <div class="order-card">
                <div class="order-meta">
                    <div class="order-time">16:05</div>
                    <div class="order-dist">2.8 km</div>
                </div>
                <div class="order-main">
                    <div class="order-header">
                        <div class="order-title">Order #ORD-9905</div>
                        <span class="order-type" style="background:#f0fdf4; color:#10b981;">Medicine</span>
                    </div>
                    <div class="route-path">
                        <div class="route-line"></div>
                        <div class="route-pt pickup">
                            <div class="pt-label">Pickup From</div>
                            <div class="pt-addr">Everest Pharmacy, Pulchowk</div>
                        </div>
                        <div class="route-pt dropoff">
                            <div class="pt-label">Drop-off To</div>
                            <div class="pt-addr">Damodar Marg, Kupondole</div>
                        </div>
                    </div>
                    <div class="order-actions">
                        <button class="btn-accept">Accept Order</button>
                        <button class="btn-details">View Map</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
