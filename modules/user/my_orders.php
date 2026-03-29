<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$current_page = 'my_orders';
$user_id = $_SESSION['user_id'];

// Fetch all orders for the user
$orders_query = "
    SELECT 
        o.*, c.name AS restaurant_name, c.image_url AS restaurant_image,
        (
            SELECT GROUP_CONCAT(CONCAT(roi.quantity, 'x ', COALESCE(roi.item_name, 'Menu Item')) SEPARATOR ', ')
            FROM restaurant_order_items roi
            WHERE roi.order_id = o.id
        ) AS items_preview
    FROM restaurant_orders o
    LEFT JOIN canteens c ON o.restaurant_id = c.id
    WHERE o.user_id = $user_id
    ORDER BY o.created_at DESC
";
$orders_res = $conn->query($orders_query);

$active_orders = [];
$past_orders = [];

if ($orders_res && $orders_res->num_rows > 0) {
    while ($row = $orders_res->fetch_assoc()) {
        if (in_array(strtolower($row['status']), ['pending', 'preparing', 'out_for_delivery'])) {
            $active_orders[] = $row;
        } else {
            // delivered, cancelled, etc.
            $past_orders[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Kurwa</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/my_orders.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <button class="mobile-menu-btn" id="openSidebar" type="button">
        <i class="ri-menu-line"></i>
    </button>

    <div class="orders-container">
        
        <div class="page-header">
            <h1><i class="ri-shopping-bag-3-fill"></i> My Orders</h1>
            <p>Track your active food orders and view your purchase history.</p>
        </div>

        <div class="tabs-wrapper">
            <button class="tab-btn active" onclick="switchTab('active')">Active Orders (<span id="activeCount"><?= count($active_orders) ?></span>)</button>
            <button class="tab-btn" onclick="switchTab('past')">Order History (<span id="pastCount"><?= count($past_orders) ?></span>)</button>
        </div>

        <!-- Active Orders View -->
        <div id="activeView" class="orders-grid">
            <?php if (count($active_orders) === 0): ?>
                <div class="empty-state">
                    <i class="ri-restaurant-2-line"></i>
                    <h3>No Active Orders</h3>
                    <p>You don't have any food on the way. Go grab a bite!</p>
                </div>
            <?php else: ?>
                <?php foreach ($active_orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-meta">
                            <span class="order-id">Order #ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span>
                            <span class="order-date"><i class="ri-calendar-line"></i> <?= date('M d, Y • h:i A', strtotime($order['created_at'])) ?></span>
                        </div>
                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                            <i class="ri-loader-4-line ri-spin"></i> <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                        </span>
                    </div>

                    <div class="order-body">
                        <div class="restaurant-info">
                            <img src="<?= $order['restaurant_image'] ?: '../../assets/images/placeholder.jpg' ?>" alt="Restaurant" style="width:50px; height:50px; border-radius:12px; object-fit:cover;">
                            <div class="restaurant-details">
                                <h3><?= htmlspecialchars($order['restaurant_name'] ?: 'Unknown Kitchen') ?></h3>
                                <p style="font-weight:500; color:var(--text-main); margin-bottom: 4px; font-size: 13px;">
                                    <i class="ri-shopping-basket-line"></i> <?= htmlspecialchars($order['items_preview'] ?: 'Items loading...') ?>
                                </p>
                                <p><i class="ri-map-pin-line"></i> Delivery to: <?= htmlspecialchars($order['delivery_address']) ?></p>
                            </div>
                        </div>
                        <div class="order-total">
                            <span>Total Amount</span>
                            <strong>Rs. <?= number_format($order['total_amount'], 2) ?></strong>
                        </div>
                    </div>

                    <div class="order-footer">
                        <button class="btn-track" onclick="openTracker(<?= $order['id'] ?>, '<?= $order['status'] ?>', '<?= date('M d, Y h:i A', strtotime($order['created_at'])) ?>', <?= $order['total_amount'] ?>)">
                            <i class="ri-map-pin-user-line"></i> Track Order
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Past Orders View -->
        <div id="pastView" class="orders-grid" style="display:none;">
            <?php if (count($past_orders) === 0): ?>
                <div class="empty-state">
                    <i class="ri-history-line"></i>
                    <h3>No Order History</h3>
                    <p>Looks like you haven't completed any orders yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($past_orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-meta">
                            <span class="order-id">Order #ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span>
                            <span class="order-date"><i class="ri-calendar-line"></i> <?= date('M d, Y • h:i A', strtotime($order['created_at'])) ?></span>
                        </div>
                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                            <i class="<?= $order['status'] === 'delivered' ? 'ri-check-line' : 'ri-close-line' ?>"></i> 
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>

                    <div class="order-body">
                        <div class="restaurant-info">
                            <img src="<?= $order['restaurant_image'] ?: '../../assets/images/placeholder.jpg' ?>" alt="Restaurant" style="width:50px; height:50px; border-radius:12px; object-fit:cover;">
                            <div class="restaurant-details">
                                <h3><?= htmlspecialchars($order['restaurant_name'] ?: 'Unknown Kitchen') ?></h3>
                                <p style="font-weight:500; color:var(--text-main); margin-bottom: 4px; font-size: 13px;">
                                    <i class="ri-shopping-basket-line"></i> <?= htmlspecialchars($order['items_preview'] ?: 'Items loading...') ?>
                                </p>
                                <p><i class="ri-map-pin-line"></i> Delivered to: <?= htmlspecialchars($order['delivery_address']) ?></p>
                            </div>
                        </div>
                        <div class="order-total">
                            <span>Total Amount</span>
                            <strong>Rs. <?= number_format($order['total_amount'], 2) ?></strong>
                        </div>
                    </div>

                    <div class="order-footer">
                        <button class="btn-details" onclick="openTracker(<?= $order['id'] ?>, '<?= $order['status'] ?>', '<?= date('M d, Y h:i A', strtotime($order['created_at'])) ?>', <?= $order['total_amount'] ?>)">
                            <i class="ri-file-list-3-line"></i> View Receipt
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Tracker & Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2 id="modalOrderId">Order Details</h2>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;" id="modalOrderDate">Nov 28, 2026 • 12:30 PM</div>
            </div>
            <button class="close-modal" onclick="closeTracker()"><i class="ri-close-line"></i></button>
        </div>
        
        <div class="modal-body">
            <!-- Map Section -->
            <div id="trackerMapContainer" style="height: 220px; border-radius: 16px; margin-bottom: 20px; border: 1px solid #e2e8f0; display:none; background: #f8fafc; z-index:1;"></div>

            <!-- Timeline (Only shown for non-cancelled orders) -->
            <div class="tracker-container" id="timelineContainer">
                <div class="timeline">
                    <div class="timeline-progress" id="progressLine" style="width: 0%;"></div>
                    
                    <div class="timeline-step" id="step_pending">
                        <div class="step-icon"><i class="ri-file-list-3-line"></i></div>
                        <div class="step-label">Order Placed</div>
                    </div>
                    
                    <div class="timeline-step" id="step_preparing">
                        <div class="step-icon"><i class="ri-restaurant-line"></i></div>
                        <div class="step-label">Preparing Food</div>
                    </div>
                    
                    <div class="timeline-step" id="step_out_for_delivery">
                        <div class="step-icon"><i class="ri-ebike-2-line"></i></div>
                        <div class="step-label">Out for Delivery</div>
                    </div>
                    
                    <div class="timeline-step" id="step_delivered">
                        <div class="step-icon"><i class="ri-home-smile-line"></i></div>
                        <div class="step-label">Delivered</div>
                    </div>
                </div>
            </div>

            <!-- Receipt Info -->
            <div class="receipt-section">
                <div class="receipt-header">
                    <i class="ri-receipt-line"></i> Order Receipt
                </div>
                
                <div id="receiptItems">
                    <!-- Items injected here by AJAX -->
                    <div class="loader-ctn"><i class="ri-loader-4-line"></i></div>
                </div>

                <div class="receipt-total">
                    <span>Total Paid</span>
                    <strong id="modalTotalAmount">Rs. 0.00</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../../assets/js/sidebar.js"></script>
<script>
    let trackerMap = null;
    let riderMarker = null;
    let riderInterval = null;

    function switchTab(view) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('activeView').style.display = 'none';
        document.getElementById('pastView').style.display = 'none';
        
        if (view === 'active') {
            document.querySelector('.tab-btn[onclick="switchTab(\'active\')"]').classList.add('active');
            document.getElementById('activeView').style.display = 'grid';
        } else {
            document.querySelector('.tab-btn[onclick="switchTab(\'past\')"]').classList.add('active');
            document.getElementById('pastView').style.display = 'grid';
        }
    }

    function openTracker(orderId, status, dateStr, total) {
        document.getElementById('orderModal').classList.add('active');
        document.getElementById('modalOrderId').innerText = 'Order #ORD-' + orderId.toString().padStart(5, '0');
        document.getElementById('modalOrderDate').innerText = dateStr;
        document.getElementById('modalTotalAmount').innerText = 'Rs. ' + parseFloat(total).toLocaleString(undefined, {minimumFractionDigits: 2});
        
        // Setup Timeline
        const timeline = document.getElementById('timelineContainer');
        if (status.toLowerCase() === 'cancelled') {
            timeline.style.display = 'none';
        } else {
            timeline.style.display = 'block';
            updateTimeline(status.toLowerCase());
        }

        // Fetch Items
        const itemsContainer = document.getElementById('receiptItems');
        itemsContainer.innerHTML = '<div class="loader-ctn"><i class="ri-loader-4-line"></i></div>';
        
        fetch(`handlers/fetch_order_details.php?order_id=${orderId}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    let html = '';
                    data.items.forEach(item => {
                        let noteHtml = item.special_notes ? `<div style="font-size:11px; color:#ef4444; font-weight:600; margin-top:4px; padding:4px 8px; background:#fff5f5; border-radius:6px; display:inline-block;"><i class="ri-information-line"></i> Note: ${item.special_notes}</div>` : '';
                        html += `
                        <div class="receipt-item" style="flex-direction:column; align-items:flex-start; gap:0;">
                            <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                <div class="item-info">
                                    <span class="item-name">${item.name || 'Custom Item'}</span>
                                    <span class="item-meta">Qty: ${item.quantity} • Rs. ${parseFloat(item.price).toLocaleString()} each</span>
                                </div>
                                <span class="item-price">Rs. ${(item.quantity * item.price).toLocaleString()}</span>
                            </div>
                            ${noteHtml}
                        </div>`;
                    });
                    itemsContainer.innerHTML = html;
                    
                    // Render Map
                    const mapContainer = document.getElementById('trackerMapContainer');
                    if (status.toLowerCase() !== 'cancelled' && data.delivery_lat && data.delivery_lng) {
                        mapContainer.style.display = 'block';
                        initTrackerMap(data.delivery_lat, data.delivery_lng, status.toLowerCase());
                    } else {
                        mapContainer.style.display = 'none';
                    }
                } else {
                    itemsContainer.innerHTML = `<div style="padding:20px; text-align:center; color:#ef4444;">Failed to load items.</div>`;
                }
            })
            .catch(err => {
                itemsContainer.innerHTML = `<div style="padding:20px; text-align:center; color:#ef4444;">Connection error.</div>`;
            });
    }

    function initTrackerMap(lat, lng, status) {
        if(trackerMap) {
            trackerMap.remove();
        }
        
        trackerMap = L.map('trackerMapContainer').setView([lat, lng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(trackerMap);
        
        // Home Marker
        const homeIcon = L.divIcon({
            className: 'home-marker',
            html: '<div style="background:#3542f3; color:white; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 6px rgba(0,0,0,0.3); border:2px solid white;"><i class="ri-home-smile-fill" style="font-size:18px;"></i></div>',
            iconSize: [36, 36],
            iconAnchor: [18, 36]
        });
        L.marker([lat, lng], {icon: homeIcon}).addTo(trackerMap);
        
        // Rider Marker
        if (status === 'out_for_delivery' || status === 'preparing') {
            const riderIcon = L.divIcon({
                className: 'rider-marker',
                html: '<div style="background:#ef4444; color:white; width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.5); border:3px solid white;"><i class="ri-ebike-2-fill" style="font-size:24px;"></i></div>',
                iconSize: [44, 44],
                iconAnchor: [22, 22]
            });
            
            // Start rider some distance away
            let rLat = parseFloat(lat) - 0.008;
            let rLng = parseFloat(lng) - 0.008;
            riderMarker = L.marker([rLat, rLng], {icon: riderIcon}).addTo(trackerMap);
            
            // Adjust bounds to fit both
            const group = new L.featureGroup([L.marker([lat, lng]), riderMarker]);
            trackerMap.fitBounds(group.getBounds(), {padding: [30, 30]});
            
            // Simulate smooth rider movement towards home
            if (riderInterval) clearInterval(riderInterval);
            riderInterval = setInterval(() => {
                const latDiff = (lat - rLat) * 0.1;
                const lngDiff = (lng - rLng) * 0.1;
                rLat += latDiff;
                rLng += lngDiff;
                riderMarker.setLatLng([rLat, rLng]);
            }, 1500);
        }
        
        setTimeout(() => trackerMap.invalidateSize(), 300);
    }

    function closeTracker() {
        document.getElementById('orderModal').classList.remove('active');
        if (riderInterval) clearInterval(riderInterval);
    }

    function updateTimeline(status) {
        // Reset
        document.querySelectorAll('.timeline-step').forEach(s => {
            s.classList.remove('active', 'completed');
        });
        
        const line = document.getElementById('progressLine');
        
        let progress = 0;
        
        if (status === 'pending') {
            document.getElementById('step_pending').classList.add('active');
            progress = 0;
        } else if (status === 'preparing') {
            document.getElementById('step_pending').classList.add('completed');
            document.getElementById('step_preparing').classList.add('active');
            progress = 33;
        } else if (status === 'out_for_delivery') {
            document.getElementById('step_pending').classList.add('completed');
            document.getElementById('step_preparing').classList.add('completed');
            document.getElementById('step_out_for_delivery').classList.add('active');
            progress = 66;
        } else if (status === 'delivered') {
            document.getElementById('step_pending').classList.add('completed');
            document.getElementById('step_preparing').classList.add('completed');
            document.getElementById('step_out_for_delivery').classList.add('completed');
            document.getElementById('step_delivered').classList.add('completed');
            progress = 100;
        }
        
        line.style.width = progress + '%';
    }
</script>
</body>
</html>
