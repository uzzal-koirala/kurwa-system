<?php
if (!defined('INC_PATH')) {
    require_once __DIR__ . '/../core/config.php';
}
require_once INC_PATH . '/core/auth_check.php';
$user_name = $_SESSION['full_name'] ?? "User";
$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=3b82f6&color=fff";
?>
<div class="user-dropdown-container">
    <div class="user-display" id="profileToggle">
        <div class="info">
            <h4><?= htmlspecialchars($user_name) ?></h4>
            <p>Verified Member</p>
        </div>
        <img src="<?= $avatar_url ?>" alt="User">
    </div>
    
    <div class="profile-dropdown" id="profileDropdown">
        <div class="dropdown-header">
            <h4>Account</h4>
        </div>
        <a href="#" class="dropdown-item">
            <i class="ri-settings-4-line"></i>
            <span>Settings</span>
        </a>
        <div class="dropdown-divider"></div>
        <a href="logout.php" class="dropdown-item logout">
            <i class="ri-logout-box-r-line"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
