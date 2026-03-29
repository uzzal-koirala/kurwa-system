<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "caretakers";

$caretakers = $conn->query("
    SELECT c.*, l.name as location_name, h.name as hospital_name_db 
    FROM caretakers c 
    LEFT JOIN locations l ON c.location_id = l.id 
    LEFT JOIN hospitals h ON c.hospital_id = h.id 
    ORDER BY c.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caretaker Management | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <div>
            <h1>Caretaker Database</h1>
            <p style="color:var(--admin-text-muted); font-size:14px; margin-top:5px;">Manage nursing experts, health assistants and care providers.</p>
        </div>
        <a href="manage_caretaker_profile.php" style="background:var(--admin-primary); border:none; color:white; padding:12px 25px; border-radius:12px; font-weight:700; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:8px; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);">
            <i class="ri-user-add-line"></i> Add New Expert
        </a>
    </div>

    <div class="admin-stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
        <div class="admin-stat-card" style="padding: 15px;">
            <div class="admin-stat-info">
                <span>Total Experts</span>
                <h2><?= $caretakers->num_rows ?></h2>
            </div>
        </div>
        <div class="admin-stat-card" style="padding: 15px;">
            <div class="admin-stat-info">
                <span>Avg Rating</span>
                <h2>4.8</h2>
            </div>
        </div>
        <div class="admin-stat-card" style="padding: 15px;">
            <div class="admin-stat-info">
                <span>Verified %</span>
                <h2>98%</h2>
            </div>
        </div>
        <div class="admin-stat-card" style="padding: 15px;">
            <div class="admin-stat-info">
                <span>Patients Helped</span>
                <h2>1.5K+</h2>
            </div>
        </div>
    </div>

    <div class="admin-panel-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Expert</th>
                    <th>Category</th>
                    <th>Experience</th>
                    <th>Rate/Day</th>
                    <th>Assignment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($ct = $caretakers->fetch_assoc()): ?>
                <tr id="row-<?= $ct['id'] ?>">
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <?php 
                                $img = $ct['image_url'];
                                if(!empty($img) && !str_starts_with($img, 'http')) {
                                    $img = '../../' . $img;
                                } else if(empty($img)) {
                                    $img = '../../assets/images/defaults/expert.png';
                                }
                            ?>
                            <img src="<?= htmlspecialchars($img) ?>" style="width:45px; height:45px; border-radius:14px; object-fit:cover;">
                            <div>
                                <div style="font-weight:700;"><?= htmlspecialchars($ct['full_name']) ?></div>
                                <div style="font-size:11px; color:var(--admin-primary); font-weight:600;"><?= htmlspecialchars($ct['specialization']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="admin-badge" style="background:rgba(99, 102, 241, 0.1); color:#818cf8;"><?= htmlspecialchars($ct['category']) ?></span></td>
                    <td><?= $ct['experience_years'] ?> Years</td>
                    <td style="font-weight:700;">Rs. <?= number_format($ct['price_per_day']) ?></td>
                    <td>
                        <?php if($ct['location_name']): ?>
                            <div style="font-size:11px; color:var(--admin-text-muted); display:flex; align-items:center; gap:4px; margin-bottom:4px;">
                                <i class="ri-map-pin-2-line" style="color:var(--admin-primary);"></i> <?= htmlspecialchars($ct['location_name']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if($ct['hospital_name_db']): ?>
                            <div style="font-size:10px; color:white; background:rgba(255,255,255,0.05); padding:2px 8px; border-radius:6px; display:inline-block; border:1px solid var(--admin-border);">
                                <i class="ri-hospital-line"></i> <?= htmlspecialchars($ct['hospital_name_db']) ?>
                            </div>
                        <?php else: ?>
                            <span style="font-size:10px; color:var(--admin-text-muted);">Self-Employed</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <a href="manage_caretaker_profile.php?id=<?= $ct['id'] ?>" style="background:rgba(255,255,255,0.05); border:1px solid var(--admin-border); color:white; width:35px; height:35px; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; text-decoration:none;"><i class="ri-pencil-line"></i></a>
                            <button onclick="deleteCaretaker(<?= $ct['id'] ?>)" style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); color:#ef4444; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-delete-bin-line"></i></button>
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
    async function deleteCaretaker(id) {
        if(!confirm('Delete this expert profile permanently?')) return;
        const body = new FormData();
        body.append('action', 'delete');
        body.append('id', id);
        try {
            const res = await fetch('api/manage_caretaker.php', { method: 'POST', body: body });
            const result = await res.json();
            if(result.success) { 
                document.getElementById('row-'+id).remove(); 
            } else { 
                alert(result.message); 
            }
        } catch(err) { alert('Deletion failed.'); }
    }
</script>
</body>
</html>
