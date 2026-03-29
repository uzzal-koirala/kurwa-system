<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "orders";

// Unified Service Tracking (Across all categories)
$orders_sql = "
    (SELECT 'Caretaker' as category, b.id, b.created_at, c.full_name as provider, u.full_name as customer, b.total_price as amount, b.status 
     FROM caretaker_bookings b 
     JOIN caretakers c ON b.caretaker_id = c.id 
     JOIN users u ON b.user_id = u.id)
    UNION ALL
    (SELECT 'Pharmacy' as category, m.id, m.created_at, p.name as provider, u.full_name as customer, m.total_price as amount, m.status 
     FROM medicine_orders m 
     JOIN pharmacies p ON m.pharmacy_id = p.id 
     JOIN users u ON m.user_id = u.id)
    UNION ALL
    (SELECT 'Canteen' as category, o.id, o.created_at, r.name as provider, u.full_name as customer, o.total_amount as amount, o.status 
     FROM restaurant_orders o 
     JOIN restaurants r ON o.restaurant_id = r.id 
     JOIN users u ON o.user_id = u.id)
    ORDER BY created_at DESC LIMIT 20";

$orders = $conn->query($orders_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Orders | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <h1>Global Service Orders</h1>
        <div style="display:flex; gap:15px;">
             <div class="stat-badge" style="background:var(--admin-card-bg); padding:10px 20px; border-radius:12px; border:1px solid var(--admin-border);">
                <span style="color:var(--admin-text-muted); font-size:12px;">Processable:</span> <span style="font-weight:700;">12</span>
             </div>
        </div>
    </div>

    <div class="admin-panel-box">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Customer</th>
                    <th>Provider</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && $orders->num_rows > 0): while($ord = $orders->fetch_assoc()): ?>
                <tr>
                    <td><span style="font-family:monospace; color:var(--admin-primary);">#ORD-<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($ord['customer']) ?></td>
                    <td><?= htmlspecialchars($ord['provider']) ?></td>
                    <td>
                        <?php 
                        $icon = "ri-pulse-line";
                        if($ord['category'] == 'Pharmacy') $icon = "ri-capsule-line";
                        if($ord['category'] == 'Canteen') $icon = "ri-restaurant-line";
                        ?>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <i class="<?= $icon ?>" style="color:var(--admin-text-muted);"></i>
                            <span><?= $ord['category'] ?></span>
                        </div>
                    </td>
                    <td style="font-weight:700;">Rs. <?= number_format($ord['amount']) ?></td>
                    <td>
                        <span class="admin-badge <?= (strtolower($ord['status']) == 'confirmed' || strtolower($ord['status']) == 'delivered' || strtolower($ord['status']) == 'completed') ? 'admin-badge-success' : 'admin-badge-warning' ?>">
                            <?= ucfirst($ord['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if($ord['category'] == 'Canteen'): ?>
                            <button onclick="viewAdminOrderDetails(<?= $ord['id'] ?>, '<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?>')" style="background:rgba(99, 102, 241, 0.1); border:1px solid rgba(99, 102, 241, 0.2); color:var(--admin-primary); width:32px; height:32px; border-radius:8px; cursor:pointer;"><i class="ri-eye-line"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--admin-text-muted);">No orders found in system.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<!-- Order details modal for Admin -->
<div id="adminOrderModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); align-items:center; justify-content:center;">
    <div style="background:var(--admin-card-bg); border:1px solid var(--admin-border); padding:30px; border-radius:24px; width:100%; max-width:500px; box-shadow:0 10px 40px rgba(0,0,0,0.5);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0; font-size:18px; font-weight:800;">Receipt #ORD-<span id="mo_order_id">00000</span></h2>
            <button onclick="document.getElementById('adminOrderModal').style.display='none'" style="background:none; border:none; color:var(--admin-text-muted); cursor:pointer; font-size:24px;"><i class="ri-close-line"></i></button>
        </div>
        
        <div id="mo_items_container" style="max-height:400px; overflow-y:auto; margin-bottom:20px;">
            <!-- Items will be injected here -->
        </div>

        <div id="mo_overall_notes" style="display:none; padding:15px; background:rgba(255,255,255,0.02); border:1px solid var(--admin-border); border-radius:12px; margin-bottom:20px;">
            <p id="mo_overall_notes_text" style="margin:0; font-size:12px; color:var(--admin-text-muted);"></p>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
async function viewAdminOrderDetails(id, displayId) {
    const modal = document.getElementById('adminOrderModal');
    const container = document.getElementById('mo_items_container');
    const overallNotesDiv = document.getElementById('mo_overall_notes');
    const overallNotesText = document.getElementById('mo_overall_notes_text');
    
    document.getElementById('mo_order_id').innerText = displayId;
    container.innerHTML = '<div style="text-align:center; padding:30px; color:var(--admin-text-muted);"><i class="ri-loader-4-line ri-spin" style="font-size:24px;"></i></div>';
    overallNotesDiv.style.display = 'none';
    modal.style.display = 'flex';

    try {
        // We reuse the restaurant handler or create an admin one. 
        // For simplicity and since Admin has permission, we'll fetch via a slightly different route or a dedicated admin handler.
        // Let's create an admin handler to be clean.
        const res = await fetch(`api/fetch_order_details.php?order_id=${id}`);
        const data = await res.json();
        
        if(data.success) {
            let html = '';
            data.items.forEach(item => {
                let noteHtml = item.special_notes ? `<div style="margin-top:5px; font-size:11px; color:#ef4444;"><i class="ri-sticky-note-line"></i> Note: ${item.special_notes}</div>` : '';
                html += `
                <div style="padding:12px; border-bottom:1px solid var(--admin-border); display:flex; flex-direction:column;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-weight:600; color:white;">${item.quantity}x ${item.name}</span>
                        <span style="font-weight:700; color:var(--admin-primary);">Rs. ${(item.quantity * item.price).toLocaleString()}</span>
                    </div>
                    ${noteHtml}
                </div>`;
            });
            container.innerHTML = html;

            if(data.overall_notes) {
                overallNotesText.innerText = "Order Note: " + data.overall_notes;
                overallNotesDiv.style.display = 'block';
            }
        } else {
            container.innerHTML = `<div style="text-align:center; padding:30px; color:#ef4444;">${data.message}</div>`;
        }
    } catch(err) {
        container.innerHTML = `<div style="text-align:center; padding:30px; color:#ef4444;">Connection error.</div>`;
    }
}
</script>
</body>
</html>
