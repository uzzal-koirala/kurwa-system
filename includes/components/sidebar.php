<?php
if (!isset($current_page)) {
    $current_page = 'dashboard';
}

if (!isset($user_id) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = (isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Member') . ' Account';
$user_avatar = !empty($_SESSION['profile_picture']) ? '/Kurwa/kurwa-system/' . $_SESSION['profile_picture'] : "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=3b82f6&color=fff";
?>

<!-- Universal Mobile Top Bar -->
<div class="mobile-top-bar">
    <button class="mobile-toggle-btn" id="openSidebarUniversal" aria-label="Open Sidebar">
        <i class="ri-menu-4-line"></i>
    </button>
    
    <div class="mobile-user-info">
        <div class="info">
            <h4><?php echo htmlspecialchars($user_name); ?></h4>
            <p>Verified Member</p>
        </div>
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=3b82f6&color=fff" alt="User">
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
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
        <span class="menu-label">
            Main Menu
        </span>
        
        <a href="user_dashboard.php" class="menu-item <?php echo($current_page === 'dashboard') ? 'active' : ''; ?>">
            <i class="ri-function-line"></i>
            <span>Overview</span>
        </a>
        <a href="caretaker.php" class="menu-item <?php echo($current_page === 'caretaker' || $current_page === 'caretakers') ? 'active' : ''; ?>">
            <i class="ri-user-heart-line"></i>
            <span>Hire Caretaker</span>
        </a>
        <a href="chat.php" class="menu-item <?php echo($current_page === 'chat') ? 'active' : ''; ?>" style="position:relative;">
            <i class="ri-message-3-line"></i>
            <span>Messages</span>
        </a>
        <a href="food_orders.php" class="menu-item <?php echo($current_page === 'food_orders') ? 'active' : ''; ?>">
            <i class="ri-restaurant-2-line"></i>
            <span>Food Orders</span>
        </a>
        <a href="my_orders.php" class="menu-item <?php echo($current_page === 'my_orders') ? 'active' : ''; ?>">
            <i class="ri-shopping-bag-3-fill"></i>
            <span>My Orders</span>
        </a>
        <a href="medicine_orders.php" class="menu-item <?php echo($current_page === 'medicine_orders') ? 'active' : ''; ?>">
            <i class="ri-capsule-line"></i>
            <span>Order Medicines</span>
        </a>

        <a href="support.php" class="menu-item <?php echo($current_page === 'support') ? 'active' : ''; ?>">
            <i class="ri-customer-service-2-line"></i>
            <span>Support & Tickets</span>
        </a>
        <a href="payments.php" class="menu-item <?php echo($current_page === 'payments') ? 'active' : ''; ?>">
            <i class="ri-wallet-3-line"></i>
            <span>Payments & Topup</span>
        </a>
        <a href="settings.php" class="menu-item <?php echo($current_page === 'settings') ? 'active' : ''; ?>">
            <i class="ri-settings-4-line"></i>
            <span>Settings</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="user-profile">
            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="User" onerror="this.src='https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&w=100&q=80'">
            <div class="user-info">
                <h4><?php echo htmlspecialchars($user_name); ?></h4>
                <p><?php echo htmlspecialchars($user_role); ?></p>
            </div>
        </div>
    </div>
</aside>