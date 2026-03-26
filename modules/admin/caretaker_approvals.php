<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "caretakers"; // We use the same side-link, but we filter for pending

// Fetch pending caretakers
$pending = $conn->query("SELECT * FROM caretakers WHERE onboarding_completed = 1 AND status = 'pending' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expert Approvals | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <h1>Expert Verification</h1>
        <p style="color:var(--admin-text-muted); font-size:14px;">Review and approve new caretakers and experts.</p>
    </div>

    <div class="admin-panel-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Expert Profile</th>
                    <th>Category</th>
                    <th>Skills</th>
                    <th>Document</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($pending->num_rows === 0): ?>
                    <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--admin-text-muted);">No pending approvals found.</td></tr>
                <?php endif; ?>
                
                <?php while($ct = $pending->fetch_assoc()): ?>
                <tr id="row-<?= $ct['id'] ?>">
                    <td>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <img src="../../<?= htmlspecialchars($ct['photo']) ?>" style="width:60px; height:60px; border-radius:15px; object-fit:cover; border:2px solid var(--admin-border);">
                            <div>
                                <div style="font-weight:700; font-size:16px;"><?= htmlspecialchars($ct['full_name']) ?></div>
                                <div style="font-size:12px; color:var(--admin-text-muted);"><?= htmlspecialchars($ct['phone']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="admin-badge"><?= htmlspecialchars($ct['category']) ?></span></td>
                    <td style="max-width:200px; font-size:13px; line-height:1.4; color:var(--admin-text-muted);">
                        <?= htmlspecialchars($ct['skills']) ?>
                    </td>
                    <td>
                        <a href="../../<?= htmlspecialchars($ct['document']) ?>" target="_blank" style="color:var(--admin-primary); text-decoration:none; font-weight:600; display:flex; align-items:center; gap:5px;">
                            <i class="ri-file-search-line"></i> View Org. Doc
                        </a>
                    </td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button onclick="approveExpert(<?= $ct['id'] ?>, '<?= htmlspecialchars($ct['full_name']) ?>', '<?= htmlspecialchars($ct['phone']) ?>')" style="background:var(--admin-primary); border:none; color:white; padding:8px 15px; border-radius:10px; font-weight:700; cursor:pointer;">Approve</button>
                            <button onclick="disapproveExpert(<?= $ct['id'] ?>)" style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); color:#ef4444; padding:8px 15px; border-radius:10px; font-weight:700; cursor:pointer;">Reject</button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
async function approveExpert(id, name, phone) {
    if(!confirm(`Approve ${name}? An SMS will be sent in Nepali.`)) return;
    
    try {
        const response = await fetch('api/verify_caretaker.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&action=approve&name=${encodeURIComponent(name.split(' ')[0])}&phone=${phone}`
        });
        const result = await response.json();
        if(result.success) {
            document.getElementById('row-' + id).remove();
            alert('Expert Approved successfully.');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (e) { console.error(e); }
}

async function disapproveExpert(id) {
    let reason = prompt('Reason for rejection?');
    if(!reason) return;
    
    try {
        const response = await fetch('api/verify_caretaker.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&action=disapprove&reason=${encodeURIComponent(reason)}`
        });
        const result = await response.json();
        if(result.success) {
            document.getElementById('row-' + id).remove();
            alert('Expert Rejected.');
        }
    } catch (e) { console.error(e); }
}
</script>
</body>
</html>
