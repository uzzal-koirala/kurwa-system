<?php
$delivery_name = $_SESSION['delivery_name'] ?? 'Rider';
$current_page = $current_page ?? 'dashboard';
?>
<aside class="delivery-sidebar" id="deliverySidebar">
    <div class="sidebar-header">
        <div class="logo-area">
            <div class="logo-box">
                <i class="ri-moped-fill"></i>
            </div>
            <span class="logo-text">Kurwa <span class="text-white opacity-60">Rider</span></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?= $current_page == 'dashboard' ? 'active' : '' ?>">
            <i class="ri-dashboard-3-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="orders.php" class="nav-item <?= $current_page == 'orders' ? 'active' : '' ?>">
            <i class="ri-route-line"></i>
            <span>Active Orders</span>
            <span class="badge">3</span>
        </a>
        <a href="history.php" class="nav-item <?= $current_page == 'history' ? 'active' : '' ?>">
            <i class="ri-history-line"></i>
            <span>Deliveries</span>
        </a>
        <a href="earnings.php" class="nav-item <?= $current_page == 'earnings' ? 'active' : '' ?>">
            <i class="ri-wallet-3-line"></i>
            <span>Earnings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="rider-profile">
            <div class="rider-info">
                <p class="name"><?= htmlspecialchars($delivery_name) ?></p>
                <div class="status-indicator">
                    <span class="dot pulse"></span>
                    <span>ONLINE</span>
                </div>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="ri-logout-box-r-line"></i>
            <span>End Shift</span>
        </a>
    </div>
</aside>
