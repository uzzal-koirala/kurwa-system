<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

$current_page = "dashboard"; 
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name']; 

// Fetch current user details including balance
$user_data = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$user_balance = $user_data['balance'] ?? 0.00;
$user_name = $user_data['full_name'] ?? "User";

// 1. Fetch Stats as requested
// "Total Kurwa" (Total Caretakers)
$kurwa_stats = $conn->query("SELECT COUNT(*) as count FROM caretakers")->fetch_assoc();
// "Total Pharmacy" (Total Pharmacies)
$pharmacy_stats = $conn->query("SELECT COUNT(*) as count FROM pharmacies")->fetch_assoc();
// "Total Canteen" (Total Canteens)
$canteen_stats = $conn->query("SELECT COUNT(*) as count FROM canteens")->fetch_assoc();
// "Total Doctor" (Hardcoded as per request)
$doctor_count = 10;

// 2. Recent Activity (Unified Service History)
$recent_activity_sql = "
    (SELECT 'Caretaker' as category, b.created_at as date_in, c.full_name as name, b.status 
     FROM caretaker_bookings b 
     JOIN caretakers c ON b.caretaker_id = c.id 
     WHERE b.user_id = $user_id)
    UNION ALL
    (SELECT 'Food' as category, o.order_date as date_in, c.name as name, o.status 
     FROM food_orders o 
     JOIN canteens c ON o.canteen_id = c.id 
     WHERE o.user_id = $user_id)
    UNION ALL
    (SELECT 'Pharmacy' as category, m.created_at as date_in, p.name as name, m.status 
     FROM medicine_orders m 
     JOIN pharmacies p ON m.pharmacy_id = p.id 
     WHERE m.user_id = $user_id)
    ORDER BY date_in DESC LIMIT 5";
$recent_activity = $conn->query($recent_activity_sql);
if (!$recent_activity) {
    $recent_activity = null;
    error_log("Dashboard recent_activity query failed: " . $conn->error);
}

// 3. Dynamic Service Updates (Latest system additions)
$latest_pharmacy_res = $conn->query("SELECT name FROM pharmacies ORDER BY id DESC LIMIT 1");
$latest_pharmacy = $latest_pharmacy_res ? $latest_pharmacy_res->fetch_assoc() : ['name' => 'N/A'];
$latest_caretaker_res = $conn->query("SELECT full_name FROM caretakers ORDER BY id DESC LIMIT 1");
$latest_caretaker = $latest_caretaker_res ? $latest_caretaker_res->fetch_assoc() : ['full_name' => 'N/A'];
$latest_canteen_res = $conn->query("SELECT name FROM canteens ORDER BY id DESC LIMIT 1");
$latest_canteen = $latest_canteen_res ? $latest_canteen_res->fetch_assoc() : ['name' => 'N/A'];

// 4. Upcoming Appointments
$upcoming_appointments = $conn->query("
    SELECT b.*, c.full_name, c.specialization, c.image_url 
    FROM caretaker_bookings b 
    JOIN caretakers c ON b.caretaker_id = c.id 
    WHERE b.user_id = $user_id AND b.start_date >= CURDATE() 
    ORDER BY b.start_date ASC LIMIT 4");
if (!$upcoming_appointments) {
    $upcoming_appointments = null;
    error_log("Dashboard upcoming_appointments query failed: " . $conn->error);
}

// 4. Chart Data (Real System Activity)
$chart_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'];
$chart_values = [];
$year = date('Y');

foreach ($chart_labels as $key => $label) {
    $month = $key + 1;
    $query = "
        SELECT 
            (SELECT COUNT(*) FROM caretaker_bookings WHERE user_id = $user_id AND MONTH(created_at) = $month AND YEAR(created_at) = $year) +
            (SELECT COUNT(*) FROM food_orders WHERE user_id = $user_id AND MONTH(order_date) = $month AND YEAR(order_date) = $year) +
            (SELECT COUNT(*) FROM medicine_orders WHERE user_id = $user_id AND MONTH(created_at) = $month AND YEAR(created_at) = $year) 
        as total_activity";
    $res = $conn->query($query);
    if ($res) {
        $result = $res->fetch_assoc();
        $chart_values[] = (int)($result['total_activity'] ?? 0);
    } else {
        $chart_values[] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurwa Dashboard | Medical Excellence</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<?php include INC_PATH . '/components/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    
    <!-- Global Header Section -->
    <?php 
    $page_title = "Hi " . explode(' ', $user_name)[0] . ",";
    $page_subtitle = "How is your patient today?";
    include INC_PATH . "/components/user_header.php"; 
    ?>

    <div class="dashboard-container">
        <!-- Middle Column Content -->
        <div class="main-column">
            
            <!-- Three Stat Cards -->
            <div class="stats-grid">
                <a href="caretaker.php" class="stat-link">
                    <div class="stat-card">
                        <div class="stat-icon pink"><i class="ri-heart-3-line"></i></div>
                        <div class="stat-details">
                            <span class="label">Total Kurwa</span>
                            <span class="value"><?= $kurwa_stats['count'] ?></span>
                        </div>
                    </div>
                </a>
                <a href="medicine_orders.php" class="stat-link">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="ri-capsule-line"></i></div>
                        <div class="stat-details">
                            <span class="label">Total Pharmacy</span>
                            <span class="value"><?= $pharmacy_stats['count'] ?></span>
                        </div>
                    </div>
                </a>
                <a href="food_orders.php" class="stat-link">
                    <div class="stat-card">
                        <div class="stat-icon teal"><i class="ri-restaurant-line"></i></div>
                        <div class="stat-details">
                            <span class="label">Total Canteen</span>
                            <span class="value"><?= $canteen_stats['count'] ?></span>
                        </div>
                    </div>
                </a>
                <a href="#" class="stat-link">
                    <div class="stat-card">
                        <div class="stat-icon purple"><i class="ri-nurse-line"></i></div>
                        <div class="stat-details">
                            <span class="label">Total Doctor</span>
                            <span class="value"><?= $doctor_count ?></span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="content-grid">
                <!-- Activity Chart -->
                <div class="card-box">
                    <div class="card-title-row">
                        <h3>System Activity</h3>
                        <select style="border:none; color:var(--text-muted); font-size:12px; font-weight:600;">
                            <option>This Week</option>
                        </select>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>

                <!-- Reports Feed -->
                <div class="card-box">
                    <div class="card-title-row">
                        <h3>Service Updates</h3>
                        <i class="ri-equalizer-line" style="color:var(--text-muted);"></i>
                    </div>
                    <div class="reports-list">
                        <div class="report-item info">
                            <div class="report-icon"><i class="ri-megaphone-line"></i></div>
                            <div class="report-text">
                                <h5><?= $latest_pharmacy ? htmlspecialchars($latest_pharmacy['name']) : 'New Pharmacy' ?> added nearby</h5>
                                <p>Latest Addition</p>
                            </div>
                        </div>
                        <div class="report-item warning">
                            <div class="report-icon"><i class="ri-shield-user-line"></i></div>
                            <div class="report-text">
                                <h5>Staff verified: <?= $latest_caretaker ? htmlspecialchars($latest_caretaker['full_name']) : 'New Expert' ?></h5>
                                <p>Background check complete</p>
                            </div>
                        </div>
                        <div class="report-item error">
                            <div class="report-icon"><i class="ri-error-warning-line"></i></div>
                            <div class="report-text">
                                <h5>Canteen Alert: <?= $latest_canteen ? htmlspecialchars($latest_canteen['name']) : 'System' ?></h5>
                                <p>Maintenance scheduled</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="table-card">
                <div class="card-title-row">
                    <h3>Recent Transactions</h3>
                    <i class="ri-more-2-line" style="color:var(--text-muted);"></i>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Date</th>
                            <th>Service Provider</th>
                            <th>Category</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_activity && $recent_activity->num_rows > 0): $i = 1; while($row = $recent_activity->fetch_assoc()): ?>
                        <tr>
                            <td><?= sprintf("%02d", $i++) ?></td>
                            <td><?= date('m/d/y', strtotime($row['date_in'])) ?></td>
                            <td style="font-weight:700;"><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['category'] ?></td>
                            <td>
                                <span class="status-badge <?= (strtolower($row['status']) === 'confirmed' || strtolower($row['status']) === 'pending') ? 'status-'.strtolower($row['status']) : 'status-pending' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center; color:#94a3b8; padding: 20px;">No recent activity yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Side Panel -->
        <aside class="side-panel">
            <!-- New Balance Widget -->
            <div class="balance-card">
                <div class="balance-info">
                    <span class="label">Total Balance</span>
                    <h2 class="amount">Rs. <?= number_format($user_balance, 2) ?></h2>
                </div>
                <div class="balance-actions">
                    <a href="payments.php" class="topup-btn"><i class="ri-add-line"></i> Top Up</a>
                    <a href="payments.php" class="history-btn"><i class="ri-history-line"></i> History</a>
                </div>
            </div>

            <div class="calendar-widget">
                <div class="calendar-header">
                    <h3>Nepali Calendar</h3>
                </div>
                <!-- Hamro Patro Calendar Widget -->
                <div class="calendar-wrapper" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <div style="text-align:center; display: flex; justify-content: center; min-width: 295px;">
                        <iframe src="https://www.hamropatro.com/widgets/calender-medium.php" frameborder="0" scrolling="no" marginwidth="0" marginheight="0" style="border:none; overflow:hidden; width:295px; height:385px; border-radius: 12px;" allowtransparency="true"></iframe>
                    </div>
                </div>
            </div>

            <div class="appoint-section">
                <div class="card-title-row" style="margin-bottom:20px;">
                    <h3>Service Appointments</h3>
                </div>
                <div class="appoint-list">
                    <?php 
                    $count = 0;
                    if ($upcoming_appointments && $upcoming_appointments->num_rows > 0):
                    while($app = $upcoming_appointments->fetch_assoc()): 
                        $count++;
                    ?>
                    <div class="appoint-card <?= $count == 2 ? 'active' : '' ?>">
                        <div class="appoint-icon">
                            <i class="ri-user-heart-line"></i>
                        </div>
                        <div class="appoint-info">
                            <h4><?= htmlspecialchars($app['specialization']) ?></h4>
                            <span><?= date('M d', strtotime($app['start_date'])) ?> • <?= htmlspecialchars(explode(' ', $app['full_name'])[0]) ?></span>
                        </div>
                        <i class="ri-arrow-right-s-line" style="margin-left:auto;"></i>
                    </div>
                    <?php endwhile; endif; ?>
                    
                    <?php if($count == 0): ?>
                    <p style="font-size:12px; color:var(--text-muted); text-align:center;">No upcoming appointments.</p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                data: <?= json_encode($chart_values) ?>,
                backgroundColor: '#3b82f6',
                borderRadius: 5,
                barThickness: 12,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { display: true, color: '#f1f5f9' },
                    ticks: { color: '#a3aed0', font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#a3aed0', font: { size: 10 } }
                }
            }
        }
    });
</script>

<script src="../../assets/js/sidebar.js"></script>

</body>
</html>
