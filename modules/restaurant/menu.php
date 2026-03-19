<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['restaurant_id'])) {
    header("Location: login.php");
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];
$restaurant_name = $_SESSION['restaurant_name'] ?? 'Restaurant Partner';
$current_page = 'menu';

// Fetch Categories
$categories = $conn->query("SELECT * FROM restaurant_categories WHERE restaurant_id = $restaurant_id ORDER BY name ASC");

// Fetch Menu Items
$items_query = "SELECT m.*, c.name as cat_name 
                FROM restaurant_menu m 
                LEFT JOIN restaurant_categories c ON m.category_id = c.id 
                WHERE m.restaurant_id = $restaurant_id 
                ORDER BY m.created_at DESC";
$items = $conn->query($items_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management | Restaurant Partner</title>
    <link rel="stylesheet" href="../../assets/css/restaurant_sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .page-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;
        }

        .page-title { font-size: 26px; font-weight: 800; color: var(--rest-secondary-dark); margin: 0; letter-spacing: -0.5px; }

        .main-content { margin-left: var(--sidebar-width); padding: 40px 50px; transition: all 0.3s ease; }

        .btn-add {
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
            color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(255, 126, 95, 0.3); transition: 0.3s;
        }

        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255, 126, 95, 0.4); }

        .tabs {
            display: flex; gap: 20px; border-bottom: 2px solid #f1f5f9; margin-bottom: 30px;
        }

        .tab {
            padding: 12px 20px; font-weight: 700; font-size: 15px; color: var(--text-muted); cursor: pointer; border-bottom: 3px solid transparent; transition: 0.3s; margin-bottom: -2px;
        }

        .tab.active { color: var(--rest-secondary-dark); border-bottom-color: var(--rest-primary); }

        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Items Grid */
        .items-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;
        }

        .item-card {
            background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; transition: 0.3s; position: relative; display: flex; flex-direction: column;
        }

        .item-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }

        .item-img {
            width: 100%; height: 180px; object-fit: cover; background: #eef2ff; position: relative;
        }

        .item-placeholder {
            width: 100%; height: 180px; display: flex; align-items: center; justify-content: center; background: #eef2ff; color: var(--rest-secondary); font-size: 40px;
        }

        .item-category-badge {
            position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); backdrop-filter: blur(4px); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; color: var(--rest-secondary-dark); box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .item-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .item-title { font-size: 16px; font-weight: 800; color: var(--text-main); margin: 0 0 5px 0; }
        .item-desc { font-size: 12px; color: var(--text-muted); margin: 0 0 15px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; flex-grow: 1; }
        .item-price { font-size: 18px; font-weight: 800; color: var(--rest-primary); margin-bottom: 15px; }

        .item-actions {
            display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px;
        }

        /* Toggle Switch */
        .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #22c55e; }
        input:checked + .slider:before { transform: translateX(20px); }

        .action-btns { display: flex; gap: 8px; }
        .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer; transition: 0.2s; }
        .edit-btn { background: #eef2ff; color: var(--rest-secondary); }
        .edit-btn:hover { background: var(--rest-secondary); color: white; }
        .delete-btn { background: #fee2e2; color: #ef4444; }
        .delete-btn:hover { background: #ef4444; color: white; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
        .modal.active { display: flex; animation: fadeInModal 0.3s ease; }
        
        .modal-content { background: white; border-radius: 20px; width: 500px; max-width: 90%; max-height: 90vh; overflow-y: auto; position: relative; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
        .modal-header { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 10; }
        .modal-title { font-size: 18px; font-weight: 800; color: var(--rest-secondary-dark); margin: 0; }
        .modal-close { background: none; border: none; font-size: 24px; color: var(--text-muted); cursor: pointer; }
        .modal-body { padding: 25px; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 6px; }
        .form-control { width: 100%; padding: 12px 15px; border-radius: 12px; border: 2px solid #f1f5f9; font-family: inherit; font-size: 14px; transition: 0.3s; background: #f8fafc; }
        .form-control:focus { border-color: var(--rest-primary); background: white; outline: none; }
        textarea.form-control { resize: vertical; min-height: 100px; }
        
        /* Category List */
        .category-list { display: flex; flex-direction: column; gap: 10px; max-width: 600px; }
        .category-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: white; border-radius: 12px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .category-name { font-weight: 700; font-size: 15px; color: var(--text-main); }
        .cat-actions { display: flex; gap: 10px; }

        @keyframes fadeInModal { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

        @media (max-width: 1024px) {
            .main-content { padding: 20px; margin-left: 0; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .items-grid { grid-template-columns: 1fr; }
            .mobile-toggle { display: block !important; }
        }
    </style>
</head>
<body class="restaurant-body">

<?php include '../../includes/components/restaurant_sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    <div class="page-header">
        <div class="flex items-center gap-4">
            <i class="ri-menu-line mobile-toggle" id="openSidebarUniversal" style="font-size: 26px; color: var(--rest-secondary-dark); cursor: pointer; display: none;"></i>
            <div>
                <h1 class="page-title">Product Management</h1>
                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Manage your products and categories.</p>
            </div>
        </div>
        <div>
            <button class="btn-add" onclick="openItemModal()"><i class="ri-add-line"></i> Add New Product</button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="switchTab('items', this)">All Items</div>
        <div class="tab" onclick="switchTab('categories', this)">Categories</div>
    </div>

    <!-- Items Tab -->
    <div class="tab-content active" id="tab-items">
        <div class="items-grid">
            <?php if($items && $items->num_rows > 0): while($m = $items->fetch_assoc()): ?>
            <div class="item-card">
                <?php if(!empty($m['image_url'])): ?>
                    <img src="<?= htmlspecialchars($m['image_url']) ?>" class="item-img" alt="<?= htmlspecialchars($m['name']) ?>">
                <?php else: ?>
                    <div class="item-placeholder"><i class="ri-restaurant-line"></i></div>
                <?php endif; ?>
                
                <div class="item-category-badge"><?= htmlspecialchars($m['cat_name'] ?? 'Uncategorized') ?></div>
                
                <div class="item-content">
                    <h3 class="item-title"><?= htmlspecialchars($m['name']) ?></h3>
                    <p class="item-desc"><?= htmlspecialchars($m['description']) ?></p>
                    <div class="item-price">Rs. <?= number_format($m['price'], 2) ?></div>
                    
                    <div class="item-actions">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <label class="toggle-switch">
                                <input type="checkbox" <?= $m['is_available'] ? 'checked' : '' ?> onchange="toggleStatus(<?= $m['id'] ?>, this.checked)">
                                <span class="slider"></span>
                            </label>
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);">In Stock</span>
                        </div>
                        <div class="action-btns">
                            <button class="btn-icon edit-btn" onclick="openItemModal(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)" title="Edit"><i class="ri-edit-2-line"></i></button>
                            <button class="btn-icon delete-btn" onclick="deleteItem(<?= $m['id'] ?>)" title="Delete"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <p style="color: var(--text-muted); padding: 20px;">No items in your menu yet. Click "Add New Item" to start!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Categories Tab -->
    <div class="tab-content" id="tab-categories">
        <div style="margin-bottom: 25px; display: flex; gap: 15px; max-width: 600px;">
            <input type="text" id="newCategoryName" class="form-control" placeholder="New Category Name (e.g., Starters, Main Course)">
            <button class="btn-add" onclick="addCategory()" style="white-space: nowrap;">Add Category</button>
        </div>

        <div class="category-list">
            <?php 
            $categories->data_seek(0);
            if($categories && $categories->num_rows > 0): 
                while($c = $categories->fetch_assoc()): 
            ?>
            <div class="category-item">
                <span class="category-name"><?= htmlspecialchars($c['name']) ?></span>
                <div class="cat-actions">
                    <button class="btn-icon delete-btn" onclick="deleteCategory(<?= $c['id'] ?>)"><i class="ri-delete-bin-line"></i></button>
                </div>
            </div>
            <?php endwhile; else: ?>
            <p style="color: var(--text-muted);">No categories added yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Item Modal -->
<div class="modal" id="itemModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add New Item</h2>
            <button class="modal-close" onclick="closeItemModal()"><i class="ri-close-line"></i></button>
        </div>
        <div class="modal-body">
            <form id="itemForm" onsubmit="saveItem(event)">
                <input type="hidden" name="action" id="formAction" value="add_item">
                <input type="hidden" name="item_id" id="itemId" value="">
                
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="name" id="itemName" class="form-control" required placeholder="e.g., Margherita Pizza">
                </div>
                
                <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Price (Rs.) *</label>
                        <input type="number" step="0.01" name="price" id="itemPrice" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" id="itemCategory" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php 
                            $categories->data_seek(0);
                            while($c = $categories->fetch_assoc()): 
                            ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="itemDesc" class="form-control" placeholder="A brief description of the food..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Food Image</label>
                    <input type="file" name="image" id="itemImage" class="form-control" accept="image/*">
                    <p style="font-size: 11px; margin-top: 5px; color: var(--text-muted);">Leave empty to keep existing image during edit.</p>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_available" id="itemStatus" checked style="width: 18px; height: 18px;">
                    <label style="margin: 0;">Currently Available in Stock</label>
                </div>

                <button type="submit" class="btn-add" style="width: 100%; justify-content: center; padding: 14px; font-size: 16px; margin-top: 10px;">
                    <i class="ri-save-line"></i> Save Item
                </button>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    function switchTab(tabId, el) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    // Modal Operations
    const modal = document.getElementById('itemModal');
    
    function openItemModal(item = null) {
        document.getElementById('itemForm').reset();
        if (item) {
            document.getElementById('modalTitle').innerText = 'Edit Item';
            document.getElementById('formAction').value = 'edit_item';
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemPrice').value = item.price;
            document.getElementById('itemCategory').value = item.category_id;
            document.getElementById('itemDesc').value = item.description;
            document.getElementById('itemStatus').checked = item.is_available == 1;
        } else {
            document.getElementById('modalTitle').innerText = 'Add New Item';
            document.getElementById('formAction').value = 'add_item';
            document.getElementById('itemId').value = '';
            document.getElementById('itemStatus').checked = true;
        }
        modal.classList.add('active');
    }

    function closeItemModal() {
        modal.classList.remove('active');
    }

    // Close modal on outside click
    window.onclick = function(event) {
        if (event.target == modal) closeItemModal();
    }

    // AJAX Operations
    function addCategory() {
        const name = document.getElementById('newCategoryName').value;
        if (!name) return Swal.fire('Error', 'Please enter a category name', 'error');

        const fd = new FormData();
        fd.append('action', 'add_category');
        fd.append('category_name', name);

        fetch('handlers/menu_handler.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(res => {
            if(res.success) window.location.reload();
            else Swal.fire('Error', res.message, 'error');
        });
    }

    function deleteCategory(id) {
        Swal.fire({
            title: 'Are you sure?', text: "Deleting a category will NOT delete the items in it, but they will be marked as uncategorized.",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#ff4757', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('action', 'delete_category');
                fd.append('id', id);
                fetch('handlers/menu_handler.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if(res.success) window.location.reload();
                });
            }
        });
    }

    function saveItem(e) {
        e.preventDefault();
        const form = document.getElementById('itemForm');
        const fd = new FormData(form);

        fetch('handlers/menu_handler.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                Swal.fire('Saved!', res.message, 'success').then(() => window.location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    }

    function deleteItem(id) {
        Swal.fire({
            title: 'Delete Item?', text: "You won't be able to revert this!",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('action', 'delete_item');
                fd.append('id', id);
                fetch('handlers/menu_handler.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if(res.success) window.location.reload();
                });
            }
        });
    }

    function toggleStatus(id, status) {
        const fd = new FormData();
        fd.append('action', 'toggle_status');
        fd.append('id', id);
        fd.append('status', status ? 1 : 0);
        fetch('handlers/menu_handler.php', { method: 'POST', body: fd });
    }
</script>
</body>
</html>
