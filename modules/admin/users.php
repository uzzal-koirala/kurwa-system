<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "users";

$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search']);
    $users = $conn->query("SELECT * FROM users WHERE (full_name LIKE '%$search_query%' OR email LIKE '%$search_query%') AND role = 'user' ORDER BY created_at DESC");
} else {
    $users = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <h1>User Database</h1>
        <div class="search-box">
            <form method="GET" style="display:flex; gap:10px;">
                <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search_query) ?>" style="background:var(--admin-card-bg); border:1px solid var(--admin-border); color:white; padding:10px 20px; border-radius:12px; font-family:inherit; width:300px;">
                <button type="submit" style="background:var(--admin-primary); border:none; color:white; padding:0 20px; border-radius:12px; cursor:pointer;"><i class="ri-search-line"></i></button>
            </form>
        </div>
    </div>

    <div class="admin-panel-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Profile</th>
                    <th>Join Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $users->fetch_assoc()): ?>
                <tr>
                    <td>#<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=random" style="width:40px; height:40px; border-radius:12px;">
                            <div>
                                <div style="font-weight:700;"><?= htmlspecialchars($user['full_name']) ?></div>
                                <div style="font-size:12px; color:var(--admin-text-muted);"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <span class="admin-badge <?= $user['verified'] ? 'admin-badge-success' : 'admin-badge-warning' ?>">
                            <?= $user['verified'] ? 'Verified' : 'Unverified' ?>
                        </span>
                    </td>
                    <td>
                        <button style="background:none; border:none; color:var(--admin-text-muted); cursor:pointer; font-size:18px;"><i class="ri-more-2-fill"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
