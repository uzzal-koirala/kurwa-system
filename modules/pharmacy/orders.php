<?php
session_start();
require_once '../../includes/core/config.php';

// Check if pharmacy is logged in
if (!isset($_SESSION['pharmacy_id'])) {
    header("Location: login.php");
    exit();
}

$pharmacy_id = $_SESSION['pharmacy_id'];
$current_page = 'orders';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    
    $update_sql = "UPDATE pharmacy_orders SET status = ? WHERE id = ? AND pharmacy_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sii", $new_status, $order_id, $pharmacy_id);
    
    if($stmt->execute()) {
        $_SESSION['msg'] = "Order #ORD-" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . " status updated to " . ucfirst(str_replace('_', ' ', $new_status));
    }
    header("Location: orders.php");
    exit();
}

// Fetch all orders with User details
$orders = [];
$orders_sql = "
    SELECT o.*, u.full_name, u.phone, u.email 
    FROM pharmacy_orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.pharmacy_id = ? 
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("i", $pharmacy_id);
$stmt->execute();
$orders_res = $stmt->get_result();

while ($row = $orders_res->fetch_assoc()) {
    // Fetch items for each order
    $items = [];
    $items_sql = "
        SELECT oi.*, m.name 
        FROM pharmacy_order_items oi
        JOIN medicines m ON oi.medicine_id = m.id
        WHERE oi.order_id = ?
    ";
    $item_stmt = $conn->prepare($items_sql);
    $item_stmt->bind_param("i", $row['id']);
    $item_stmt->execute();
    $items_res = $item_stmt->get_result();
    while ($i = $items_res->fetch_assoc()) $items[] = $i;
    
    $row['items'] = $items;
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Prescriptions & Orders | Kurwa Pharmacy</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/restaurant_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/pharmacy_dashboard.css">
    <style>
        .page-header {
            margin-bottom: 30px;
        }

        .alert-msg {
            background: #ecfdf5;
            color: #059669;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .orders-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .order-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            border: 1px solid rgba(15, 118, 110, 0.05);
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 24px;
        }

        /* Order Left: Customer Info */
        .order-customer {
            border-right: 1px solid #f1f5f9;
            padding-right: 20px;
        }

        .order-id {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .customer-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            background: #f0fdfa;
            color: #0d9488;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }

        .customer-details h4 {
            font-size: 15px;
            color: #1e293b;
            margin-bottom: 2px;
        }
        
        .customer-details p {
            font-size: 13px;
            color: #64748b;
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 13px;
            color: #475569;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .detail-row i {
            color: #0d9488;
            font-size: 16px;
        }

        /* Order Middle: Items list */
        .order-items {
            border-right: 1px solid #f1f5f9;
            padding-right: 20px;
        }

        .items-header {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 14px;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: 500;
            color: #334155;
        }

        .item-qty {
            color: #0d9488;
            font-weight: 700;
            background: #f0fdfa;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 2px solid #f1f5f9;
            font-weight: 700;
            font-size: 16px;
            color: #0f172a;
        }

        /* Order Right: Actions */
        .order-actions {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 16px;
        }

        .status-badge-lg {
            text-align: center;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .btn-update {
            width: 100%;
            padding: 12px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #334155;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-update:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        /* Dropdown actions */
        .status-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .status-select {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: #334155;
            background: #f8fafc;
            cursor: pointer;
            outline: none;
        }

        .status-select:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5,150,105,0.1);
        }

        .btn-save {
            background: #059669;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-save:hover {
            background: #047857;
        }

        @media (max-width: 1024px) {
            .order-card {
                grid-template-columns: 1fr;
            }
            .order-customer, .order-items {
                border-right: none;
                border-bottom: 1px solid #f1f5f9;
                padding-right: 0;
                padding-bottom: 20px;
            }
        }
    </style>
</head>
<body>

    <?php include '../../includes/components/pharmacy_sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="page-header">
            <h1 style="font-size:28px; font-weight:700; color:#0f172a; margin-bottom:4px; letter-spacing:-0.5px;">Prescription Orders</h1>
            <p style="color:#64748b; font-size:15px;">Monitor and fulfill incoming medicine requests.</p>
        </div>

        <?php if(isset($_SESSION['msg'])): ?>
            <div class="alert-msg">
                <i class="ri-checkbox-circle-fill" style="font-size:20px;"></i>
                <?= $_SESSION['msg'] ?>
                <?php unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="orders-grid">
            <?php if(empty($orders)): ?>
                <div style="text-align:center; padding:60px 20px; background:white; border-radius:20px; border:1px dashed #cbd5e1;">
                    <i class="ri-inbox-archive-line" style="font-size:64px; color:#94a3b8; margin-bottom:16px; display:block;"></i>
                    <h3 style="color:#0f172a; margin-bottom:8px;">No Orders Yet</h3>
                    <p style="color:#64748b;">When patients order medicines, they will securely appear here.</p>
                </div>
            <?php else: ?>
                <?php foreach($orders as $order): ?>
                    <div class="order-card">
                        <!-- Left: Customer -->
                        <div class="order-customer">
                            <div class="order-id">
                                ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?>
                                <span style="display:block; font-size:12px; color:#94a3b8; font-weight:500; margin-top:4px;">
                                    <?= date('M d, Y - h:i A', strtotime($order['created_at'])) ?>
                                </span>
                            </div>
                            
                            <div class="customer-pill">
                                <div class="customer-avatar"><?= substr($order['full_name'], 0, 1) ?></div>
                                <div class="customer-details">
                                    <h4><?= htmlspecialchars($order['full_name']) ?></h4>
                                    <p>Patient</p>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <i class="ri-map-pin-line"></i>
                                <span><?= htmlspecialchars($order['delivery_address']) ?></span>
                            </div>
                            <?php if($order['phone']): ?>
                            <div class="detail-row">
                                <i class="ri-phone-line"></i>
                                <span><?= htmlspecialchars($order['phone']) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($order['prescription_url']): ?>
                            <div style="margin-top:16px; padding:12px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; display:inline-flex; align-items:center; gap:8px;">
                                <i class="ri-file-list-3-fill" style="color:#ef4444;"></i>
                                <a href="<?= htmlspecialchars($order['prescription_url']) ?>" target="_blank" style="color:#b91c1c; font-weight:600; font-size:13px; text-decoration:none;">View Prescription</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Middle: Items -->
                        <div class="order-items">
                            <div class="items-header">Medicine List (<?= count($order['items']) ?> Items)</div>
                            
                            <?php foreach($order['items'] as $item): ?>
                                <div class="item-row">
                                    <div>
                                        <span class="item-qty"><?= $item['quantity'] ?>x</span>
                                        <span class="item-name" style="margin-left:8px;"><?= htmlspecialchars($item['name']) ?></span>
                                    </div>
                                    <span style="color:#475569; font-weight:500;">Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="total-row">
                                <span>Total Amount Paid</span>
                                <span style="color:#059669;">Rs. <?= number_format($order['total_amount'], 2) ?></span>
                            </div>
                        </div>
                        
                        <!-- Right: Actions -->
                        <div class="order-actions">
                            <?php 
                                $status_bg = '#f1f5f9'; $status_color = '#475569';
                                if($order['status'] === 'pending') { $status_bg = '#fffbeb'; $status_color = '#b45309'; }
                                if($order['status'] === 'preparing') { $status_bg = '#eff6ff'; $status_color = '#1d4ed8'; }
                                if($order['status'] === 'out_for_delivery') { $status_bg = '#faf5ff'; $status_color = '#7e22ce'; }
                                if($order['status'] === 'completed') { $status_bg = '#f0fdf4'; $status_color = '#15803d'; }
                                if($order['status'] === 'cancelled') { $status_bg = '#fef2f2'; $status_color = '#b91c1c'; }
                            ?>
                            <div class="status-badge-lg" style="background:<?= $status_bg ?>; color:<?= $status_color ?>;">
                                <i class="ri-focus-3-line" style="margin-right:6px; vertical-align:middle;"></i>
                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                            </div>
                            
                            <form method="POST" class="status-form">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status" class="status-select">
                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                                    <option value="preparing" <?= $order['status'] === 'preparing' ? 'selected' : '' ?>>Preparing Packaging</option>
                                    <option value="out_for_delivery" <?= $order['status'] === 'out_for_delivery' ? 'selected' : '' ?>>Dispatched / Out for Delivery</option>
                                    <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Delivered & Completed</option>
                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancel Order</option>
                                </select>
                                <button type="submit" class="btn-save">Update Order Status</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
