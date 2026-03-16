<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$current_page = 'support';
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];

// Fetch tickets
$tickets_sql = "SELECT * FROM support_tickets WHERE user_id = $user_id ORDER BY created_at DESC";
$tickets_res = $conn->query($tickets_sql);

// Handle new ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['raise_ticket'])) {
    $subject = $conn->real_escape_string($_POST['subject']);
    $type = $conn->real_escape_string($_POST['type']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $message = $conn->real_escape_string($_POST['message']);
    
    $insert_sql = "INSERT INTO support_tickets (user_id, subject, type, message, priority, status) 
                   VALUES ($user_id, '$subject', '$type', '$message', '$priority', 'pending')";
    
    if ($conn->query($insert_sql)) {
        header("Location: support.php?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Support | Kurwa</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/support.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../../includes/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <header class="dashboard-header">
        <div class="header-left">
            <h1>Support Center</h1>
        </div>
    </header>

    <main class="support-container">


        <!-- Support Stat Cards -->
        <div class="support-stats">
            <div class="s-card">
                <div class="s-icon purple"><i class="ri-ticket-2-line"></i></div>
                <div class="s-info">
                    <span class="label">Total Tickets</span>
                    <span class="value"><?= $tickets_res->num_rows ?></span>
                </div>
            </div>
            <div class="s-card">
                <div class="s-icon orange"><i class="ri-time-line"></i></div>
                <div class="s-info">
                    <span class="label">Pending Review</span>
                    <span class="value"><?= $conn->query("SELECT COUNT(*) FROM support_tickets WHERE user_id = $user_id AND status = 'pending'")->fetch_row()[0] ?></span>
                </div>
            </div>
            <div class="s-card">
                <div class="s-icon green"><i class="ri-checkbox-circle-line"></i></div>
                <div class="s-info">
                    <span class="label">Resolved Tickets</span>
                    <span class="value"><?= $conn->query("SELECT COUNT(*) FROM support_tickets WHERE user_id = $user_id AND status = 'resolved'")->fetch_row()[0] ?></span>
                </div>
            </div>
        </div>

        <div class="support-grid">
            <!-- Left: Raise Ticket Form -->
            <section class="card-box ticket-form-section">
                <div class="card-title-row">
                    <h3><i class="ri-add-box-line"></i> Raise New Ticket</h3>
                </div>
                <p style="color: var(--text-muted); font-size: 14px; margin-top: 10px;">Fill out the form below and we'll get back to you as soon as possible.</p>
                
                <form action="support.php" method="POST" class="ticket-form">
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" placeholder="e.g. Issue with Medicine Order" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Issue Type</label>
                            <select name="type">
                                <option value="technical">Technical Issue</option>
                                <option value="billing">Billing & Payment</option>
                                <option value="general" selected>General Inquiry</option>
                                <option value="feedback">Feedback</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <select name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Explain your problem</label>
                        <textarea name="message" rows="5" placeholder="Detail your issue here..." required></textarea>
                    </div>
                    <button type="submit" name="raise_ticket" class="submit-ticket-btn">
                        Submit Ticket <i class="ri-send-plane-fill"></i>
                    </button>
                </form>
            </section>

            <!-- Right: My Tickets List -->
            <section class="card-box tickets-list-section">
                <div class="card-title-row" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="ri-history-line"></i> Recent Tickets</h3>
                    <a href="#" class="view-all" style="font-size: 13px; font-weight: 600; color: var(--primary);">View History</a>
                </div>
                
                <div class="tickets-list">
                    <?php if ($tickets_res->num_rows > 0): ?>
                        <?php while($t = $tickets_res->fetch_assoc()): ?>
                            <div class="ticket-item">
                                <div class="ticket-main">
                                    <div class="t-badge <?= $t['priority'] ?>"><?= ucfirst($t['priority']) ?></div>
                                    <h4><?= htmlspecialchars($t['subject']) ?></h4>
                                    <p><?= substr(htmlspecialchars($t['message']), 0, 75) ?>...</p>
                                    <span class="date" style="font-size: 12px; color: var(--text-muted); display: block; margin-top: 8px;">
                                        <i class="ri-calendar-event-line"></i> <?= date('M d, Y', strtotime($t['created_at'])) ?>
                                    </span>
                                </div>
                                <div class="ticket-meta">
                                    <span class="status-pill <?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-tickets">
                            <i class="ri-inbox-line"></i>
                            <p>No tickets raised yet. If you need help, feel free to raise a new ticket.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
</div>

<script src="../../assets/js/sidebar.js"></script>

</body>
</html>
