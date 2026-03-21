<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['delivery_id'])) {
    header("Location: login.php");
    exit;
}

$delivery_id = $_SESSION['delivery_id'];
$delivery_name = $_SESSION['delivery_name'];
$current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Rider Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/delivery_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/delivery_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include '../../includes/components/delivery_sidebar.php'; ?>

<main class="main-content">
    <header class="dashboard-header">
        <div class="welcome-msg">
            <h1>Good Morning, <?= explode(' ', $delivery_name)[0] ?>!</h1>
            <p>You have 3 active assignments waiting today.</p>
        </div>
        
        <div class="header-actions">
            <div class="status-pill online" id="globalStatus" onclick="toggleGlobalStatus()">
                <div class="status-dot"></div>
                <span style="font-size: 12px; font-weight: 800; color: #10b981;" id="statusText">ONLINE</span>
            </div>
            <button style="width: 45px; height: 45px; border-radius: 14px; background: white; border: 1px solid #e2e8f0; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: var(--rider-shadow);">
                <i class="ri-notification-3-line text-xl"></i>
            </button>
        </div>
    </header>

    <div class="dashboard-grid">
        
        <!-- Left Side: Main Activity -->
        <div class="main-col">
            
            <!-- Metrics Row -->
            <div class="metrics-row">
                <div class="metric-card">
                    <div class="metric-icon m-teal"><i class="ri-check-double-line"></i></div>
                    <div class="metric-info">
                        <span class="m-label">Orders Today</span>
                        <p class="m-value">12</p>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon m-blue"><i class="ri-map-pin-2-line"></i></div>
                    <div class="metric-info">
                        <span class="m-label">Distance (KM)</span>
                        <p class="m-value">42.5</p>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon m-amber"><i class="ri-star-smile-fill"></i></div>
                    <div class="metric-info">
                        <span class="m-label">Rating</span>
                        <p class="m-value">4.92</p>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon m-rose"><i class="ri-wallet-3-line"></i></div>
                    <div class="metric-info">
                        <span class="m-label">Earnings</span>
                        <p class="m-value">Rs. 1,450</p>
                    </div>
                </div>
            </div>

            <!-- Performance Analytics -->
            <div class="rider-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2 class="panel-title" style="margin-bottom: 0;">Earnings Analytics</h2>
                    <select style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 6px 12px; font-size: 13px; font-weight: 600; color: #1e293b; outline: none; cursor: pointer; background: #f8fafc;">
                        <option>This Week</option>
                        <option>Last Week</option>
                    </select>
                </div>
                <div class="chart-box">
                    <canvas id="riderEarningsChart"></canvas>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="rider-panel">
                <h2 class="panel-title">Recent Completed Tasks</h2>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid #f1f5f9;">
                                <th style="padding: 12px 0; font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase;">ID</th>
                                <th style="padding: 12px 0; font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Destination</th>
                                <th style="padding: 12px 0; font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Time</th>
                                <th style="padding: 12px 0; font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase; text-align: right;">Payout</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 15px 0; font-weight: 700; font-size: 13px;">#ORD-9821</td>
                                <td style="padding: 15px 0; font-size: 13px; color: #1e293b; font-weight: 600;">Koteshwor-32, Kathmandu</td>
                                <td style="padding: 15px 0; font-size: 12px; color: #64748b;">15 Mins ago</td>
                                <td style="padding: 15px 0; font-weight: 800; color: #059669; text-align: right;">Rs. 120.00</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 15px 0; font-weight: 700; font-size: 13px;">#ORD-9815</td>
                                <td style="padding: 15px 0; font-size: 13px; color: #1e293b; font-weight: 600;">Patan Dhoka, Lalitpur</td>
                                <td style="padding: 15px 0; font-size: 12px; color: #64748b;">2 Hours ago</td>
                                <td style="padding: 15px 0; font-weight: 800; color: #059669; text-align: right;">Rs. 180.00</td>
                            </tr>
                            <tr>
                                <td style="padding: 15px 0; font-weight: 700; font-size: 13px;">#ORD-9810</td>
                                <td style="padding: 15px 0; font-size: 13px; color: #1e293b; font-weight: 600;">Baneshwor, Kathmandu</td>
                                <td style="padding: 15px 0; font-size: 12px; color: #64748b;">3 Hours ago</td>
                                <td style="padding: 15px 0; font-weight: 800; color: #059669; text-align: right;">Rs. 95.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>

        <!-- Right Side: Active Assignment & Tips -->
        <div class="side-col">
            
            <!-- Urgent Task Panel -->
            <div class="rider-panel active-task">
                <span class="task-id">Current Assignment #ORD-9921</span>
                <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 25px; line-height: 1.2;">Emergency Medicine Delivery</h3>
                
                <div class="route-stepper">
                    <div class="step">
                        <div class="step-dot"></div>
                        <div class="step-info">
                            <h4>City Pharmacy</h4>
                            <p>Pickup Zone • New Road</p>
                        </div>
                    </div>
                    <div class="step active">
                        <div class="step-dot"></div>
                        <div class="step-info">
                            <h4>Mr. Rabin Sharma</h4>
                            <p>Drop Zone • Koteshwor-32</p>
                        </div>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.05); border-radius: 16px; padding: 15px; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.6);">Est. Payout</span>
                        <span style="font-size: 18px; font-weight: 800; color: var(--rider-primary);">Rs. 180.00</span>
                    </div>
                </div>

                <button class="btn-complete" onclick="markDelivered()">
                    Mark Delivered <i class="ri-checkbox-circle-fill"></i>
                </button>
            </div>

            <!-- Notice / Tip Card -->
            <div class="rider-panel" style="background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%); border-color: rgba(16, 185, 129, 0.1);">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--rider-primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 15px; box-shadow: 0 8px 20px var(--rider-primary-glow);">
                    <i class="ri-lightbulb-flash-fill"></i>
                </div>
                <h4 style="font-size: 15px; font-weight: 800; color: #064e3b; margin-bottom: 8px;">Earn 20% Extra Today!</h4>
                <p style="font-size: 13px; color: #065f46; line-height: 1.6; font-weight: 500;">Heavy rain expected in Kathmandu. High demand surcharges are active across all sectors.</p>
            </div>

            <!-- Support Card -->
            <div class="rider-panel" style="margin-bottom: 0;">
                <h3 style="font-size: 15px; font-weight: 800; margin-bottom: 15px;">Rider Support</h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <button style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #f1f5f9; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 10px; cursor: pointer; transition: 0.2s;">
                        <i class="ri-customer-service-2-fill text-blue-500 text-lg"></i> Emergency Helpline
                    </button>
                    <button style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #f1f5f9; background: #f8fafc; color: #1e293b; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 10px; cursor: pointer; transition: 0.2s;">
                        <i class="ri-questionnaire-fill text-purple-500 text-lg"></i> Rider Help Center
                    </button>
                </div>
            </div>

        </div>

    </div>
</main>

<script>
    // Global Status Toggle
    function toggleGlobalStatus() {
        const pill = document.getElementById('globalStatus');
        const text = document.getElementById('statusText');
        const isOnline = pill.classList.contains('online');
        
        if (isOnline) {
            pill.classList.remove('online');
            text.innerText = 'OFFLINE';
            text.style.color = '#64748b';
            Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'You are now OFFLINE', showConfirmButton: false, timer: 2000 });
        } else {
            pill.classList.add('online');
            text.innerText = 'ONLINE';
            text.style.color = '#10b981';
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'You are now ONLINE', showConfirmButton: false, timer: 2000 });
        }
    }

    function markDelivered() {
        Swal.fire({
            title: 'Confirm Delivery?',
            text: "Have you safely handed over order #ORD-9921?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Yes, Delivered!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Success', 'Order completed. Rs. 180 added to your wallet!', 'success');
            }
        });
    }

    // Performance Chart
    const ctx = document.getElementById('riderEarningsChart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 300);
    grad.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
    grad.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Earnings (Rs)',
                data: [1200, 1850, 950, 2100, 1400, 3100, 1450],
                borderColor: '#10b981',
                backgroundColor: grad,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#10b981',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    titleFont: { family: 'Poppins', size: 12 },
                    bodyFont: { family: 'Poppins', size: 14, weight: '800' },
                    displayColors: false,
                    callbacks: { label: (c) => 'Rs. ' + c.parsed.y }
                }
            },
            scales: {
                y: { grid: { borderDash: [5, 5], color: '#f1f5f9', drawBorder: false }, ticks: { font: { family: 'Poppins', size: 11 }, color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { font: { family: 'Poppins', size: 11 }, color: '#94a3b8' } }
            }
        }
    });
</script>
</body>
</html>
