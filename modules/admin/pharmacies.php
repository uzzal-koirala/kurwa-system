<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "pharmacies";
$pharmacies = $conn->query("
    SELECT p.*, l.name as location_name, h.name as hospital_name 
    FROM pharmacies p
    LEFT JOIN locations l ON p.location_id = l.id 
    LEFT JOIN hospitals h ON p.hospital_id = h.id 
    ORDER BY p.created_at DESC
");

$locations = $conn->query("SELECT * FROM locations ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Database | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .vendor-card { background: rgba(15, 23, 42, 0.4); border: 1px solid var(--admin-border); border-radius: 20px; padding: 20px; transition: 0.3s; display: flex; flex-direction: column; gap: 12px; }
        .vendor-card:hover { border-color: #8b5cf6; background: rgba(139, 92, 246, 0.05); transform: translateY(-5px); }
        .vendor-img { width: 100%; height: 140px; border-radius: 12px; object-fit: cover; }
        .status-badge { padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .status-open { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .status-closed { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .vendor-actions { display: flex; gap: 10px; margin-top: auto; }
        .vendor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    </style>
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <div>
            <h1 style="color:#a78bfa;">Pharmacy Database</h1>
            <p style="color:var(--admin-text-muted); font-size:14px; margin-top:5px;">Manage retail medicine providers and medicine stocks.</p>
        </div>
        <a href="manage_pharmacy_profile.php" style="background:#8b5cf6; border:none; color:white; padding:12px 25px; border-radius:12px; text-decoration:none; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);">
            <i class="ri-capsule-line"></i> Register Pharmacy
        </a>
    </div>

    <div class="vendor-grid" id="vendorGrid">
        <?php while($p = $pharmacies->fetch_assoc()): ?>
        <div class="vendor-card" id="card-<?= $p['id'] ?>">
            <?php 
                $img = !empty($p['image_url']) ? '../../'.$p['image_url'] : 'https://images.unsplash.com/photo-1576091160550-217359f4ecf8?w=500&q=80';
            ?>
            <img src="<?= $img ?>" class="vendor-img">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <h3 style="font-size:16px; font-weight:700; color:white;"><?= htmlspecialchars($p['name']) ?></h3>
                <span class="status-badge status-<?= $p['status'] ?>"><?= $p['status'] ?></span>
            </div>
            
            <div style="font-size:12px; color:var(--admin-text-muted);">
                <div style="margin-bottom:6px;"><i class="ri-map-pin-2-line"></i> <?= htmlspecialchars($p['location_name'] ?? 'General') ?></div>
                <div style="margin-bottom:6px;"><i class="ri-time-line"></i> <?= date('h:i A', strtotime($p['opening_time'])) ?> - <?= date('h:i A', strtotime($p['closing_time'])) ?></div>
                <div><i class="ri-phone-line"></i> <?= htmlspecialchars($p['phone']) ?></div>
            </div>

            <div class="vendor-actions">
                <a href="manage_pharmacy_profile.php?id=<?= $p['id'] ?>" style="flex:1; background:rgba(255,255,255,0.05); border:1px solid var(--admin-border); color:white; padding:10px; border-radius:10px; cursor:pointer; text-align:center; font-size:13px; font-weight:600; text-decoration:none;"><i class="ri-pencil-line"></i> Edit</a>
                <button onclick="deletePharmacy(<?= $p['id'] ?>)" style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); color:#ef4444; width:40px; border-radius:10px; cursor:pointer;"><i class="ri-delete-bin-line"></i></button>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    async function deletePharmacy(id) {
        if(!confirm('Delete this pharmacy? This cannot be undone.')) return;
        const body = new FormData();
        body.append('action', 'delete');
        body.append('id', id);
        try {
            const res = await fetch('api/manage_pharmacies.php', { method: 'POST', body: body });
            const result = await res.json();
            if(result.success) { 
                document.getElementById('card-'+id).remove(); 
            } else { 
                alert(result.message); 
            }
        } catch(err) { alert('Deletion failed.'); }
    }
</script>
</body>
</html>
