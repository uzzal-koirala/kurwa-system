<?php
if (!isset($current_page)) {
    $current_page = 'dashboard';
}

// Dedicated caretaker identity logic
$display_name = $_SESSION['caretaker_name'] ?? 'Certified Caretaker';
$display_role = 'Premium Member';
$dashboard_url = '../caretaker/dashboard.php';
?>

<!-- Universal Mobile Top Bar (Caretaker Specific) -->
<div class="mobile-top-bar caretaker-mode">
    <button class="mobile-toggle-btn" id="openSidebarUniversal" aria-label="Open Sidebar">
        <i class="ri-menu-4-line"></i>
    </button>
    
    <div class="mobile-user-info">
        <div class="info">
            <h4><?php echo htmlspecialchars($display_name); ?></h4>
            <div class="mobile-role-badge">
                <i class="ri-checkbox-circle-fill"></i> Verified
            </div>
        </div>
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($display_name); ?>&background=6366f1&color=fff" alt="User">
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

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
    </div>

    <div class="sidebar-menu">
        <a href="<?php echo $dashboard_url; ?>" class="menu-item <?php echo($current_page === 'dashboard') ? 'active' : ''; ?>">
            <i class="ri-layout-grid-fill"></i>
            <span>Dashboard</span>
        </a>

        <a href="#" class="menu-item <?php echo($current_page === 'bookings') ? 'active' : ''; ?>">
            <i class="ri-calendar-check-line"></i>
            <span>My Schedule</span>
        </a>
        
        <a href="#" class="menu-item <?php echo($current_page === 'earnings') ? 'active' : ''; ?>">
            <i class="ri-funds-line"></i>
            <span>Earnings</span>
        </a>

        <a href="../user/chat.php" class="menu-item <?php echo($current_page === 'chat') ? 'active' : ''; ?>">
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
