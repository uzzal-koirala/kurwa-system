<?php
if (!isset($current_page)) {
    $current_page = 'dashboard';
}

$admin_name = isset($user_name) ? $user_name : 'Administrator';
$admin_role = 'System Control';
?>

<!-- Universal Mobile Top Bar for Admin -->
<div class="mobile-top-bar admin-top-bar">
    <button class="mobile-toggle-btn" id="openSidebarUniversal" aria-label="Open Sidebar">
        <i class="ri-menu-4-line"></i>
    </button>
    
    <div class="mobile-brand">
        <i class="ri-shield-flash-fill"></i>
        <span>Admin Panel</span>
    </div>

    <div class="mobile-user-info">
        <img src="https://ui-avatars.com/api/?name=Admin&background=1e293b&color=fff" alt="Admin">
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar admin-sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-icon admin-icon">
                <i class="ri-shield-user-fill"></i>
            </div>
            <span class="brand-text">Kurwa <span>Admin</span></span>
        </div>
        
        <button class="desktop-toggle" id="desktopSidebarToggle" aria-label="Toggle Sidebar">
            <i class="ri-skip-back-line"></i>
        </button>
        
        <button class="close-sidebar" id="closeSidebar" aria-label="Close Sidebar">
            <i class="ri-close-large-line"></i>
        </button>
    </div>

    <div class="sidebar-menu">
        <span class="menu-label">System Overview</span>
        
        <a href="dashboard.php" class="menu-item <?php echo($current_page === 'dashboard') ? 'active' : ''; ?>">
            <i class="ri-dashboard-3-line"></i>
            <span>Control Center</span>
        </a>

        <span class="menu-label">Management</span>
        
        <a href="users.php" class="menu-item <?php echo($current_page === 'users') ? 'active' : ''; ?>">
            <i class="ri-group-line"></i>
            <span>User Database</span>
        </a>
        <a href="caretakers.php" class="menu-item <?php echo($current_page === 'caretakers') ? 'active' : ''; ?>">
            <i class="ri-heart-pulse-line"></i>
            <span>Expert Directory</span>
        </a>
        <a href="caretaker_approvals.php" class="menu-item <?php echo($current_page === 'caretaker_approvals') ? 'active' : ''; ?>">
            <i class="ri-checkbox-circle-line"></i>
            <span>Approval Queue</span>
        </a>
        <a href="caretaker_categories.php" class="menu-item <?php echo($current_page === 'caretaker_categories') ? 'active' : ''; ?>">
            <i class="ri-bookmark-3-line"></i>
            <span>Expert Categories</span>
        </a>
        <a href="partners.php" class="menu-item <?php echo($current_page === 'partners') ? 'active' : ''; ?>">
            <i class="ri-store-2-line"></i>
            <span>Partners & Stores</span>
        </a>
        <a href="orders.php" class="menu-item <?php echo($current_page === 'orders') ? 'active' : ''; ?>">
            <i class="ri-exchange-funds-line"></i>
            <span>Global Orders</span>
        </a>

        <span class="menu-label">Administration</span>
        
        <a href="reports.php" class="menu-item <?php echo($current_page === 'reports') ? 'active' : ''; ?>">
            <i class="ri-pie-chart-2-line"></i>
            <span>System Reports</span>
        </a>
        <a href="settings.php" class="menu-item <?php echo($current_page === 'settings') ? 'active' : ''; ?>">
            <i class="ri-settings-4-line"></i>
            <span>Global Settings</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="user-profile admin-profile">
            <img src="https://ui-avatars.com/api/?name=Admin&background=0f172a&color=fff" alt="Admin">
            <div class="user-info">
                <h4><?php echo htmlspecialchars($admin_name); ?></h4>
                <p><?php echo htmlspecialchars($admin_role); ?></p>
            </div>
            <a href="../user/logout.php" class="logout-btn"><i class="ri-logout-box-r-line"></i></a>
        </div>
    </div>
</aside>
