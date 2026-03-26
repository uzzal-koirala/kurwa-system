<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "caretaker_categories";

// Fetch all categories
$categories = $conn->query("SELECT * FROM caretaker_categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .cat-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--admin-border);
            border-radius: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }
        .cat-card:hover {
            border-color: var(--admin-primary);
            background: rgba(99, 102, 241, 0.05);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <div>
            <h1>Caretaker Categories</h1>
            <p style="color:var(--admin-text-muted); font-size:14px;">Manage service types available in the ecosystem.</p>
        </div>
        <button onclick="openModal('add')" style="background:var(--admin-primary); border:none; color:white; padding:12px 25px; border-radius:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px;">
            <i class="ri-add-line text-xl"></i> Create Category
        </button>
    </div>

    <div class="admin-panel-box" style="background:transparent; border:none; padding:0;">
        <div class="grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">
            <?php while($cat = $categories->fetch_assoc()): ?>
            <div class="cat-card" id="cat-<?= $cat['id'] ?>">
                <div style="display:flex; align-items:center; gap:15px;">
                    <div style="width:45px; height:45px; background:rgba(99, 102, 241, 0.1); color:var(--admin-primary); border-radius:12px; display:flex; align-items:center; justify-content:center;">
                        <i class="ri-bookmark-3-line text-2xl"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:16px; font-weight:700;"><?= htmlspecialchars($cat['name']) ?></h3>
                        <span style="font-size:12px; color:var(--admin-text-muted);">Active Category</span>
                    </div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button onclick='openModal("edit", <?= json_encode($cat) ?>)' style="background:rgba(255,255,255,0.05); border:1px solid var(--admin-border); color:white; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-pencil-line"></i></button>
                    <button onclick="deleteCategory(<?= $cat['id'] ?>)" style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); color:#ef4444; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-delete-bin-line"></i></button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div id="catModal" class="admin-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div style="background:#0f172a; border:1px solid var(--admin-border); width:400px; border-radius:24px; padding:30px; position:relative;">
        <h2 id="modalTitle" style="margin-top:0; margin-bottom:20px;">Create Category</h2>
        <form id="catForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="catId">
            
            <div class="form-group">
                <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:8px;">Category Name</label>
                <input type="text" name="name" id="f_name" placeholder="e.g. Specialized Nursing" required style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:12px; padding:12px; color:white; outline:none; transition:all 0.3s;" onfocus="this.style.borderColor='var(--admin-primary)'" onblur="this.style.borderColor='var(--admin-border)'">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:30px;">
                <button type="button" onclick="closeModal()" style="background:transparent; border:1px solid var(--admin-border); color:white; padding:12px 20px; border-radius:12px; cursor:pointer; font-weight:600;">Cancel</button>
                <button type="submit" style="background:var(--admin-primary); border:none; color:white; padding:12px 25px; border-radius:12px; font-weight:700; cursor:pointer;">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
function openModal(mode, data = null) {
    const modal = document.getElementById('catModal');
    const form = document.getElementById('catForm');
    const title = document.getElementById('modalTitle');
    const action = document.getElementById('formAction');
    
    form.reset();
    action.value = mode;
    
    if (mode === 'add') {
        title.innerText = 'Create Category';
        document.getElementById('catId').value = '';
    } else {
        title.innerText = 'Edit Category';
        document.getElementById('catId').value = data.id;
        document.getElementById('f_name').value = data.name;
    }
    
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('catModal').style.display = 'none';
}

document.getElementById('catForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/manage_categories.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    }
});

async function deleteCategory(id) {
    if (!confirm('Are you sure? This will remove the category from the system.')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    try {
        const response = await fetch('api/manage_categories.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('cat-' + id).remove();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
</script>
</body>
</html>
