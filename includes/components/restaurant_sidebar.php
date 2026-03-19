<?php
if (!isset($current_page)) {
    $current_page = 'dashboard';
}

$display_name = $_SESSION['restaurant_name'] ?? 'Restaurant Owner';
$display_role = 'Partner';
?>

<aside class="sidebar sidebar-restaurant" id="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-icon">
                <i class="ri-restaurant-fill"></i>
            </div>
            <span class="brand-text">Kurwa Food</span>
        </div>
        
        <button class="desktop-toggle" id="desktopSidebarToggle" aria-label="Toggle Sidebar">
            <i class="ri-arrow-left-s-line"></i>
        </button>

        <button class="mobile-close-toggle" id="closeSidebar" aria-label="Close Sidebar">
            <i class="ri-close-line"></i>
        </button>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo($current_page === 'dashboard') ? 'active' : ''; ?>">
            <i class="ri-layout-grid-fill"></i>
            <span>Dashboard</span>
        </a>

        <a href="orders.php" class="menu-item <?php echo($current_page === 'orders') ? 'active' : ''; ?>">
            <i class="ri-shopping-bag-3-line"></i>
            <span>Orders & Delivery</span>
        </a>
        
        <a href="menu.php" class="menu-item <?php echo($current_page === 'menu') ? 'active' : ''; ?>">
            <i class="ri-file-list-3-line"></i>
            <span>Menu Items</span>
        </a>

        <a href="earnings.php" class="menu-item <?php echo($current_page === 'earnings') ? 'active' : ''; ?>">
            <i class="ri-wallet-3-line"></i>
            <span>Earnings</span>
        </a>
        
        <a href="settings.php" class="menu-item <?php echo($current_page === 'settings') ? 'active' : ''; ?>">
            <i class="ri-settings-4-line"></i>
            <span>Settings</span>
        </a>
    </div>

    <div class="premium-card">
        <h4>Boost your sales with <span>Top Placement</span> ads!</h4>
        <div class="premium-content">
            <button class="upgrade-btn">
                <i class="ri-arrow-right-line"></i>
            </button>
            <div class="premium-deco">
                <i class="ri-megaphone-fill"></i>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <a href="support.php" class="support-btn">
            <i class="ri-customer-service-2-fill"></i>
            <span>Partner Support</span>
        </a>

        <div class="user-profile-badge">
            <img src="<?= htmlspecialchars($_SESSION['restaurant_image'] ?? 'https://ui-avatars.com/api/?name='.urlencode($display_name).'&background=ff7e5f&color=fff') ?>" alt="">
            <div class="user-profile-info">
                <h4><?= htmlspecialchars($display_name) ?></h4>
                <p><?= htmlspecialchars($display_role) ?></p>
            </div>
            <a href="logout.php" class="logout-icon" title="Logout">
                <i class="ri-logout-box-r-line"></i>
            </a>
        </div>
    </div>
</aside>
