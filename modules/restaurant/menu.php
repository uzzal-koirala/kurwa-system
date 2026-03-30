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

        /* Categories Grid */
        .category-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .category-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .category-card:hover {
            transform: translateY(-5px);
            background: white;
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.12);
            border-color: var(--rest-primary);
        }

        .category-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: #fff2ed;
            color: var(--rest-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 16px;
            transition: 0.3s;
        }

        .category-card:hover .category-icon-wrapper {
            background: var(--rest-primary);
            color: white;
            transform: scale(1.1) rotate(5deg);
        }

        .category-info h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--rest-secondary-dark);
            margin: 0 0 4px 0;
        }

        .category-info p {
            font-size: 13px;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }

        .category-actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            opacity: 1; /* Always visible for better UX on touch */
        }

        .category-header-banner {
            background: linear-gradient(135deg, var(--rest-secondary-dark) 0%, #0f172a 100%);
            border-radius: 28px;
            padding: 35px 45px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.3);
            position: relative;
            overflow: hidden;
            flex-wrap: wrap;
            gap: 25px;
        }

        .category-header-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--rest-primary) 0%, transparent 70%);
            opacity: 0.15;
            filter: blur(40px);
            border-radius: 50%;
        }

        .category-banner-content {
            position: relative;
            z-index: 2;
        }

        .category-banner-content h2 {
            color: white;
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }

        .category-banner-content h2 i {
            background: rgba(234, 88, 12, 0.15);
            color: var(--rest-primary);
            padding: 8px;
            border-radius: 12px;
            font-size: 22px;
        }

        .category-banner-content p {
            color: rgba(255, 255, 255, 0.65);
            font-size: 15px;
            margin: 0;
            max-width: 420px;
            line-height: 1.6;
            font-weight: 400;
        }

        .add-category-wrapper {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 8px;
            border-radius: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-grow: 1;
            max-width: 550px;
            position: relative;
            z-index: 2;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
        }
        
        .add-category-wrapper:focus-within {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3), 0 0 0 4px rgba(234, 88, 12, 0.15);
        }

        .add-category-input-group {
            position: relative;
            flex-grow: 1;
        }

        .add-category-input-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 20px;
            transition: 0.3s;
        }

        .add-category-input-group .form-control {
            padding-left: 55px;
            height: 56px;
            font-size: 15px;
            background: transparent;
            border: none;
            color: white;
            font-weight: 500;
            width: 100%;
            outline: none;
            box-shadow: none;
        }

        .add-category-input-group .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
            font-weight: 400;
        }

        .add-category-input-group .form-control:focus ~ i {
            color: var(--rest-primary);
        }

        .btn-create-cat {
            background: linear-gradient(135deg, var(--rest-primary) 0%, #d04d08 100%);
            color: white;
            border: none;
            height: 56px;
            padding: 0 28px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-create-cat:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(234, 88, 12, 0.4);
            background: linear-gradient(135deg, #f97316 0%, var(--rest-primary) 100%);
        }

        @keyframes fadeInModal { 
            from { opacity: 0; transform: scale(0.95); } 
            to { opacity: 1; transform: scale(1); } 
        }

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
        <div class="category-header-banner">
            <div class="category-banner-content">
                <h2><i class="ri-folder-open-fill"></i> Organize Your Menu</h2>
                <p>Create distinct categories like "Starters", "Main Course", or "Beverages" to seamlessly organize your culinary offerings and enhance the customer ordering experience.</p>
            </div>
            
            <div class="add-category-wrapper">
                <div class="add-category-input-group">
                    <input type="text" id="newCategoryName" class="form-control" placeholder="Type new category name...">
                    <i class="ri-grid-fill"></i>
                </div>
                <button class="btn-create-cat" onclick="addCategory()">
                    <i class="ri-add-line"></i> Create
                </button>
            </div>
        </div>

        <div class="category-list">
            <?php 
            $categories->data_seek(0);
            if($categories && $categories->num_rows > 0): 
                while($c = $categories->fetch_assoc()): 
                    // Fetch product count for this category
                    $cat_id = $c['id'];
                    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM restaurant_menu WHERE category_id = ?");
                    $count_stmt->bind_param("i", $cat_id);
                    $count_stmt->execute();
                    $items_count = $count_stmt->get_result()->fetch_assoc()['total'];
                    $count_stmt->close();
            ?>
            <div class="category-card">
                <div class="category-icon-wrapper">
                    <i class="ri-restaurant-2-line"></i>
                </div>
                <div class="category-info">
                    <h3><?= htmlspecialchars($c['name']) ?></h3>
                    <p><?= $items_count ?> Products</p>
                </div>
                <div class="category-actions">
                    <button class="btn-icon delete-btn" onclick="deleteCategory(<?= $c['id'] ?>)" title="Delete Category">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: white; border-radius: 24px; border: 2px dashed #f1f5f9;">
                <i class="ri-folder-open-line" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                <p style="color: var(--text-muted); font-weight: 500;">No categories added yet. Start by creating your first category above!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Item Modal -->
<div class="modal" id="itemModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">
                <i class="ri-restaurant-line"></i> 
                <span>Add New Item</span>
            </h2>
            <button class="modal-close" onclick="closeItemModal()"><i class="ri-close-line"></i></button>
        </div>
        <div class="modal-body">
            <form id="itemForm" onsubmit="saveItem(event)">
                <input type="hidden" name="action" id="formAction" value="add_item">
                <input type="hidden" name="item_id" id="itemId" value="">
                
                <div class="input-group">
                    <input type="text" name="name" id="itemName" class="form-control" required placeholder="Product Name">
                    <i class="ri-edit-line"></i>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="input-group">
                        <input type="number" step="0.01" name="price" id="itemPrice" class="form-control" required placeholder="Price (Rs)">
                        <i class="ri-price-tag-3-line"></i>
                    </div>
                    <div class="input-group">
                        <select name="category_id" id="itemCategory" class="form-control" required style="padding-left: 48px;">
                            <option value="">Select Category</option>
                            <?php 
                            $categories->data_seek(0);
                            while($c = $categories->fetch_assoc()): 
                            ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <i class="ri-folder-line"></i>
                    </div>
                </div>

                <div class="input-group">
                    <textarea name="description" id="itemDesc" class="form-control" placeholder="Product Description (optional)"></textarea>
                </div>
                
                <div class="upload-zone" id="uploadZone">
                    <i class="ri-image-add-line"></i>
                    <p id="uploadText">Click or Drag image here to upload</p>
                    <input type="file" name="image" id="itemImage" accept="image/*" onchange="previewImage(this)">
                </div>
                <div class="preview-container" id="imagePreviewContainer">
                    <img id="imagePreview" src="" alt="Preview">
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; background: #f8fafc; padding: 12px; border-radius: 12px; border: 1px solid #f1f5f9;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_available" id="itemStatus" checked>
                        <span class="slider"></span>
                    </label>
                    <span style="font-size: 14px; font-weight: 600; color: #475569;">Available in Stock</span>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="ri-save-line"></i> Save Product Details
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
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImageEl = document.getElementById('imagePreview');
    const uploadText = document.getElementById('uploadText');
    
    function openItemModal(item = null) {
        document.getElementById('itemForm').reset();
        previewContainer.style.display = 'none';
        uploadText.innerText = 'Click or Drag image here to upload';
        
        if (item) {
            document.querySelector('#modalTitle span').innerText = 'Edit Product Details';
            document.getElementById('formAction').value = 'edit_item';
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemPrice').value = item.price;
            document.getElementById('itemCategory').value = item.category_id;
            document.getElementById('itemDesc').value = item.description;
            document.getElementById('itemStatus').checked = item.is_available == 1;
            
            if (item.image_url) {
                previewImageEl.src = '../../' + item.image_url;
                previewContainer.style.display = 'block';
                uploadText.innerText = 'Change product image';
            }
        } else {
            document.querySelector('#modalTitle span').innerText = 'Add New Product';
            document.getElementById('formAction').value = 'add_item';
            document.getElementById('itemId').value = '';
            document.getElementById('itemStatus').checked = true;
        }
        modal.classList.add('active');
    }

    function closeItemModal() {
        modal.classList.remove('active');
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImageEl.src = e.target.result;
                previewContainer.style.display = 'block';
                uploadText.innerText = 'Image selected: ' + input.files[0].name;
            }
            reader.readAsDataURL(input.files[0]);
        }
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
