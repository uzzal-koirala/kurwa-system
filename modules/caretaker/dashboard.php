<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['caretaker_id'])) {
    header("Location: login.php");
    exit;
}

$caretaker_id = $_SESSION['caretaker_id'];
$caretaker_name = $_SESSION['caretaker_name'];
$current_page = 'dashboard';

// Fetch caretaker details
$caretaker = $conn->query("SELECT * FROM caretakers WHERE id = $caretaker_id")->fetch_assoc();

// Fetch stats
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM caretaker_bookings WHERE caretaker_id = $caretaker_id")->fetch_assoc()['count'];
$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM caretaker_bookings WHERE caretaker_id = $caretaker_id AND status = 'pending'")->fetch_assoc()['count'];
$total_earnings = $conn->query("SELECT SUM(total_price) as total FROM caretaker_bookings WHERE caretaker_id = $caretaker_id AND status = 'completed'")->fetch_assoc()['total'] ?? 0;
$avg_rating = $caretaker['rating'] ?? 0;

// Fetch active duty (current booking)
$active_duty = $conn->query("
    SELECT b.*, u.full_name as user_name, u.phone as user_phone 
    FROM caretaker_bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.caretaker_id = $caretaker_id AND b.status = 'confirmed' AND b.start_date <= CURDATE() AND b.end_date >= CURDATE()
    LIMIT 1")->fetch_assoc();

// Fetch upcoming requests
$requests = $conn->query("
    SELECT b.*, u.full_name as user_name 
    FROM caretaker_bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.caretaker_id = $caretaker_id AND b.status = 'pending' 
    ORDER BY b.created_at DESC LIMIT 3");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinical Hub | Caretaker</title>
    <link rel="stylesheet" href="../../assets/css/caretaker_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/caretaker_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="caretaker-body">

<?php include '../../includes/components/caretaker_sidebar.php'; ?>

<div class="main-content">
    <div class="dashboard-header">
        <div class="header-left">
            <div class="search-bar">
                <i class="ri-search-line"></i>
                <input type="text" placeholder="Quick Patient Search...">
                <button>Add Patient</button>
            </div>
            <div class="nav-links">
                <span class="active">TREATMENTS</span>
                <span>DOCTORS</span>
                <span>HELP</span>
            </div>
        </div>
        <div class="header-right">
            <i class="ri-global-line"></i>
            <i class="ri-message-3-line"></i>
            <div style="position: relative;">
                <i class="ri-notification-3-line"></i>
                <span style="position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; background: #ff4757; border-radius: 50%; border: 2px solid white;"></span>
            </div>
        </div>
    </div>

    <div class="page-title">
        <h1 style="font-size: 14px; font-weight: 800; color: #1b2559; opacity: 0.8;">DASHBOARD</h1>
    </div>

    <div class="stats-row">
        <div class="stat-card-clinic">
            <div class="stat-clinic-icon" style="background: #ff5c8e;">
                <i class="ri-user-heart-line"></i>
            </div>
            <div class="details">
                <p>Total Patients</p>
                <h3>2.040</h3>
            </div>
        </div>
        <div class="stat-card-clinic">
            <div class="stat-clinic-icon" style="background: #ff9f43;">
                <i class="ri-shield-user-line"></i>
            </div>
            <div class="details">
                <p>Total Missions</p>
                <h3><?= $total_bookings ?></h3>
            </div>
        </div>
        <div class="stat-card-clinic">
            <div class="stat-clinic-icon" style="background: #2ed573;">
                <i class="ri-pulse-line"></i>
            </div>
            <div class="details">
                <p>Trust Level</p>
                <h3><?= $avg_rating ?>/5.0</h3>
            </div>
        </div>
        <div class="stat-card-clinic">
            <div class="stat-clinic-icon" style="background: #341f97;">
                <i class="ri-money-dollar-circle-line"></i>
            </div>
            <div class="details">
                <p>Total Revenue</p>
                <h3>Rs. <?= number_format($total_earnings, 0) ?></h3>
            </div>
        </div>
    </div>

    <div class="clincal-grid">
        <div class="main-stats-col">
            <!-- Patient Statistics Line Chart -->
            <div class="content-card">
                <div class="card-header">
                    <h3>PATIENT STATISTICS</h3>
                    <div style="display: flex; gap: 15px; font-size: 11px; font-weight: 700;">
                        <span style="cursor: pointer; color: #a3aed0;">YEAR</span>
                        <span style="cursor: pointer; color: #a3aed0;">MONTH</span>
                        <span style="cursor: pointer; color: #4361ee;">WEEK</span>
                    </div>
                </div>
                <div style="display: flex; gap: 30px; align-items: stretch;">
                    <div style="flex: 1; height: 250px;">
                        <canvas id="clinicChart"></canvas>
                    </div>
                    <div style="width: 200px; background: #4361ee; border-radius: 12px; padding: 30px 20px; color: white; text-align: center; display: flex; flex-direction: column; justify-content: center;">
                        <p style="font-size: 11px; font-weight: 600; opacity: 0.9; margin-bottom: 8px;">Total Patients</p>
                        <h2 style="font-size: 42px; font-weight: 800; margin-bottom: 25px;">120</h2>
                        <div style="height: 60px;">
                            <canvas id="miniWaveChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest Patients Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3>LATEST PATIENTS</h3>
                    <i class="ri-equalizer-line" style="color: #a3aed0; cursor: pointer;"></i>
                </div>
                <div style="overflow-x: auto;">
                    <table class="clinic-table">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>DATE</th>
                                <th>ID</th>
                                <th>NAME</th>
                                <th>AGE</th>
                                <th>COUNTRY</th>
                                <th>GENDER</th>
                                <th>SETTINGS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $dummy_patients = [
                                ['01', '22/02/2021', 'GH-224536', 'William Zabka', '24', 'SINGAPORE', 'MALE'],
                                ['02', '22/02/2021', 'GH-224537', 'Thomas Shelby', '21', 'USA', 'MALE'],
                                ['03', '22/02/2021', 'GH-224538', 'Bobby Singer', '34', 'INDONESIA', 'MALE'],
                            ];
                            foreach($dummy_patients as $p): ?>
                            <tr>
                                <td><?= $p[0] ?></td>
                                <td><?= $p[1] ?></td>
                                <td style="color: #a3aed0;"><?= $p[2] ?></td>
                                <td><?= $p[3] ?></td>
                                <td><?= $p[4] ?></td>
                                <td><?= $p[5] ?></td>
                                <td style="color: #a3aed0;"><?= $p[6] ?></td>
                                <td style="display: flex; gap: 10px;">
                                    <i class="ri-pencil-line" style="color: #a3aed0; cursor: pointer;"></i>
                                    <i class="ri-delete-bin-line" style="color: #ff4757; cursor: pointer;"></i>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="sidebar-stats-col">
            <!-- Reports Widget -->
            <div class="content-card">
                <div class="card-header">
                    <h3>REPORTS</h3>
                    <i class="ri-more-2-line" style="color: #a3aed0; cursor: pointer;"></i>
                </div>
                <div class="reports-list">
                    <div class="report-item">
                        <div class="report-meta">
                            <div style="width: 40px; height: 40px; background: #eef2ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #5c7cfa; font-size: 18px;">
                                <i class="ri-stethoscope-line"></i>
                            </div>
                            <div class="details">
                                <h5>Cardiac Checkup</h5>
                                <p>1 minute ago</p>
                            </div>
                        </div>
                        <div class="report-action">DETAILS <i class="ri-arrow-right-s-line"></i></div>
                    </div>
                    <div class="report-item">
                        <div class="report-meta">
                            <div style="width: 40px; height: 40px; background: #fff5f5; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #ff6b6b; font-size: 18px;">
                                <i class="ri-test-tube-line"></i>
                            </div>
                            <div class="details">
                                <h5>Blood Test Result</h5>
                                <p>5 minutes ago</p>
                            </div>
                        </div>
                        <div class="report-action">DETAILS <i class="ri-arrow-right-s-line"></i></div>
                    </div>
                    <div class="report-item">
                        <div class="report-meta">
                            <div style="width: 40px; height: 40px; background: #f0fdf4; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #22c55e; font-size: 18px;">
                                <i class="ri-lungs-line"></i>
                            </div>
                            <div class="details">
                                <h5>Oxygen Saturation</h5>
                                <p>12 minutes ago</p>
                            </div>
                        </div>
                        <div class="report-action">DETAILS <i class="ri-arrow-right-s-line"></i></div>
                    </div>
                </div>
            </div>

            <!-- Balance/Income Widget -->
            <div class="content-card">
                <div class="card-header">
                    <h3>BALANCE</h3>
                    <i class="ri-more-2-line" style="color: #a3aed0; cursor: pointer;"></i>
                </div>
                <div class="balance-item">
                    <div class="balance-meta">
                        <div class="balance-icon income">
                            <i class="ri-arrow-down-line"></i>
                        </div>
                        <div class="balance-info">
                            <h4>Income</h4>
                            <p>Rs. 120,000</p>
                        </div>
                    </div>
                    <div style="width: 80px; height: 40px;">
                        <canvas id="incomeSpark"></canvas>
                    </div>
                </div>
                <div class="balance-item">
                    <div class="balance-meta">
                        <div class="balance-icon outcome">
                            <i class="ri-arrow-up-line"></i>
                        </div>
                        <div class="balance-info">
                            <h4>Outcome</h4>
                            <p>Rs. 80,000</p>
                        </div>
                    </div>
                    <div style="width: 80px; height: 40px;">
                        <canvas id="outcomeSpark"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Main Performance Chart (Teal/Clinic Theme)
    const clinicCtx = document.getElementById('clinicChart').getContext('2d');
    const clinicGradient = clinicCtx.createLinearGradient(0, 0, 0, 250);
    clinicGradient.addColorStop(0, 'rgba(67, 97, 238, 0.2)');
    clinicGradient.addColorStop(1, 'rgba(67, 97, 238, 0)');

    new Chart(clinicCtx, {
        type: 'line',
        data: {
            labels: ['20', '21', '22', '23', '24', '25', '26'],
            datasets: [{
                data: [3100, 3500, 3200, 4200, 3100, 3800, 4100],
                borderColor: '#4361ee',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                backgroundColor: clinicGradient,
                pointRadius: 4,
                pointBackgroundColor: '#4361ee',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#f1f5f9' }, border: { display: false }, ticks: { color: '#a3aed0', font: { size: 10 } } },
                x: { grid: { display: false }, border: { display: false }, ticks: { color: '#a3aed0', font: { size: 10 } } }
            }
        }
    });

    // Mini Wave Chart in the Teal Box
    const miniCtx = document.getElementById('miniWaveChart').getContext('2d');
    new Chart(miniCtx, {
        type: 'line',
        data: {
            labels: [1, 2, 3, 4, 5, 6],
            datasets: [{
                data: [5, 15, 8, 20, 12, 25],
                borderColor: 'rgba(255, 255, 255, 0.8)',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 0,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });

    // Income Sparkline
    new Chart(document.getElementById('incomeSpark'), {
        type: 'line',
        data: { labels: [1,2,3,4,5], datasets: [{ data: [15,35,22,45,28], borderColor: '#4361ee', borderWidth: 2, pointRadius: 0, fill: false, tension: 0.4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
    });

    // Outcome Sparkline
    new Chart(document.getElementById('outcomeSpark'), {
        type: 'line',
        data: { labels: [1,2,3,4,5], datasets: [{ data: [40,20,35,15,25], borderColor: '#ff6b6b', borderWidth: 2, pointRadius: 0, fill: false, tension: 0.4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
    });
</script>
<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
