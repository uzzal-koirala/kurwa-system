<?php
session_start();
require_once '../../includes/core/config.php';

// Check if pharmacy is logged in
if (!isset($_SESSION['pharmacy_id'])) {
    header("Location: login.php");
    exit();
}

$pharmacy_id = $_SESSION['pharmacy_id'];
$current_page = 'inventory';

// Handle Add/Edit Medicine (Simplified for now)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $category = $conn->real_escape_string($_POST['category']);
    $req_presc = isset($_POST['requires_prescription']) ? 1 : 0;
    
    // Default image if none provided
    $img_url = $_POST['image_url'] ?: 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=300&q=80';

    if ($_POST['action'] === 'add') {
        $sql = "INSERT INTO medicines (pharmacy_id, name, description, price, category, requires_prescription, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdsis", $pharmacy_id, $name, $desc, $price, $category, $req_presc, $img_url);
        $stmt->execute();
        $_SESSION['msg'] = "Medicine added successfully!";
    } elseif ($_POST['action'] === 'edit') {
        $id = intval($_POST['medicine_id']);
        $sql = "UPDATE medicines SET name=?, description=?, price=?, category=?, requires_prescription=?, image_url=? WHERE id=? AND pharmacy_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsisii", $name, $desc, $price, $category, $req_presc, $img_url, $id, $pharmacy_id);
        $stmt->execute();
        $_SESSION['msg'] = "Medicine updated successfully!";
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['medicine_id']);
        $conn->query("DELETE FROM medicines WHERE id=$id AND pharmacy_id=$pharmacy_id");
        $_SESSION['msg'] = "Medicine removed!";
    }
    
    header("Location: inventory.php");
    exit();
}

// Fetch all medicines
$medicines = [];
$res = $conn->query("SELECT * FROM medicines WHERE pharmacy_id = $pharmacy_id ORDER BY created_at DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $medicines[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Inventory | Kurwa Pharmacy</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/restaurant_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/pharmacy_dashboard.css">
    <style>
        /* Specific Inventory CSS Extensions */
        .inventory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .add-btn {
            background: #059669;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);
        }
        
        .add-btn:hover {
            background: #047857;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3);
        }

        .med-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .med-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            border: 1px solid rgba(15, 118, 110, 0.05);
            transition: 0.2s;
        }

        .med-card:hover {
            box-shadow: 0 10px 25px -5px rgba(15, 118, 110, 0.1);
        }

        .med-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #f1f5f9;
        }

        .med-info {
            padding: 20px;
        }

        .med-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .med-header h3 {
            font-size: 18px;
            color: #0f172a;
            font-weight: 700;
        }

        .med-price {
            background: #f0fdfa;
            color: #0d9488;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
        }

        .med-category {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .rx-badge {
            background: #fef2f2;
            color: #ef4444;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .med-desc {
            color: #475569;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .med-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid #f1f5f9;
            padding-top: 16px;
        }

        .med-action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .edit-btn {
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
        }
        
        .edit-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .del-btn {
            background: #fef2f2;
            color: #ef4444;
        }

        .del-btn:hover {
            background: #fee2e2;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 24px;
            width: 100%;
            max-width: 550px;
            padding: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            background: #f8fafc;
            color: #0f172a;
            font-family: inherit;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #059669;
            background: white;
            box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1);
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fef2f2;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid #fecaca;
            cursor: pointer;
        }
        
        .checkbox-wrapper input {
            width: 20px;
            height: 20px;
            accent-color: #ef4444;
        }
        
        .checkbox-wrapper label {
            margin: 0;
            color: #b91c1c;
            font-weight: 600;
            cursor: pointer;
        }

        .alert-msg {
            background: #ecfdf5;
            color: #059669;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <?php include '../../includes/components/pharmacy_sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="inventory-header">
            <div>
                <h1 style="font-size:28px; font-weight:700; color:#0f172a; margin-bottom:4px; letter-spacing:-0.5px;">Medicine Inventory</h1>
                <p style="color:#64748b; font-size:15px;">Manage your store's catalog and stock.</p>
            </div>
            <button class="add-btn" onclick="openModal('add')">
                <i class="ri-add-line" style="font-size:20px;"></i> Add New Medicine
            </button>
        </div>

        <?php if(isset($_SESSION['msg'])): ?>
            <div class="alert-msg">
                <i class="ri-checkbox-circle-fill" style="font-size:20px;"></i>
                <?= $_SESSION['msg'] ?>
                <?php unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="med-grid">
            <?php if(empty($medicines)): ?>
                <div style="grid-column:1/-1; text-align:center; padding:60px 20px; background:white; border-radius:20px; border:1px dashed #cbd5e1;">
                    <i class="ri-medicine-bottle-line" style="font-size:64px; color:#94a3b8; margin-bottom:16px; display:block;"></i>
                    <h3 style="color:#0f172a; margin-bottom:8px;">Your Inventory is Empty</h3>
                    <p style="color:#64748b; margin-bottom:24px;">Start adding medicines to your digital storefront so patients can find them.</p>
                    <button class="add-btn" style="margin:0 auto;" onclick="openModal('add')">Add Your First Medicine</button>
                </div>
            <?php else: ?>
                <?php foreach($medicines as $med): ?>
                    <div class="med-card">
                        <img src="<?= htmlspecialchars($med['image_url']) ?>" alt="Medicine Image" class="med-img">
                        <div class="med-info">
                            <div class="med-header">
                                <h3><?= htmlspecialchars($med['name']) ?></h3>
                                <div class="med-price">Rs. <?= number_format($med['price'], 2) ?></div>
                            </div>
                            <div class="med-category">
                                <i class="ri-flask-line"></i> <?= htmlspecialchars($med['category']) ?>
                                <?php if($med['requires_prescription']): ?>
                                    <span class="rx-badge" style="margin-left:auto;"><i class="ri-file-list-3-fill"></i> Rx Required</span>
                                <?php endif; ?>
                            </div>
                            <p class="med-desc"><?= htmlspecialchars($med['description']) ?></p>
                            
                            <div class="med-actions">
                                <button class="med-action-btn edit-btn" onclick='openModal("edit", <?= json_encode($med) ?>)'>
                                    <i class="ri-edit-line"></i> Edit Details
                                </button>
                                <form method="POST" style="flex:1;" onsubmit="return confirm('Delete this medicine permanently?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="medicine_id" value="<?= $med['id'] ?>">
                                    <button type="submit" class="med-action-btn del-btn" style="width:100%;">
                                        <i class="ri-delete-bin-line"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal for Add/Edit -->
    <div id="medModal" class="modal">
        <div class="modal-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <h2 id="modalTitle" style="font-size:24px; color:#0f172a; font-weight:700;">Add Medicine</h2>
                <i class="ri-close-line" onclick="closeModal()" style="font-size:28px; cursor:pointer; color:#94a3b8; transition:0.2s;"></i>
            </div>
            
            <form method="POST" id="medForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="medicine_id" id="medId" value="">
                
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Medicine Name</label>
                        <input type="text" name="name" id="medName" placeholder="e.g. Paracetamol 500mg" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Price (Rs.)</label>
                        <input type="number" name="price" id="medPrice" placeholder="0.00" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="medCategory">
                            <option value="General">General</option>
                            <option value="Painkiller">Painkiller</option>
                            <option value="Antibiotic">Antibiotic</option>
                            <option value="Vitamins">Vitamins & Supplements</option>
                            <option value="First Aid">First Aid</option>
                            <option value="Syrup">Syrup</option>
                        </select>
                    </div>
                    
                    <div class="form-group full">
                        <label>Image URL (Optional)</label>
                        <input type="url" name="image_url" id="medImage" placeholder="https://...">
                    </div>
                    
                    <div class="form-group full">
                        <label>Description & Dosage</label>
                        <textarea name="description" id="medDesc" rows="3" placeholder="Describe the medicine usage..."></textarea>
                    </div>
                    
                    <div class="form-group full">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="requires_prescription" id="medRx">
                            <span>Requires Doctor's Prescription (Rx)</span>
                        </label>
                    </div>
                </div>
                
                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:10px;">
                    <button type="button" onclick="closeModal()" style="padding:14px 24px; border-radius:12px; border:1px solid #e2e8f0; background:white; font-weight:600; cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding:14px 24px; border-radius:12px; border:none; background:#059669; color:white; font-weight:600; cursor:pointer; box-shadow:0 4px 6px -1px rgba(5,150,105,0.2);">Save Medicine</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
    <script>
        function openModal(mode, data = null) {
            document.getElementById('medModal').style.display = 'flex';
            const form = document.getElementById('medForm');
            
            if (mode === 'add') {
                document.getElementById('modalTitle').innerText = 'Add New Medicine';
                document.getElementById('formAction').value = 'add';
                form.reset();
            } else if (mode === 'edit' && data) {
                document.getElementById('modalTitle').innerText = 'Edit Medicine';
                document.getElementById('formAction').value = 'edit';
                
                document.getElementById('medId').value = data.id;
                document.getElementById('medName').value = data.name;
                document.getElementById('medPrice').value = data.price;
                document.getElementById('medCategory').value = data.category;
                document.getElementById('medImage').value = data.image_url;
                document.getElementById('medDesc').value = data.description;
                document.getElementById('medRx').checked = data.requires_prescription == 1;
            }
        }

        function closeModal() {
            document.getElementById('medModal').style.display = 'none';
        }
    </script>
</body>
</html>
