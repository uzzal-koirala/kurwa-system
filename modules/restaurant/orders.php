<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['restaurant_id'])) {
    header("Location: login.php");
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];
$restaurant_name = $_SESSION['restaurant_name'] ?? 'Restaurant Partner';
$current_page = 'orders';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['new_status']);
    
    $update = $conn->prepare("UPDATE restaurant_orders SET status = ? WHERE id = ? AND restaurant_id = ?");
    $update->bind_param("sii", $new_status, $order_id, $restaurant_id);
    if ($update->execute()) {
        header("Location: orders.php?success=1");
        exit;
    }
}

// Fetch Orders
$query = "SELECT ro.*, u.full_name as customer_name, u.phone as customer_phone 
          FROM restaurant_orders ro 
          JOIN users u ON ro.user_id = u.id 
          WHERE ro.restaurant_id = $restaurant_id 
          ORDER BY ro.created_at DESC";
$orders = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Restaurant Partner</title>
    <link rel="stylesheet" href="../../assets/css/restaurant_sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--rest-secondary-dark);
            margin: 0;
            letter-spacing: -0.5px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px 50px;
            transition: all 0.3s ease;
        }

        .content-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
        }

        /* Order Table View */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            text-align: left;
            padding: 15px 20px;
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        .orders-table th:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        .orders-table th:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

        .orders-table td {
            padding: 20px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #eef2ff;
            color: var(--rest-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 800;
        }

        .customer-details h4 { margin: 0; font-size: 14px; font-weight: 700; color: var(--text-main); }
        .customer-details p { margin: 0; font-size: 12px; color: var(--text-muted); }

        .order-id { font-family: monospace; font-weight: 700; color: var(--rest-primary); background: var(--rest-primary-light); padding: 4px 8px; border-radius: 6px; font-size: 13px; }

        .amount { font-weight: 800; font-size: 15px; color: var(--text-main); }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge.pending { background: #fffbeb; color: #f59e0b; }
        .status-badge.preparing { background: #eff6ff; color: #3b82f6; }
        .status-badge.completed { background: #f0fdf4; color: #22c55e; }
        .status-badge.cancelled { background: #fef2f2; color: #ef4444; }

        /* Action Select */
        .status-select {
            padding: 8px 12px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            background: var(--white);
            font-family: inherit;
            font-weight: 600;
            color: var(--text-main);
            font-size: 13px;
            cursor: pointer;
            outline: none;
            transition: 0.2s;
        }

        .status-select:focus { border-color: var(--rest-primary); }

        .btn-update {
            padding: 8px 16px;
            border-radius: 10px;
            background: var(--rest-primary);
            color: white;
            border: none;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-update:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(255, 126, 95, 0.3); }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state i { font-size: 60px; color: #e2e8f0; margin-bottom: 20px; }
        .empty-state h3 { font-size: 18px; color: var(--text-main); margin: 0 0 10px 0; font-weight: 700; }

        @media (max-width: 1024px) {
            .main-content { padding: 20px; margin-left: 0; }
            .orders-table { display: block; overflow-x: auto; white-space: nowrap; }
            .mobile-toggle { display: block !important; }
        }
    </style>
</head>
<body class="restaurant-body">

<?php include '../../includes/components/restaurant_sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    <div class="page-header">
        <div class="flex items-center gap-4">
            <i class="ri-menu-line mobile-toggle" id="openSidebarUniversal" style="font-size: 26px; color: var(--rest-secondary-dark); cursor: pointer; display: none;"></i>
            <div>
                <h1 class="page-title">Orders Management</h1>
                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">View and update the status of your customer orders.</p>
            </div>
        </div>
        <div class="header-actions">
            <!-- Refresh Button -->
            <button onclick="window.location.reload()" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 20px; cursor: pointer; color: var(--rest-secondary-dark); transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px;">
                <i class="ri-refresh-line"></i> Refresh
            </button>
        </div>
    </div>

    <div class="content-card">
        <?php if($orders && $orders->num_rows > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date & Time</th>
                        <th>Amount</th>
                        <th>Current Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($o = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><span class="order-id">#ORD-<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
                        <td>
                            <div class="customer-info">
                                <div class="customer-avatar"><?= strtoupper(substr($o['customer_name'], 0, 1)) ?></div>
                                <div class="customer-details">
                                    <h4><?= htmlspecialchars($o['customer_name']) ?></h4>
                                    <p><i class="ri-phone-line"></i> <?= htmlspecialchars($o['customer_phone'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main); font-size: 14px;"><?= date('M d, Y', strtotime($o['created_at'])) ?></div>
                            <div style="font-size: 12px; color: var(--text-muted);"><?= date('h:i A', strtotime($o['created_at'])) ?></div>
                        </td>
                        <td><span class="amount">Rs. <?= number_format($o['total_amount'], 2) ?></span></td>
                        <td>
                            <span class="status-badge <?= $o['status'] ?>">
                                <?php if($o['status'] === 'pending'): ?><i class="ri-time-line"></i>
                                <?php elseif($o['status'] === 'preparing'): ?><i class="ri-restaurant-line"></i>
                                <?php elseif($o['status'] === 'completed'): ?><i class="ri-check-line"></i>
                                <?php else: ?><i class="ri-close-circle-line"></i><?php endif; ?>
                                <?= ucfirst($o['status']) ?>
                            </span>
                        </td>
                        <td>
                            <form action="" method="POST" style="display: flex; gap: 10px;">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="new_status" class="status-select">
                                    <option value="pending" <?= $o['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="preparing" <?= $o['status'] === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                    <option value="completed" <?= $o['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="btn-update">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="ri-inbox-archive-line"></i>
                <h3>No Orders Yet</h3>
                <p>When customers place orders, they will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<?php if(isset($_GET['success'])): ?>
<script>
    Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Status Updated Correctly',
        showConfirmButton: false,
        timer: 1500,
        toast: true
    });
    
    // Clean URL
    window.history.replaceState({}, document.title, window.location.pathname);
</script>
<?php endif; ?>
</body>
</html>
