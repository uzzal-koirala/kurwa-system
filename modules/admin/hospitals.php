<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "hospitals";
$hospitals = $conn->query("
    SELECT h.*, l.name as location_name 
    FROM hospitals h 
    JOIN locations l ON h.location_id = l.id 
    ORDER BY l.name ASC, h.name ASC
");
$locations = $conn->query("SELECT * FROM locations ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Database | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .form-group select option { background: #0f172a; color: white; padding: 10px; }
        .form-group select:hover { background: rgba(99, 102, 241, 0.2) !important; border-color: rgba(99, 102, 241, 0.5) !important; }
        .form-group select:focus { background: rgba(99, 102, 241, 0.25) !important; border-color: #818cf8 !important; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }
    </style>
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <h1>Hospital Database</h1>
        <button onclick="openModal('add')" style="background:var(--admin-primary); border:none; color:white; padding:10px 25px; border-radius:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px;">
            <i class="ri-hospital-line"></i> Add New Hospital
        </button>
    </div>

    <div class="admin-panel-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Hospital Name</th>
                    <th>Location</th>
                    <th>Full Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($h = $hospitals->fetch_assoc()): ?>
                <tr id="row-<?= $h['id'] ?>">
                    <td style="font-weight:700; color:white;"><?= htmlspecialchars($h['name']) ?></td>
                    <td><span class="admin-badge" style="background:rgba(99, 102, 241, 0.1); color:#818cf8;"><?= htmlspecialchars($h['location_name']) ?></span></td>
                    <td style="font-size:13px; color:var(--admin-text-muted);"><?= htmlspecialchars($h['address']) ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button onclick='openModal("edit", <?= json_encode($h) ?>)' style="background:rgba(255,255,255,0.05); border:1px solid var(--admin-border); color:white; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-pencil-line"></i></button>
                            <button onclick="deleteHospital(<?= $h['id'] ?>)" style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); color:#ef4444; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="hospModal" class="admin-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div style="background:#0f172a; border:1px solid var(--admin-border); width:500px; border-radius:24px; padding:30px;">
        <h2 id="modalTitle">Add Hospital</h2>
        <form id="hospForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="hospId">
            
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Hospital Name</label>
                <input type="text" name="hospital_name" id="f_h_name" required style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white;">
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Select Service Location</label>
                <select name="location_id" id="f_h_location" required style="width:100%; background:rgba(99, 102, 241, 0.12); border:1px solid rgba(99, 102, 241, 0.3); border-radius:12px; padding:12px; color:#818cf8; font-weight:600; outline:none; cursor:pointer; transition:all 0.3s ease;">
                    <option value="">Select Location</option>
                    <?php 
                    $locations->data_seek(0);
                    while($l = $locations->fetch_assoc()): ?>
                        <option value="<?= $l['id'] ?>"><?= $l['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Exact Address</label>
                <input type="text" name="address" id="f_h_address" placeholder="Road, Ward No., etc." style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" onclick="closeModal()" style="background:transparent; border:1px solid var(--admin-border); color:white; padding:10px 20px; border-radius:10px; cursor:pointer;">Cancel</button>
                <button type="submit" style="background:var(--admin-primary); border:none; color:white; padding:10px 25px; border-radius:10px; font-weight:700; cursor:pointer;">Save Hospital</button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
function openModal(mode, data = null) {
    document.getElementById('hospForm').reset();
    document.getElementById('formAction').value = mode;
    if(mode === 'edit') {
        document.getElementById('modalTitle').innerText = 'Edit Hospital';
        document.getElementById('hospId').value = data.id;
        document.getElementById('f_h_name').value = data.name;
        document.getElementById('f_h_location').value = data.location_id;
        document.getElementById('f_h_address').value = data.address;
    } else {
        document.getElementById('modalTitle').innerText = 'Add Hospital';
    }
    document.getElementById('hospModal').style.display = 'flex';
}

function closeModal() { document.getElementById('hospModal').style.display = 'none'; }

document.getElementById('hospForm').onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await fetch('api/manage_hospitals.php', { method: 'POST', body: formData });
    const result = await res.json();
    if(result.success) { location.reload(); } else { alert(result.message); }
};

async function deleteHospital(id) {
    if(!confirm('Delete this hospital?')) return;
    const body = new FormData();
    body.append('action', 'delete');
    body.append('id', id);
    const res = await fetch('api/manage_hospitals.php', { method: 'POST', body: body });
    const result = await res.json();
    if(result.success) { document.getElementById('row-'+id).remove(); } else { alert(result.message); }
}
</script>
</body>
</html>
