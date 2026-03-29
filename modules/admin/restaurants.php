<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "restaurants";
$restaurants = $conn->query("
    SELECT r.*, l.name as location_name, h.name as hospital_name 
    FROM restaurants r 
    LEFT JOIN locations l ON r.location_id = l.id 
    LEFT JOIN hospitals h ON r.hospital_id = h.id 
    ORDER BY r.created_at DESC
");

$locations = $conn->query("SELECT * FROM locations ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Database | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .vendor-card { background: rgba(30, 41, 59, 0.5); border: 1px solid var(--admin-border); border-radius: 20px; padding: 20px; transition: all 0.3s ease; display: flex; flex-direction: column; gap: 15px; }
        .vendor-card:hover { transform: translateY(-5px); border-color: var(--admin-primary); background: rgba(30, 41, 59, 0.8); }
        .vendor-img { width: 100%; height: 140px; border-radius: 15px; object-fit: cover; }
        .vendor-info h3 { margin: 0; font-size: 18px; color: white; }
        .vendor-meta { display: flex; align-items: center; gap: 10px; font-size: 12px; color: var(--admin-text-muted); }
        .vendor-actions { display: flex; gap: 10px; margin-top: auto; }
        .vendor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        
        .status-badge { padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-active { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .status-inactive { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    </style>
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <div>
            <h1>Canteen Database</h1>
            <p style="color:var(--admin-text-muted); font-size:14px; margin-top:5px;">Manage global food vendors and hospital canteens.</p>
        </div>
        <a href="manage_canteen_profile.php" style="background:var(--admin-primary); border:none; color:white; padding:12px 25px; border-radius:12px; text-decoration:none; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);">
            <i class="ri-restaurant-line"></i> Register Canteen
        </a>
    </div>

    <div class="vendor-grid" id="vendorGrid">
        <?php while($r = $restaurants->fetch_assoc()): ?>
        <div class="vendor-card" id="card-<?= $r['id'] ?>">
            <?php 
                $img = !empty($r['image_url']) ? '../../'.$r['image_url'] : 'https://images.unsplash.com/photo-1517248135467-4c7ed9d42339?w=500&q=80';
            ?>
            <img src="<?= $img ?>" class="vendor-img" alt="<?= htmlspecialchars($r['name']) ?>">
            
            <div class="vendor-info">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <h3><?= htmlspecialchars($r['name']) ?></h3>
                    <span class="status-badge status-<?= $r['status'] ?>"><?= $r['status'] ?></span>
                </div>
                
                <div class="vendor-meta">
                    <span><i class="ri-map-pin-2-line"></i> <?= htmlspecialchars($r['location_name'] ?? 'General') ?></span>
                    <span><i class="ri-time-line"></i> <?= date('h:i A', strtotime($r['opening_time'])) ?> - <?= date('h:i A', strtotime($r['closing_time'])) ?></span>
                    <span><i class="ri-star-fill" style="color:#fbbf24;"></i> <?= number_format($r['rating'], 1) ?></span>
                </div>
                
                <div style="font-size:12px; color:var(--admin-text-muted); margin-top:8px;">
                    <i class="ri-mail-line"></i> <?= htmlspecialchars($r['email']) ?><br>
                    <i class="ri-phone-line"></i> <?= htmlspecialchars($r['phone']) ?>
                </div>

                <?php if($r['hospital_name']): ?>
                <div style="margin-top:10px; font-size:11px; background:rgba(99, 102, 241, 0.1); color:#818cf8; padding:5px 10px; border-radius:8px; display:inline-block;">
                    <i class="ri-hospital-line"></i> Linked to: <?= htmlspecialchars($r['hospital_name']) ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="vendor-actions">
                <a href="manage_canteen_profile.php?id=<?= $r['id'] ?>" style="flex:1; background:rgba(255,255,255,0.05); border:1px solid var(--admin-border); color:white; padding:10px; border-radius:10px; cursor:pointer; text-align:center; font-size:14px; font-weight:600; text-decoration:none;"><i class="ri-pencil-line"></i> Edit</a>
                <button onclick="deleteVendor(<?= $r['id'] ?>)" style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); color:#ef4444; width:42px; border-radius:10px; cursor:pointer;"><i class="ri-delete-bin-line"></i></button>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    async function deleteVendor(id) {
        if(!confirm('Delete this canteen? This cannot be undone.')) return;
        const body = new FormData();
        body.append('action', 'delete');
        body.append('id', id);
        try {
            const res = await fetch('api/manage_restaurants.php', { method: 'POST', body: body });
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
