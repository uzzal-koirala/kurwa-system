<?php
$current_page = $current_page ?? 'dashboard';
?>
<!-- Mobile Header for Sidebar Toggle -->
<div class="mobile-header">
    <div class="logo">
        <i class="ri-capsule-fill" style="color: #059669; margin-right: 8px;"></i> 
        Kurwa<span style="color: #059669;">Pharmacy</span>
    </div>
    <i class="ri-menu-line menu-toggle" onclick="toggleSidebar()"></i>
</div>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="ri-capsule-fill" style="color: #059669; font-size: 28px; margin-right: 12px;"></i>
            <span style="font-weight: 700; font-size: 22px; color: #0f172a; letter-spacing: -0.5px;">Kurwa<span style="color: #059669;">Pharm</span></span>
        </div>
        <i class="ri-close-line close-sidebar" onclick="toggleSidebar()"></i>
    </div>

    <ul class="nav-links">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <i class="ri-dashboard-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="inventory.php" class="nav-link <?= $current_page === 'inventory' ? 'active' : '' ?>">
                <i class="ri-medicine-bottle-fill"></i>
                <span>Inventory</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="orders.php" class="nav-link <?= $current_page === 'orders' ? 'active' : '' ?>">
                <i class="ri-shopping-bag-3-fill"></i>
                <span>Prescription Orders</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="earnings.php" class="nav-link <?= $current_page === 'earnings' ? 'active' : '' ?>">
                <i class="ri-wallet-3-fill"></i>
                <span>Earnings & Payouts</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="settings.php" class="nav-link <?= $current_page === 'settings' ? 'active' : '' ?>">
                <i class="ri-settings-4-fill"></i>
                <span>Store Settings</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <i class="ri-logout-box-r-line"></i>
            <span>Log Out Account</span>
        </a>
        <div class="user-info">
            <div class="avatar">
                <i class="ri-store-2-fill"></i>
            </div>
            <div class="details">
                <h4><?= htmlspecialchars($_SESSION['pharmacy_name']) ?></h4>
                <p>Pharmacy Partner</p>
            </div>
        </div>
    </div>
</nav>
