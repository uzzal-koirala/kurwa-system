<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['caretaker_id'])) {
    header("Location: login.php");
    exit;
}

$caretaker_id = $_SESSION['caretaker_id'];
$caretaker_name = $_SESSION['caretaker_name'];
$current_page = 'schedule';

// Fetch caretaker details
$caretaker = $conn->query("SELECT * FROM caretakers WHERE id = $caretaker_id")->fetch_assoc();

// Fetch all bookings for the calendar
$bookings_query = $conn->prepare("
    SELECT b.*, u.full_name as user_name, u.phone as user_phone 
    FROM caretaker_bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.caretaker_id = ? 
    ORDER BY b.start_date ASC
");
$bookings_query->bind_param("i", $caretaker_id);
$bookings_query->execute();
$bookings_result = $bookings_query->get_result();

$events = [];
while ($row = $bookings_result->fetch_assoc()) {
    $color = '#4361ee'; // Default confirmed
    if ($row['status'] === 'pending') $color = '#ff9f43';
    elseif ($row['status'] === 'cancelled') $color = '#ff4757';
    elseif ($row['status'] === 'completed') $color = '#2ed573';

    $events[] = [
        'id' => $row['id'],
        'title' => $row['user_name'],
        'start' => $row['start_date'],
        'end' => date('Y-m-d', strtotime($row['end_date'] . ' +1 day')), // FullCalendar end date is exclusive
        'backgroundColor' => $color,
        'borderColor' => $color,
        'extendedProps' => [
            'status' => $row['status'],
            'phone' => $row['user_phone'],
            'price' => $row['total_price']
        ]
    ];
}

// Fetch upcoming bookings for the list view
$upcoming_query = $conn->prepare("
    SELECT b.*, u.full_name as user_name, u.phone as user_phone 
    FROM caretaker_bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.caretaker_id = ? AND b.status IN ('pending', 'confirmed') AND b.end_date >= CURDATE()
    ORDER BY b.start_date ASC
    LIMIT 5
");
$upcoming_query->bind_param("i", $caretaker_id);
$upcoming_query->execute();
$upcoming_bookings = $upcoming_query->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule | Caretaker</title>
    <link rel="stylesheet" href="../../assets/css/caretaker_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/caretaker_schedule.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FullCalendar CDN -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
</head>
<body class="caretaker-body">

<?php include '../../includes/components/caretaker_sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    <div class="dashboard-header">
        <div class="header-left" style="display: flex; align-items: center; gap: 15px;">
            <i class="ri-menu-2-line mobile-toggle" id="openSidebarUniversal" style="font-size: 24px; color: #1b2559; cursor: pointer; display: none;"></i>
            <h1 style="font-size: 16px; font-weight: 800; color: #1b2559; opacity: 0.8; margin: 0; text-transform: uppercase;">My Schedule</h1>
        </div>
        <div class="header-right">
            <i class="ri-global-line"></i>
            <i class="ri-message-3-line"></i>
            <div style="position: relative;">
                <i class="ri-notification-3-line"></i>
                <span class="notification-badge"></span>
            </div>
        </div>
    </div>

    <div class="schedule-grid">
        <div class="calendar-section">
            <div class="content-card">
                <div id='calendar'></div>
            </div>
        </div>

        <div class="upcoming-section">
            <div class="content-card">
                <div class="card-header">
                    <h3>Upcoming Jobs</h3>
                    <i class="ri-more-2-line"></i>
                </div>
                <div class="upcoming-list">
                    <?php if ($upcoming_bookings->num_rows > 0): ?>
                        <?php while($booking = $upcoming_bookings->fetch_assoc()): ?>
                            <div class="upcoming-item">
                                <div class="item-date">
                                    <span class="day"><?= date('d', strtotime($booking['start_date'])) ?></span>
                                    <span class="month"><?= date('M', strtotime($booking['start_date'])) ?></span>
                                </div>
                                <div class="item-info">
                                    <h4><?= htmlspecialchars($booking['user_name']) ?></h4>
                                    <p><i class="ri-calendar-event-line"></i> <?= date('d M', strtotime($booking['start_date'])) ?> - <?= date('d M', strtotime($booking['end_date'])) ?></p>
                                    <span class="status-tag <?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span>
                                </div>
                                <div class="item-action">
                                    <button class="view-btn"><i class="ri-arrow-right-s-line"></i></button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="ri-calendar-line"></i>
                            <p>No upcoming jobs found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="availability-card content-card">
                <div class="card-header">
                    <h3>Availability</h3>
                    <label class="switch-small">
                        <input type="checkbox" checked>
                        <span class="slider-small"></span>
                    </label>
                </div>
                <p>Status: <span style="color: #2ed573; font-weight: 600;">Active</span></p>
                <button class="update-availability-btn">Update Schedule</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            themeSystem: 'standard',
            events: <?= json_encode($events) ?>,
            eventClick: function(info) {
                // Show booking details (Could be a modal)
                alert('Booking for: ' + info.event.title + '\nStatus: ' + info.event.extendedProps.status);
            },
            height: 'auto',
            responsive: true
        });
        calendar.render();
    });
</script>
<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
