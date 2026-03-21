<script>
    // Immediate state check to prevent flash
    (function() {
        if (localStorage.getItem('riderSidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed-init');
            // Also try to add to body immediately if it exists, otherwise wait
            if (document.body) {
                document.body.classList.add('sidebar-collapsed-init');
            } else {
                document.addEventListener('DOMContentLoaded', () => {
                    document.body.classList.add('sidebar-collapsed-init');
                });
            }
        }
    })();
</script>

<?php
$delivery_name = $_SESSION['delivery_name'] ?? 'Rider';
$current_page = $current_page ?? 'dashboard';
?>

<!-- Mobile Toggle Button -->
<button class="mobile-sidebar-toggle" onclick="toggleSidebar()">
    <i class="ri-menu-2-line"></i>
</button>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="delivery-sidebar" id="deliverySidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-icon">
                <i class="ri-moped-fill"></i>
            </div>
            <span class="brand-text">Kurwa Rider <span>PRO</span></span>
        </div>
        
        <button class="desktop-toggle-btn hidden md:flex" onclick="toggleSidebarCollapse()" title="Toggle Sidebar">
            <i class="ri-menu-line" id="collapseIcon"></i>
        </button>
    </div>

    <div class="sidebar-body">
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item <?= $current_page == 'dashboard' ? 'active' : '' ?>">
                <i class="ri-layout-grid-fill"></i>
                <span>Dashboard</span>
            </a>

            <a href="orders.php" class="menu-item <?= $current_page == 'orders' ? 'active' : '' ?>">
                <i class="ri-route-fill"></i>
                <span>Active Orders</span>
            </a>

            <a href="history.php" class="menu-item <?= $current_page == 'history' ? 'active' : '' ?>">
                <i class="ri-history-fill"></i>
                <span>Deliveries</span>
            </a>

            <a href="earnings.php" class="menu-item <?= $current_page == 'earnings' ? 'active' : '' ?>">
                <i class="ri-wallet-3-fill"></i>
                <span>Earnings</span>
            </a>
        </div>

        <div class="premium-card">
            <h4>Boost your income with <span>Priority Delivery</span>!</h4>
            <div class="premium-content">
                <button class="upgrade-btn">
                    <i class="ri-arrow-right-line"></i>
                </button>
                <div class="premium-deco">
                    <i class="ri-flashlight-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <a href="support.php" class="support-btn">
            <i class="ri-customer-service-2-fill"></i>
            <span>Rider Support</span>
        </a>

        <div class="user-profile-badge">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($delivery_name) ?>&background=10b981&color=fff" alt="">
            <div class="user-profile-info">
                <h4><?= htmlspecialchars($delivery_name) ?></h4>
                <p>Silver Rider</p>
            </div>
            <a href="logout.php" class="logout-icon" title="Logout">
                <i class="ri-logout-box-r-line"></i>
            </a>
        </div>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('deliverySidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    }

    function toggleSidebarCollapse() {
        const sidebar = document.getElementById('deliverySidebar');
        sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');
        
        if (sidebar.classList.contains('collapsed')) {
            localStorage.setItem('riderSidebarCollapsed', 'true');
        } else {
            localStorage.setItem('riderSidebarCollapsed', 'false');
        }
    }

    // Initialize state on load
    document.addEventListener('DOMContentLoaded', () => {
        const isCollapsed = localStorage.getItem('riderSidebarCollapsed') === 'true';
        const sidebar = document.getElementById('deliverySidebar');
        
        if (isCollapsed) {
            sidebar?.classList.add('collapsed');
            document.body?.classList.add('sidebar-collapsed');
        }
    });
</script>
