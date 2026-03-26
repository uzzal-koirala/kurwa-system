<?php
if (!isset($current_page)) {
    $current_page = 'dashboard';
}

$display_name = $_SESSION['caretaker_name'] ?? 'Caretaker';
$display_role = 'Verified Professional';
$user_avatar = "https://ui-avatars.com/api/?name=" . urlencode($display_name) . "&background=4361ee&color=fff";
?>

<!-- Universal Mobile Top Bar for Caretaker -->
<div class="mobile-top-bar caretaker-mode">
    <button class="mobile-toggle-btn" id="openSidebarUniversal" aria-label="Open Sidebar">
        <i class="ri-menu-4-line"></i>
    </button>
    
    <div class="mobile-user-info">
        <div class="info">
            <h4><?php echo htmlspecialchars($display_name); ?></h4>
            <p>Professional</p>
        </div>
        <img src="<?php echo $user_avatar; ?>" alt="Caretaker">
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar sidebar-caretaker" id="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-icon">
                <i class="ri-heart-pulse-fill"></i>
            </div>
            <span class="brand-text">Kurwa <span>v1.0</span></span>
        </div>
        
        <!-- Desktop Collapse Toggle -->
        <button class="desktop-toggle" id="desktopSidebarToggle" aria-label="Toggle Sidebar">
            <i class="ri-menu-line"></i>
        </button>

        <!-- Mobile Close Toggle -->
        <button class="close-sidebar" id="closeSidebar" aria-label="Close Sidebar">
            <i class="ri-close-large-line"></i>
        </button>
    </div>

    <div class="sidebar-menu">
        <span class="menu-label">Caretaker Portal</span>
        
        <a href="dashboard.php" class="menu-item <?php echo($current_page === 'dashboard') ? 'active' : ''; ?>">
            <i class="ri-function-line"></i>
            <span>Overview</span>
        </a>

        <a href="bookings.php" class="menu-item <?php echo($current_page === 'bookings') ? 'active' : ''; ?>">
            <i class="ri-calendar-event-line"></i>
            <span>My Bookings</span>
        </a>
        
        <a href="earnings.php" class="menu-item <?php echo($current_page === 'earnings') ? 'active' : ''; ?>">
            <i class="ri-wallet-3-line"></i>
            <span>Earnings</span>
        </a>

        <a href="chat.php" class="menu-item <?php echo($current_page === 'chat') ? 'active' : ''; ?>">
            <i class="ri-message-3-line"></i>
            <span>Messages</span>
        </a>
        
        <a href="support.php" class="menu-item <?php echo($current_page === 'support') ? 'active' : ''; ?>">
            <i class="ri-customer-service-2-line"></i>
            <span>Support</span>
        </a>

        <a href="settings.php" class="menu-item <?php echo($current_page === 'settings') ? 'active' : ''; ?>">
            <i class="ri-settings-4-line"></i>
            <span>Settings</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="user-profile">
            <img src="<?php echo $user_avatar; ?>" alt="Caretaker" onerror="this.src='https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&w=100&q=80'">
            <div class="user-info">
                <h4><?php echo htmlspecialchars($display_name); ?></h4>
                <p><?php echo htmlspecialchars($display_role); ?></p>
            </div>
        </div>
    </div>
</aside>
