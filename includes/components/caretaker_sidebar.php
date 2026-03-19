<?php
if (!isset($current_page)) {
    $current_page = 'dashboard';
}

// Dedicated caretaker identity logic
$display_name = $_SESSION['caretaker_name'] ?? 'Certified Caretaker';
$display_role = 'Premium Member';
$dashboard_url = '../caretaker/dashboard.php';
?>


<aside class="sidebar sidebar-caretaker" id="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-icon">
                <i class="ri-heart-pulse-fill"></i>
            </div>
            <span class="brand-text">Kurwa</span>
        </div>
        
        <!-- Desktop Collapse Toggle - Floating Mockup Style -->
        <button class="desktop-toggle" id="desktopSidebarToggle" aria-label="Toggle Sidebar">
            <i class="ri-arrow-left-s-line"></i>
        </button>

        <!-- Mobile Close Toggle -->
        <button class="mobile-close-toggle" id="closeSidebar" aria-label="Close Sidebar">
            <i class="ri-close-line"></i>
        </button>
    </div>

    <div class="sidebar-menu">
        <a href="<?php echo $dashboard_url; ?>" class="menu-item <?php echo($current_page === 'dashboard') ? 'active' : ''; ?>">
            <i class="ri-layout-grid-fill"></i>
            <span>Dashboard</span>
        </a>

        <a href="bookings.php" class="menu-item <?php echo($current_page === 'bookings') ? 'active' : ''; ?>">
            <i class="ri-calendar-check-line"></i>
            <span>My Bookings</span>
        </a>
        
        <a href="earnings.php" class="menu-item <?php echo($current_page === 'earnings') ? 'active' : ''; ?>">
            <i class="ri-funds-line"></i>
            <span>Earnings</span>
        </a>

        <a href="chat.php" class="menu-item <?php echo($current_page === 'chat') ? 'active' : ''; ?>">
            <i class="ri-chat-3-line"></i>
            <span>Messaging</span>
        </a>
        
        <a href="support.php" class="menu-item <?php echo($current_page === 'support') ? 'active' : ''; ?>">
            <i class="ri-settings-4-line"></i>
            <span>Settings</span>
        </a>
    </div>

    <!-- Premium Card - Mockup Upgrade Widget -->
    <div class="premium-card">
        <h4>Get more power with our <span>Premium</span> features now!</h4>
        <div class="premium-content">
            <button class="upgrade-btn">
                <i class="ri-arrow-right-line"></i>
            </button>
            <div class="premium-deco">
                <i class="ri-rocket-2-fill deco-rocket"></i>
                <i class="ri-shield-flash-line deco-shield"></i>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <a href="support.php" class="support-btn">
            <i class="ri-customer-service-2-fill"></i>
            <span>Support</span>
        </a>

        <div class="mode-toggle-wrapper">
            <div class="mode-toggle-label">
                <i class="ri-sun-line"></i>
                <span>Light Mode</span>
            </div>
            <label class="switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>
</aside>
