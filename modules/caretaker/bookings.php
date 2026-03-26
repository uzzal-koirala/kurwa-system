<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['caretaker_id'])) {
    header("Location: login.php");
    exit;
}

$caretaker_id = $_SESSION['caretaker_id'];
$caretaker_name = $_SESSION['caretaker_name'];
$current_page = 'bookings';

// Fetch caretaker details
$caretaker = $conn->query("SELECT * FROM caretakers WHERE id = $caretaker_id")->fetch_assoc();

// Fetch all bookings for the list view
$bookings_query = $conn->prepare("
    SELECT b.*, u.full_name as user_name, u.phone as user_phone 
    FROM caretaker_bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.caretaker_id = ? 
    ORDER BY b.start_date DESC
");
$bookings_query->bind_param("i", $caretaker_id);
$bookings_query->execute();
$bookings_result = $bookings_query->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Caretaker</title>
    <link rel="stylesheet" href="../../assets/css/caretaker_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/caretaker_bookings.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="caretaker-body">

<?php include '../../includes/components/caretaker_sidebar.php'; ?>

<div class="main-content">
    <div class="dashboard-header">
        <div class="header-left" style="display: flex; align-items: center; gap: 15px;">
            <h1 class="page-main-title">My Bookings</h1>
        </div>
        <div class="header-right">
            <div class="search-bar-mini">
                <i class="ri-search-line"></i>
                <input type="text" placeholder="Search bookings...">
            </div>
            <div class="header-icons">
                <i class="ri-notification-3-line"></i>
                <span class="notification-badge"></span>
            </div>
        </div>
    </div>

    <div class="bookings-container">
        <div class="bookings-header-stats">
            <div class="stat-box">
                <span class="label">Total Bookings</span>
                <span class="value"><?= $bookings_result->num_rows ?></span>
            </div>
            <div class="stat-box">
                <span class="label">Active This Month</span>
                <span class="value">4</span>
            </div>
        </div>

        <div class="bookings-list">
            <?php if ($bookings_result->num_rows > 0): ?>
                <?php while($booking = $bookings_result->fetch_assoc()): ?>
                    <div class="booking-card">
                        <div class="booking-user">
                            <div class="user-avatar">
                                <i class="ri-user-heart-line"></i>
                            </div>
                            <div class="user-details">
                                <h3><?= htmlspecialchars($booking['user_name']) ?></h3>
                                <p><i class="ri-phone-line"></i> <?= htmlspecialchars($booking['user_phone']) ?></p>
                            </div>
                        </div>

                        <div class="booking-period">
                            <div class="period-item">
                                <span class="label">Duration</span>
                                <div class="date-range">
                                    <div class="date-box">
                                        <span class="m"><?= date('M', strtotime($booking['start_date'])) ?></span>
                                        <span class="d"><?= date('d', strtotime($booking['start_date'])) ?></span>
                                    </div>
                                    <i class="ri-arrow-right-line"></i>
                                    <div class="date-box">
                                        <span class="m"><?= date('M', strtotime($booking['end_date'])) ?></span>
                                        <span class="d"><?= date('d', strtotime($booking['end_date'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="booking-meta">
                            <div class="meta-item">
                                <span class="label">Total Price</span>
                                <span class="price">Rs. <?= number_format($booking['total_price'], 0) ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="label">Status</span>
                                <span class="status-badge <?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span>
                            </div>
                        </div>

                        <div class="booking-actions">
                            <button class="action-btn chat" onclick="location.href='../user/chat.php'"><i class="ri-chat-3-line"></i> Chat</button>
                            <button class="action-btn details">Details</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="ri-calendar-event-line"></i>
                    </div>
                    <h2>No Bookings Yet</h2>
                    <p>When clients book your services, they will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
