<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "locations";
$locations = $conn->query("SELECT * FROM locations ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Locations | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <h1>Service Locations</h1>
        <button onclick="openModal('add')" style="background:var(--admin-primary); border:none; color:white; padding:10px 25px; border-radius:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px;">
            <i class="ri-map-pin-add-line"></i> Add New Area
        </button>
    </div>

    <div class="admin-panel-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($loc = $locations->fetch_assoc()): ?>
                <tr id="row-<?= $loc['id'] ?>">
                    <td style="color:var(--admin-text-muted);">#<?= $loc['id'] ?></td>
                    <td style="font-weight:700; color:var(--admin-primary);"><?= htmlspecialchars($loc['name']) ?></td>
                    <td style="font-size:13px; color:var(--admin-text-muted);"><?= date('M d, Y', strtotime($loc['created_at'])) ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button onclick='openModal("edit", <?= json_encode($loc) ?>)' style="background:rgba(255,255,255,0.05); border:1px solid var(--admin-border); color:white; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-pencil-line"></i></button>
                            <button onclick="deleteLocation(<?= $loc['id'] ?>)" style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); color:#ef4444; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="locModal" class="admin-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div style="background:#0f172a; border:1px solid var(--admin-border); width:400px; border-radius:24px; padding:30px;">
        <h2 id="modalTitle" style="margin-top:0;">Add Location</h2>
        <form id="locForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="locId">
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:8px;">Name</label>
                <input type="text" name="name" id="f_name" required class="form-control" style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:12px; padding:12px; color:white;" placeholder="e.g. Kathmandu">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeModal()" style="background:transparent; border:1px solid var(--admin-border); color:white; padding:10px 20px; border-radius:10px; cursor:pointer;">Cancel</button>
                <button type="submit" style="background:var(--admin-primary); border:none; color:white; padding:10px 25px; border-radius:10px; font-weight:700; cursor:pointer;">Save</button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
function openModal(mode, data = null) {
    document.getElementById('locForm').reset();
    document.getElementById('formAction').value = mode;
    if(mode === 'edit') {
        document.getElementById('modalTitle').innerText = 'Edit Location';
        document.getElementById('locId').value = data.id;
        document.getElementById('f_name').value = data.name;
    } else {
        document.getElementById('modalTitle').innerText = 'Add Location';
    }
    document.getElementById('locModal').style.display = 'flex';
}

function closeModal() { document.getElementById('locModal').style.display = 'none'; }

document.getElementById('locForm').onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await fetch('api/manage_locations.php', { method: 'POST', body: formData });
    const result = await res.json();
    if(result.success) { location.reload(); } else { alert(result.message); }
};

async function deleteLocation(id) {
    if(!confirm('Delete this location? All associated hospitals will also be removed.')) return;
    const body = new FormData();
    body.append('action', 'delete');
    body.append('id', id);
    const res = await fetch('api/manage_locations.php', { method: 'POST', body: body });
    const result = await res.json();
    if(result.success) { document.getElementById('row-'+id).remove(); } else { alert(result.message); }
}
</script>
</body>
</html>
