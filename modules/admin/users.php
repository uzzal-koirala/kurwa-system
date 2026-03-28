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
    <style>
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: var(--admin-card-bg); border: 1px solid var(--admin-border); width: 100%; max-width: 500px; border-radius: 20px; padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; color: var(--admin-text-muted); margin-bottom: 8px; font-weight: 500; }
        .form-control { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--admin-border); border-radius: 12px; padding: 12px 15px; color: white; outline: none; transition: 0.3s; }
        .form-control:focus { border-color: var(--admin-primary); background: rgba(255,255,255,0.05); }
    </style>
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <div>
            <h1>User Database</h1>
            <p style="color:var(--admin-text-muted); font-size:14px; margin-top:5px;">Manage platform users and account records.</p>
        </div>
        <div style="display:flex; gap:15px; align-items:center;">
            <div class="search-box">
                <form method="GET" style="display:flex; gap:10px;">
                    <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search_query) ?>" style="background:var(--admin-card-bg); border:1px solid var(--admin-border); color:white; padding:12px 20px; border-radius:12px; width:250px;">
                    <button type="submit" style="background:var(--admin-card-bg); border:1px solid var(--admin-border); color:white; width:44px; border-radius:12px;"><i class="ri-search-line"></i></button>
                </form>
            </div>
            <button onclick="openModal()" style="background:var(--admin-primary); color:white; border:none; padding:12px 25px; border-radius:12px; font-weight:700; cursor:pointer; display:flex; gap:8px; align-items:center;">
                <i class="ri-user-add-line"></i> Add User
            </button>
        </div>
    </div>

    <div class="admin-panel-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Profile Information</th>
                    <th>Joined At</th>
                    <th>Verification</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $users->fetch_assoc()): ?>
                <tr id="user-<?= $user['id'] ?>">
                    <td><span style="font-family:monospace; color:var(--admin-text-muted);">#<?= str_pad($user['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=random&color=fff&rounded=true" style="width:45px; height:45px; border-radius:12px; border:2px solid var(--admin-border);">
                            <div>
                                <div style="font-weight:700; font-size:15px;"><?= htmlspecialchars($user['full_name']) ?></div>
                                <div style="font-size:12px; color:var(--admin-text-muted);"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= date('d M Y, h:i A', strtotime($user['created_at'])) ?></td>
                    <td>
                        <span class="admin-badge <?= $user['verified'] ? 'admin-badge-success' : 'admin-badge-warning' ?>" style="font-size:10px; padding:4px 10px;">
                            <?= $user['verified'] ? 'Verified' : 'Pending' ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:8px; justify-content:flex-end;">
                            <button onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)" style="background:rgba(99, 102, 241, 0.1); border:none; color:var(--admin-primary); padding:8px; border-radius:8px; cursor:pointer;"><i class="ri-edit-line"></i></button>
                            <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['full_name']) ?>')" style="background:rgba(239, 68, 68, 0.1); border:none; color:#ef4444; padding:8px; border-radius:8px; cursor:pointer;"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <h2 id="modalTitle" style="margin-bottom:25px; font-size:22px;">Add New User</h2>
        <form id="userForm">
            <input type="hidden" name="id" id="u_id">
            <input type="hidden" name="action" id="u_action" value="add">
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" id="u_name" class="form-control" required placeholder="e.g. Sujal Bardewa">
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" id="u_email" class="form-control" required placeholder="e.g. user@example.com">
            </div>
            
            <div class="form-group">
                <label>Password <span id="pwdLabel" style="font-size:10px; color:#ef4444;">(Leave blank to keep current)</span></label>
                <input type="password" name="password" id="u_password" class="form-control" placeholder="••••••••">
            </div>

            <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-top:10px;">
                <input type="checkbox" name="verified" id="u_verified" style="width:18px; height:18px; accent-color:var(--admin-primary);">
                <label style="margin-bottom:0;">Mark as Verified Account</label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:30px;">
                <button type="button" onclick="closeModal()" style="background:none; border:none; color:var(--admin-text-muted); font-weight:600; cursor:pointer;">Cancel</button>
                <button type="submit" style="background:var(--admin-primary); border:none; color:white; padding:12px 30px; border-radius:12px; font-weight:700; cursor:pointer;">Save User Profile</button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
function openModal() {
    document.getElementById('modalTitle').innerText = "Add New User";
    document.getElementById('u_id').value = "";
    document.getElementById('u_action').value = "add";
    document.getElementById('u_name').value = "";
    document.getElementById('u_email').value = "";
    document.getElementById('u_verified').checked = false;
    document.getElementById('pwdLabel').style.display = "none";
    document.getElementById('userModal').classList.add('active');
}

function closeModal() {
    document.getElementById('userModal').classList.remove('active');
}

function editUser(user) {
    document.getElementById('modalTitle').innerText = "Edit User Profile";
    document.getElementById('u_id').value = user.id;
    document.getElementById('u_action').value = "edit";
    document.getElementById('u_name').value = user.full_name;
    document.getElementById('u_email').value = user.email;
    document.getElementById('u_verified').checked = parseInt(user.verified) === 1;
    document.getElementById('pwdLabel').style.display = "inline";
    document.getElementById('userModal').classList.add('active');
}

async function deleteUser(id, name) {
    if(!confirm(`WARNING: Are you sure you want to delete ${name}?\n\nThis will PERMANENTLY delete all their orders, bookings, and platform data. This action cannot be undone.`)) return;
    
    try {
        const response = await fetch('api/manage_user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=delete&id=${id}`
        });
        const result = await response.json();
        if(result.success) {
            document.getElementById('user-' + id).remove();
            alert(result.message);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (e) { console.error(e); }
}

document.getElementById('userForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/manage_user.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if(result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (e) { console.error(e); }
});
</script>
</body>
</html>
