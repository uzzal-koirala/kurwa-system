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
    <title>Rider Dashboard | Kurwa</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/delivery_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/delivery_dashboard.css">
</head>
<body>

<?php include '../../includes/components/delivery_sidebar.php'; ?>

<main class="main-content">
    <header class="dashboard-header">
        <div>
            <h1 class="page-title">Good Morning, <?= explode(' ', $delivery_name)[0] ?>!</h1>
            <p style="color: #64748b; font-size: 14px;">You have 3 active assignments for today.</p>
        </div>
        
        <div class="status-card online" id="statusCard" onclick="toggleStatus()">
            <span id="statusText" style="font-size: 12px; font-weight: 800; color: #10b981;">ONLINE</span>
            <div class="toggle-switch"></div>
        </div>
    </header>

    <!-- Essential Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-emerald"><i class="ri-check-double-line"></i></div>
            <p class="stat-label">Orders Today</p>
            <p class="stat-value">12</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-blue"><i class="ri-map-pin-2-line"></i></div>
            <p class="stat-label">Distance (KM)</p>
            <p class="stat-value">42.5</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-amber"><i class="ri-star-smile-fill"></i></div>
            <p class="stat-label">Rider Rating</p>
            <p class="stat-value">4.92</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-rose"><i class="ri-wallet-3-line"></i></div>
            <p class="stat-label">Today's Earnings</p>
            <p class="stat-value">Rs. 1,450</p>
        </div>
    </div>

    <!-- Active High-Fidelity Task Card -->
    <div class="active-order-card">
        <div class="order-badge">URGENT MEDICINE DELIVERY</div>
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
            <div>
                <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0;">Assignment #ORD-9921</h2>
                <p style="color: #64748b; font-size: 13px; margin-top: 5px;">Picked up from City Pharmacy • 10 mins ago</p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;">Est. Payout</p>
                <p style="font-size: 18px; font-weight: 800; color: #10b981;">Rs. 180.00</p>
            </div>
        </div>

        <div class="route-info">
            <div class="stop-list">
                <div class="stop-item">
                    <div class="stop-dot pickup"></div>
                    <div class="stop-details">
                        <h4>City Pharmacy, New Road</h4>
                        <p>Pickup: 2x Insulin, 1x First Aid Kit</p>
                    </div>
                </div>
                <div class="stop-item">
                    <div class="stop-dot dropoff"></div>
                    <div class="stop-details">
                        <h4>Mr. Rabin Sharma</h4>
                        <p>Drop: Koteshwor-32, Near Shiva Temple</p>
                    </div>
                </div>
            </div>
            
            <div style="margin-left: auto;">
                <button class="btn-complete" onclick="markDelivered()">
                    Mark as Delivered <i class="ri-checkbox-circle-line ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Recent History Mini -->
    <div style="background: white; border-radius: 20px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 20px;">Recent Shifts</h3>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="ri-calendar-event-line text-lg text-blue-500"></i>
                    <div>
                        <p style="font-size: 13px; font-weight: 600;">Yesterday, 19 Mar</p>
                        <p style="font-size: 11px; color: #64748b;">14 Orders • 8.5 Hours</p>
                    </div>
                </div>
                <p style="font-weight: 800; color: #1e293b;">Rs. 2,100</p>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="ri-calendar-event-line text-lg text-blue-500"></i>
                    <div>
                        <p style="font-size: 13px; font-weight: 600;">Wednesday, 18 Mar</p>
                        <p style="font-size: 11px; color: #64748b;">8 Orders • 4 Hours</p>
                    </div>
                </div>
                <p style="font-weight: 800; color: #1e293b;">Rs. 1,250</p>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleStatus() {
        const card = document.getElementById('statusCard');
        const text = document.getElementById('statusText');
        if(card.classList.contains('online')) {
            card.classList.remove('online');
            text.innerText = 'OFFLINE';
            text.style.color = '#64748b';
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: 'You are now OFFLINE',
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            card.classList.add('online');
            text.innerText = 'ONLINE';
            text.style.color = '#10b981';
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'You are now ONLINE',
                showConfirmButton: false,
                timer: 2000
            });
        }
    }

    function markDelivered() {
        Swal.fire({
            title: 'Confirm Delivery?',
            text: "Are you sure you have handed over assignment #ORD-9921?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Yes, Delivered!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Success', 'Order marked as delivered. Earnings added to your wallet!', 'success');
            }
        });
    }
</script>
</body>
</html>
