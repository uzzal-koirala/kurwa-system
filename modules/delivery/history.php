<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['delivery_id'])) {
    header("Location: login.php");
    exit();
}
$current_page = 'history';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery History | Rider Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/delivery_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/delivery_dashboard.css">
    <style>
        .history-table-card { padding: 0; overflow: hidden; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 16px 25px; font-size: 13px; font-weight: 700; color: var(--rider-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
        td { padding: 18px 25px; font-size: 14px; color: var(--rider-text-main); border-bottom: 1px solid #f8fafc; vertical-align: middle; }
        tr:hover td { background: #f8fafc; }
        .order-id { font-weight: 700; color: var(--rider-secondary); }
        .status-badge { padding: 6px 14px; border-radius: 99px; font-size: 12px; font-weight: 600; }
        .status-completed { background: #ecfdf5; color: #10b981; }
        .payout-val { font-weight: 700; color: #10b981; }
        .history-filters { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; }
        .filter-select { padding: 10px 16px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px; font-weight: 500; color: var(--rider-text-main); outline: none; transition: 0.3s; }
        .filter-select:focus { border-color: var(--rider-primary); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
    </style>
</head>
<body>

    <?php include "../../includes/components/delivery_sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="welcome-msg">
                <h1>Delivery History</h1>
                <p>Overview of your completed assignments</p>
            </div>
            
            <div class="header-actions">
                <div class="status-pill online">
                    <div class="status-dot"></div>
                    <span>Online</span>
                </div>
                <div class="notification-bell">
                    <i class="ri-notification-3-line"></i>
                </div>
            </div>
        </header>

        <div class="history-filters">
            <select class="filter-select">
                <option>All Deliveries</option>
                <option>Today</option>
                <option>This Week</option>
                <option>This Month</option>
            </select>
            <select class="filter-select">
                <option>All Types</option>
                <option>Food</option>
                <option>Medicine</option>
            </select>
        </div>

        <div class="rider-panel history-table-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer / Store</th>
                            <th>Type</th>
                            <th>Date & Time</th>
                            <th>Payout</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="order-id">#ORD-9821</td>
                            <td>
                                <strong>Rabin Sharma</strong>
                                <p style="font-size: 11px; color: #64748b; margin: 2px 0 0;">City Pharmacy &rarr; Koteshwor</p>
                            </td>
                            <td><span style="font-size: 11px; background: #eff6ff; color: #3b82f6; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Medicine</span></td>
                            <td>20 Mar, 10:15 AM</td>
                            <td class="payout-val">Rs. 120.00</td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                        </tr>
                        <tr>
                            <td class="order-id">#ORD-9818</td>
                            <td>
                                <strong>Megha Rai</strong>
                                <p style="font-size: 11px; color: #64748b; margin: 2px 0 0;">The Burger House &rarr; Jhamsikhel</p>
                            </td>
                            <td><span style="font-size: 11px; background: #fff1f2; color: #f43f5e; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Food</span></td>
                            <td>20 Mar, 09:40 AM</td>
                            <td class="payout-val">Rs. 180.00</td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                        </tr>
                        <tr>
                            <td class="order-id">#ORD-9815</td>
                            <td>
                                <strong>Patan Dhoka Restro</strong>
                                <p style="font-size: 11px; color: #64748b; margin: 2px 0 0;">Patan &rarr; Lalitpur</p>
                            </td>
                            <td><span style="font-size: 11px; background: #fff1f2; color: #f43f5e; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Food</span></td>
                            <td>19 Mar, 08:30 PM</td>
                            <td class="payout-val">Rs. 210.00</td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                        </tr>
                        <tr>
                            <td class="order-id">#ORD-9812</td>
                            <td>
                                <strong>Anil Kapali</strong>
                                <p style="font-size: 11px; color: #64748b; margin: 2px 0 0;">Fresh Corner &rarr; Jawalakhel</p>
                            </td>
                            <td><span style="font-size: 11px; background: #fff1f2; color: #f43f5e; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Food</span></td>
                            <td>19 Mar, 07:15 PM</td>
                            <td class="payout-val">Rs. 150.00</td>
                            <td><span class="status-badge status-completed">Completed</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
