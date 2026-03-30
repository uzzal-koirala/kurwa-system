<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$current_page = 'food_orders';
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];

// Fetch canteens (managed in restaurants table) linked to user's hospital
$hospital_id = $_SESSION['hospital_id'];
$canteens_sql = "SELECT id, name, image_url, status, rating, opening_time, closing_time, 'Canteen' as type, '15-20 min' as delivery_time 
                FROM restaurants 
                WHERE status = 'active' AND hospital_id = $hospital_id 
                ORDER BY rating DESC";
$canteens_res = $conn->query($canteens_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Orders | Kurwa</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/food_order.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <style>
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
            font-size: 24px;
            color: #3542f3;
        }

        /* Checkout Modal CSS */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            width: 100%;
            max-width: 480px;
            padding: 30px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalSlide 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalSlide {
            from { transform: translateY(40px) scale(0.95); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .close-modal {
            position: absolute;
            right: 24px;
            top: 24px;
            font-size: 28px;
            cursor: pointer;
            color: #94a3b8;
            transition: 0.2s;
            line-height: 1;
        }

        .close-modal:hover {
            color: #ef4444;
            transform: rotate(90deg);
        }

        /* Cart Sidebar CSS */
        .cart-sidebar-overlay {
            display: none;
            position: fixed;
            z-index: 9998;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .cart-sidebar-overlay.show {
            opacity: 1;
        }

        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            max-width: 420px;
            height: 100%;
            background: white;
            z-index: 9999;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
            transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
        }

        .cart-sidebar.open {
            right: 0;
        }

        .cart-header {
            padding: 25px 30px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-body {
            padding: 20px 30px;
            flex: 1;
            overflow-y: auto;
        }

        .cart-footer {
            padding: 20px 30px 30px;
            border-top: 1px solid #e2e8f0;
            background: #fff;
        }

        /* Header Action Buttons */
        .hdr-action-btn {
            position: relative;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            color: #475569;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hdr-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .hdr-action-btn.primary {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .hdr-action-btn.primary:hover {
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
            transform: translateY(-2px);
        }

        .hdr-cart-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: white;
            font-size: 11px;
            font-weight: 700;
            height: 20px;
            min-width: 20px;
            padding: 0 5px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
            transform: scale(0);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 2;
        }

        .hdr-cart-badge.show {
            transform: scale(1);
        }

        /* Empty vector */
        .empty-cart-icon-container {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 1), 0 10px 20px rgba(0, 0, 0, 0.04);
            border: 4px solid #ffffff;
        }
        
        .empty-cart-icon-container i {
            font-size: 42px;
            background: linear-gradient(135deg, #3542f3 0%, #4361ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .item-note-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            color: #475569;
            background: #f8fafc;
            transition: all 0.2s ease;
        }
        
        .item-note-input:focus {
            outline: none;
            border-color: #3542f3;
            box-shadow: 0 0 0 3px rgba(53, 66, 243, 0.15);
            background: #ffffff;
        }

        .closed-vendor { 
            opacity: 0.7; 
            filter: grayscale(0.5); 
            cursor: not-allowed !important; 
            pointer-events: auto !important; 
        }
        .closed-vendor * { pointer-events: none; }
        .canteen-status.status-closed { background: #ef4444; }
        .canteen-status.status-open { background: #22c55e; }

        /* Store Closed Modal CSS */
        .closed-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        .closed-modal-card {
            background: white;
            border-radius: 32px;
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.2);
            transform: scale(0.9);
            animation: cardPop 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardPop {
            to { transform: scale(1); opacity: 1; }
        }

        .closed-icon-ctn {
            width: 90px;
            height: 90px;
            background: #fff7ed;
            color: #f97316;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 25px;
            border: 2px solid #ffedd5;
            position: relative;
        }

        .closed-icon-ctn::after {
            content: '';
            position: absolute;
            inset: -8px;
            border: 2px solid #fff7ed;
            border-radius: 50%;
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        .closed-time-badge {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 20px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
            font-weight: 600;
            color: #475569;
        }

        .closed-btn {
            background: #0f172a;
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 16px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: 0.3s;
        }

        .closed-btn:hover {
            background: #334155;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <button class="mobile-menu-btn" id="openSidebar" type="button">
        <i class="ri-menu-line"></i>
    </button>

    <div class="food-order-container">
        
        <!-- View 1: Canteen Selection -->
        <div id="canteenSelection">
            <div class="order-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h1><i class="ri-restaurant-fill"></i> Order Delicious Food</h1>
                <div style="display: flex; gap: 15px;">
                    <button type="button" class="hdr-action-btn primary" onclick="openCheckout()" title="View Cart">
                        <i class="ri-shopping-cart-2-line"></i>
                        <span class="hdr-cart-badge" id="hdrCartBadge1">0</span>
                    </button>
                    <button type="button" class="hdr-action-btn" onclick="window.location.href='my_orders.php'" title="My Orders">
                        <i class="ri-shopping-bag-3-line"></i>
                    </button>
                </div>
            </div>

            <div class="search-area">
                <div class="search-box">
                    <i class="ri-search-line"></i>
                    <input type="text" id="foodSearch" placeholder="Search for food or restaurants..." oninput="handleSearch()">
                </div>
            </div>

            <div class="canteens-grid" id="canteensGrid">
                <?php 
                $canteens_res->data_seek(0);
                while($canteen = $canteens_res->fetch_assoc()): 
                    $curr_time = date('H:i:s');
                    $open = $canteen['opening_time'];
                    $close = $canteen['closing_time'];
                    
                    if ($open <= $close) {
                        $is_open = ($curr_time >= $open && $curr_time <= $close);
                    } else {
                        $is_open = ($curr_time >= $open || $curr_time <= $close);
                    }
                    
                    $status_text = $is_open ? 'Open' : 'Closed';
                    $status_class = $is_open ? 'status-open' : 'status-closed';
                    $open_fmt = date('h:i A', strtotime($open));
                    $close_fmt = date('h:i A', strtotime($close));
                    $click_action = $is_open ? "loadMenu({$canteen['id']}, '" . addslashes($canteen['name']) . "')" : "showClosedModal(this)";
                ?>
                <div class="canteen-card <?= !$is_open ? 'closed-vendor' : '' ?>" 
                     data-name="<?= strtolower(htmlspecialchars($canteen['name'])) ?>" 
                     data-full-name="<?= htmlspecialchars($canteen['name']) ?>"
                     data-open-time="<?= $open_fmt ?>"
                     data-close-time="<?= $close_fmt ?>"
                     onclick="<?= $click_action ?>">
                    <div class="canteen-img-wrapper">
                        <?php 
                            $img = !empty($canteen['image_url']) ? '../../'.$canteen['image_url'] : 'https://images.unsplash.com/photo-1517248135467-4c7ed9d42339?w=500&q=80';
                        ?>
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($canteen['name']) ?>">
                        <span class="canteen-badge"><?= htmlspecialchars($canteen['type']) ?></span>
                        <span class="canteen-status <?= $status_class ?>"><?= $status_text ?></span>
                    </div>
                    <div class="canteen-info">
                        <h3><?= htmlspecialchars($canteen['name']) ?></h3>
                        <p class="canteen-tags">Fast Food • Healthy • Quick Delivery</p>
                        <div class="canteen-meta">
                            <span class="meta-item rating"><i class="ri-star-fill"></i> <?= $canteen['rating'] ?></span>
                            <span class="meta-item time"><i class="ri-history-line"></i> <?= $canteen['delivery_time'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- View 2: Menu Browser (Hidden by default) -->
        <div id="menuView">
            <div class="menu-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="back-btn" onclick="showCanteens()">
                        <i class="ri-arrow-left-line"></i>
                    </div>
                    <div class="menu-title-area">
                        <h2 id="currentCanteenName" style="margin: 0; font-size: 20px;">Canteen Name</h2>
                        <p id="canteenMetaInfo" style="margin: 0; font-size: 12px; color: #64748b;">Rating: 4.8 • Healthy & Fresh</p>
                    </div>
                </div>
                <div style="display: flex; gap: 15px;">
                    <button type="button" class="hdr-action-btn primary" onclick="openCheckout()" title="View Cart">
                        <i class="ri-shopping-cart-2-line"></i>
                        <span class="hdr-cart-badge" id="hdrCartBadge2">0</span>
                    </button>
                    <button type="button" class="hdr-action-btn" onclick="window.location.href='my_orders.php'" title="My Orders">
                        <i class="ri-shopping-bag-3-line"></i>
                    </button>
                </div>
            </div>

            <div class="loading-spinner" id="menuLoader">
                <i class="ri-loader-4-line ri-spin"></i>
            </div>

            <div class="foods-grid" id="foodItemsContainer">
                <!-- Food Items will be injected here via JS -->
            </div>
        </div>

    </div>
</div>

<!-- Floating Cart Indicator -->
<div class="cart-float" id="cartIndicator" onclick="alert('Proceeding to Checkout...')">
    <i class="ri-shopping-basket-fill"></i>
    <span id="cartCount">0 Items</span>
    <span class="divider">|</span>
    <span id="cartTotal">Rs. 0</span>
</div>

<!-- Store Closed Modal -->
<div id="closedModal" class="closed-modal-overlay">
    <div class="closed-modal-card">
        <div class="closed-icon-ctn">
            <i class="ri-time-line"></i>
        </div>
        <h2 id="closedStoreName" style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Canteen Closed</h2>
        <p style="color: #64748b; font-size: 15px; line-height: 1.5;">This canteen is currently off-duty and not accepting new orders at this moment.</p>
        
        <div class="closed-time-badge">
            <i class="ri-calendar-todo-line" style="color: #f97316;"></i>
            <span>Hours: <span id="closedOperatingHours">08:00 AM - 10:00 PM</span></span>
        </div>

        <button onclick="closeClosedModal()" class="closed-btn">Understood</button>
    </div>
</div>

<!-- Cart Sidebar overlay -->
<div id="cartOverlay" class="cart-sidebar-overlay" onclick="closeCheckout()"></div>

<!-- Cart Sidebar -->
<div id="cartSidebar" class="cart-sidebar">
    <div class="cart-header">
        <h2 style="margin:0; color:#0f172a; display: flex; align-items: center; gap: 8px;"><i class="ri-shopping-basket-fill" style="color:#3542f3;"></i> Cart</h2>
        <span class="close-modal" onclick="closeCheckout()" style="position: static; font-size: 28px;">&times;</span>
    </div>
    
    <div class="cart-body">
        <div id="checkoutItems" style="margin-bottom: 20px;"></div>
    </div>
    
    <div class="cart-footer">
        <div class="price-summary" style="background:#f8fafc; padding:15px; border-radius:12px; margin-bottom:15px;">
            <div style="display:flex; justify-content:space-between; font-weight:700; font-size:18px; color:#1e293b;">
                <span>Total Amount:</span>
                <span id="checkoutTotal">Rs. 0</span>
            </div>
        </div>

        <form id="checkoutForm" onsubmit="submitOrder(event)">
            <div style="display:flex; flex-direction:column; gap:8px; margin-bottom: 20px;">
                <label style="font-weight:600; font-size:14px; color:#475569; display: flex; justify-content: space-between; align-items: center;">
                    Delivery Address
                    <button type="button" onclick="getCurrentLocation()" style="background:#f1f5f9; color:#3542f3; border:none; padding:4px 8px; border-radius:6px; cursor:pointer; font-weight:600; font-size:11px;"><i class="ri-map-pin-user-fill"></i> Get Location</button>
                </label>
                <div id="checkoutMap" style="height: 140px; border-radius: 12px; margin-bottom: 4px; border: 1px solid #e2e8f0; z-index: 1;"></div>
                <input type="hidden" id="deliveryLat">
                <input type="hidden" id="deliveryLng">
                <input type="text" id="deliveryAddress" placeholder="Apt 4B, 123 Main St..." style="padding:12px; border:1px solid #e2e8f0; border-radius:10px; font-family:inherit;">
            </div>
                <!-- Overall Special Notes Removed: User requested per-item notes only -->
                <input type="hidden" id="specialNotes" value="">
            <button type="submit" style="width:100%; background:#3542f3; color:#fff; border:none; padding:16px; border-radius:12px; font-weight:700; cursor:pointer; font-size:16px; box-shadow: 0 4px 15px rgba(53, 66, 243, 0.3);">
                <i class="ri-checkbox-circle-line"></i> Place Order
            </button>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="modal-content" style="text-align:center; padding: 40px 20px; max-width: 400px;">
        <div style="width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);">
            <i class="ri-checkbox-circle-fill"></i>
        </div>
        <h2 style="color: #0f172a; margin-top: 0; margin-bottom: 10px; font-weight: 700; font-size: 24px;">Order Placed!</h2>
        <p style="color: #64748b; margin-bottom: 25px; line-height: 1.5;">Your delicious food is being prepared by the restaurant. You can track its live status in your order history.</p>
        <button type="button" onclick="window.location.href='my_orders.php'" style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); color: #fff; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 600; cursor: pointer; width: 100%; font-size: 15px; box-shadow: 0 4px 15px rgba(53, 66, 243, 0.3); transition: 0.3s;">
            <i class="ri-map-pin-user-line" style="vertical-align: middle; margin-right: 5px;"></i> Track My Order
        </button>
        <button type="button" onclick="document.getElementById('successModal').style.display='none'" style="background: transparent; color: #64748b; border: 1px solid #e2e8f0; padding: 14px 30px; border-radius: 12px; margin-top: 10px; font-weight: 600; cursor: pointer; width: 100%; font-size: 15px; transition: 0.3s;">
            Continue Browsing
        </button>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../../assets/js/sidebar.js"></script>
<script>
    let cart = JSON.parse(localStorage.getItem('food_cart')) || [];
    let currentCanteenId = parseInt(localStorage.getItem('food_canteen_id')) || 0;
    let currentFoodItems = [];
    let currentView = 'canteens';
    let checkoutMap = null;
    let checkoutMarker = null;
    function reverseGeocode(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    const shortAddress = data.display_name.split(',').slice(0, 3).join(', ');
                    document.getElementById('deliveryAddress').value = shortAddress;
                }
            })
            .catch(err => console.log('Reverse geocoding failed', err));
    }

    function initCheckoutMap() {
        if(checkoutMap) {
            setTimeout(() => checkoutMap.invalidateSize(), 300);
            return;
        }
        
        const defaultLat = 27.7172;
        const defaultLng = 85.3240;
        
        checkoutMap = L.map('checkoutMap').setView([defaultLat, defaultLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(checkoutMap);

        const customIcon = L.divIcon({
            className: 'custom-map-marker',
            html: '<div style="background:#3542f3; color:white; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 6px rgba(0,0,0,0.3); border:2px solid white;"><i class="ri-map-pin-user-fill"></i></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });

        checkoutMarker = L.marker([defaultLat, defaultLng], {icon: customIcon, draggable: true}).addTo(checkoutMap);
        
        document.getElementById('deliveryLat').value = defaultLat;
        document.getElementById('deliveryLng').value = defaultLng;

        checkoutMarker.on('dragend', function(e) {
            const position = checkoutMarker.getLatLng();
            document.getElementById('deliveryLat').value = position.lat;
            document.getElementById('deliveryLng').value = position.lng;
            reverseGeocode(position.lat, position.lng);
        });

        checkoutMap.on('click', function(e) {
            checkoutMarker.setLatLng(e.latlng);
            document.getElementById('deliveryLat').value = e.latlng.lat;
            document.getElementById('deliveryLng').value = e.latlng.lng;
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });
        
        setTimeout(() => checkoutMap.invalidateSize(), 300);
    }

    function getCurrentLocation() {
        const btn = document.querySelector('[onclick="getCurrentLocation()"]');
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Locating...';

        const setLocation = (lat, lng) => {
            checkoutMap.setView([lat, lng], 16);
            checkoutMarker.setLatLng([lat, lng]);
            document.getElementById('deliveryLat').value = lat;
            document.getElementById('deliveryLng').value = lng;
            btn.innerHTML = '<i class="ri-map-pin-user-fill"></i> Get Location';
            reverseGeocode(lat, lng);
        };

        const fallbackLocation = () => {
            fetch('https://ipapi.co/json/')
                .then(res => res.json())
                .then(data => {
                    if (data.latitude && data.longitude) {
                        setLocation(data.latitude, data.longitude);
                    } else {
                        throw new Error("No IP location Data");
                    }
                })
                .catch(err => {
                    alert("Could not pull location automatically. Please select manually on map.");
                    btn.innerHTML = '<i class="ri-map-pin-user-fill"></i> Get Location';
                });
        };

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => setLocation(position.coords.latitude, position.coords.longitude),
                (error) => fallbackLocation(), 
                { timeout: 5000, enableHighAccuracy: true }
            );
        } else {
            fallbackLocation();
        }
    }

    // Initialize UI on page load
    window.onload = function() {
        updateCartUI();
    };

    function handleSearch() {
        const query = document.getElementById('foodSearch').value.toLowerCase().trim();
        
        if (currentView === 'canteens') {
            const cards = document.querySelectorAll('.canteen-card');
            cards.forEach(card => {
                const name = card.dataset.name;
                if (name.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        } else {
            const filteredFoods = currentFoodItems.filter(food => 
                food.name.toLowerCase().includes(query) || 
                food.description.toLowerCase().includes(query)
            );
            renderFoods(filteredFoods);
        }
    }

    function loadMenu(canteenId, name) {
        currentView = 'menu';
        
        

        currentCanteenId = canteenId;
        localStorage.setItem('food_canteen_id', canteenId);
        
        document.getElementById('canteenSelection').style.display = 'none';
        document.getElementById('menuView').style.display = 'block';
        document.getElementById('currentCanteenName').innerText = name;
        document.getElementById('foodItemsContainer').innerHTML = '';
        document.getElementById('menuLoader').style.display = 'block';
        document.getElementById('foodSearch').value = ''; // Clear search when switching view

        // Fetch food items
        fetch(`handlers/fetch_menu.php?canteen_id=${canteenId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('menuLoader').style.display = 'none';
                currentFoodItems = data;
                renderFoods(data);
            })
            .catch(err => {
                console.error('Error fetching menu:', err);
                document.getElementById('menuLoader').innerHTML = 'Error loading menu. Please try again.';
            });
    }

    function renderFoods(foods) {
        const container = document.getElementById('foodItemsContainer');
        if (foods.length === 0) {
            container.innerHTML = `<p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #94a3b8;">No food items match your search.</p>`;
            return;
        }

        container.innerHTML = '';
        foods.forEach(food => {
            const card = `
                <div class="food-card">
                    <img src="${food.image_url}" alt="${food.name}" class="food-img-sm">
                    <div class="food-details">
                        <h4>${food.name}</h4>
                        <p class="food-desc">${food.description}</p>
                        <div class="food-price-cta">
                            <span class="price-tag">Rs. ${parseFloat(food.price).toLocaleString()}</span>
                            <button class="add-btn" onclick="addToCart(${food.id}, '${food.name.replace(/'/g, "\\'")}', ${food.price}, '${food.image_url}')">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    }

    function showCanteens() {
        currentView = 'canteens';
        document.getElementById('menuView').style.display = 'none';
        document.getElementById('canteenSelection').style.display = 'block';
        document.getElementById('foodSearch').value = ''; // Clear search when returning
        
        // Reset canteen cards display
        document.querySelectorAll('.canteen-card').forEach(card => card.style.display = 'flex');
    }

    function addToCart(id, name, price, image = '') {
        // Check if item already exists
        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({id, name, price, quantity: 1, image});
        }
        updateCartUI();
        
        // Simple bounce effect
        const cartFloat = document.getElementById('cartIndicator');
        cartFloat.style.display = 'flex';
        cartFloat.style.transform = 'scale(1.1)';
        cartFloat.onclick = openCheckout;
        setTimeout(() => cartFloat.style.transform = 'scale(1)', 200);
    }

    function updateCartUI() {
        localStorage.setItem('food_cart', JSON.stringify(cart));
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);
        const total = cart.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
        
        if (count > 0) {
            document.getElementById('cartCount').innerText = `${count} ${count === 1 ? 'Item' : 'Items'}`;
            document.getElementById('cartTotal').innerText = `Rs. ${total.toLocaleString()}`;
            document.getElementById('cartIndicator').style.display = 'flex';
            document.getElementById('cartIndicator').onclick = openCheckout;
            
            // Update header badges
            ['hdrCartBadge1', 'hdrCartBadge2'].forEach(id => {
                const badge = document.getElementById(id);
                if (badge) {
                    badge.innerText = count;
                    badge.classList.add('show');
                }
            });
        } else {
            document.getElementById('cartIndicator').style.display = 'none';
            // Hide header badges
            ['hdrCartBadge1', 'hdrCartBadge2'].forEach(id => {
                const badge = document.getElementById(id);
                if (badge) {
                    badge.classList.remove('show');
                }
            });
        }
    }

    function openCheckout() {
        const overlay = document.getElementById('cartOverlay');
        const sidebar = document.getElementById('cartSidebar');
        overlay.style.display = 'block';
        setTimeout(() => overlay.classList.add('show'), 10);
        sidebar.classList.add('open');
        
        const container = document.getElementById('checkoutItems');
        const footer = document.querySelector('.cart-footer');
        
        if(cart.length === 0) {
            container.innerHTML = `
                <div style="text-align:center; padding: 60px 20px;">
                    <div class="empty-cart-icon-container">
                        <i class="ri-shopping-cart-2-fill"></i>
                    </div>
                    <h3 style="color:#1e293b; margin-bottom:10px; font-weight: 700; font-size: 18px;">Your cart is empty</h3>
                    <p style="color:#64748b; font-size:14px; margin-bottom:30px; line-height: 1.5;">Looks like you haven't added any delicious food yet.</p>
                    <button onclick="closeCheckout()" style="background:#f1f5f9; color:#475569; border:none; padding:14px 28px; border-radius:12px; font-weight:600; cursor:pointer; transition:0.3s; width: 100%;">Continue Browsing</button>
                </div>
            `;
            footer.style.display = 'none';
            return;
        }
        
        footer.style.display = 'block';
        container.innerHTML = '';
        cart.forEach((item, index) => {
            const itemTotal = parseFloat(item.price) * item.quantity;
            const fallbackImg = item.image ? item.image : '../../assets/images/placeholder.jpg';
            
            container.innerHTML += `
            <div style="display:flex; align-items:center; gap: 15px; margin-bottom:12px; padding: 12px; background: #fff; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); transition: 0.2s; flex-wrap: wrap;">
                <img src="${fallbackImg}" alt="${item.name}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 12px; background: #f8fafc;">
                
                <div style="flex: 1; min-width: 100px;">
                    <span style="font-weight:600; font-size:15px; color:#0f172a; display:block; margin-bottom: 4px;">${item.name}</span>
                    <strong style="color:#3542f3; font-size: 14px;">Rs. ${itemTotal.toLocaleString()}</strong>
                </div>
                
                <div style="display:flex; flex-direction: column; align-items:flex-end; gap:8px;">
                    <button type="button" onclick="removeFromCart(${index})" style="background:transparent; color:#ef4444; border:none; padding:4px; font-size: 18px; cursor:pointer; display:flex; align-items:center; justify-content:center; opacity: 0.7; transition: 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                        <i class="ri-close-circle-fill"></i>
                    </button>
                    
                    <div style="display:flex; align-items:center; background:#f8fafc; border-radius:8px; padding: 2px;">
                        <button type="button" onclick="updateQuantity(${index}, -1)" style="background:transparent; border:none; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor:pointer; font-weight:700; color:#475569; font-size:16px; border-radius: 6px; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='transparent'">-</button>
                        <span style="width:24px; text-align:center; font-weight:600; font-size:13px; color: #1e293b;">${item.quantity}</span>
                        <button type="button" onclick="updateQuantity(${index}, 1)" style="background:#fff; border:none; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor:pointer; font-weight:700; color:#3542f3; font-size:16px; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">+</button>
                    </div>
                </div>
                <div style="flex-basis: 100%; margin-top: 4px;">
                    <input type="text" class="item-note-input" placeholder="Add note for ${item.name} (optional)..." value="${item.special_note || ''}" oninput="updateItemNote(${index}, this.value)" />
                </div>
            </div>`;
        });
        
        const total = cart.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
        document.getElementById('checkoutTotal').innerText = `Rs. ${total.toLocaleString()}`;
        
        // Init Map
        initCheckoutMap();
    }

    function updateItemNote(index, note) {
        cart[index].special_note = note;
        // Don't call updateCartUI immediately to avoid losing focus on input
        // Just save to local storage
        localStorage.setItem('food_cart', JSON.stringify(cart));
    }

    function updateQuantity(index, change) {
        if (cart[index].quantity + change > 0) {
            cart[index].quantity += change;
            updateCartUI();
            openCheckout(); // Re-render sidebar
        } else if (cart[index].quantity + change === 0) {
            removeFromCart(index); // Remove if quantity hits 0
        }
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        updateCartUI();
        openCheckout(); // Re-render sidebar
    }

    function closeCheckout() {
        const overlay = document.getElementById('cartOverlay');
        const sidebar = document.getElementById('cartSidebar');
        
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        setTimeout(() => overlay.style.display = 'none', 300);
        
        document.getElementById('deliveryAddress').value = '';
    }

    async function submitOrder(e) {
        e.preventDefault();
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Processing...';
        submitBtn.disabled = true;

        const address = document.getElementById('deliveryAddress').value;
        const notes = document.getElementById('specialNotes').value;
        const lat = document.getElementById('deliveryLat').value || null;
        const lng = document.getElementById('deliveryLng').value || null;
        const total = cart.reduce((sum, item) => sum + parseFloat(item.price), 0);
        
        const payload = {
            restaurant_id: currentCanteenId,
            delivery_address: address,
            special_notes: notes,
            delivery_lat: lat,
            delivery_lng: lng,
            total_amount: total,
            items: cart
        };
        
        try {
            const response = await fetch('handlers/place_food_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('successModal').style.display = 'flex';
                cart = [];
                updateCartUI();
                closeCheckout();
            } else {
                alert('Failed to place order: ' + result.message);
            }
        } catch (error) {
            console.error('Checkout error:', error);
            alert('An unexpected error occurred during checkout.');
        } finally {
            submitBtn.innerHTML = '<i class="ri-checkbox-circle-line"></i> Place Order';
            submitBtn.disabled = false;
        }
    }

    function showClosedModal(el) {
        const name = el.dataset.fullName;
        const openTime = el.dataset.openTime;
        const closeTime = el.dataset.closeTime;
        
        document.getElementById('closedStoreName').innerText = name + ' is Closed';
        document.getElementById('closedOperatingHours').innerText = `${openTime} - ${closeTime}`;
        document.getElementById('closedModal').style.display = 'flex';
    }

    function closeClosedModal() {
        document.getElementById('closedModal').style.display = 'none';
    }
</script>
</body>
</html>
