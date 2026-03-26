<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "caretakers";

$caretakers = $conn->query("SELECT * FROM caretakers ORDER BY created_at DESC");
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
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="admin-header">
        <h1>Caretaker Directory</h1>
        <button onclick="openModal('add')" style="background:var(--admin-primary); border:none; color:white; padding:10px 25px; border-radius:12px; font-weight:700; cursor:pointer;">
            <i class="ri-add-line"></i> Add New Expert
        </button>
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
                <span>Total Patients</span>
                <h2>1.2k+</h2>
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
                    <th>Rate</th>
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
                                if(!str_starts_with($img, 'http')) {
                                    $img = '../../' . $img;
                                }
                            ?>
                            <img src="<?= htmlspecialchars($img) ?>" style="width:45px; height:45px; border-radius:14px; object-fit:cover;">
                            <div>
                                <div style="font-weight:700;"><?= htmlspecialchars($ct['full_name']) ?></div>
                                <div style="font-size:12px; color:var(--admin-primary); font-weight:600;"><?= htmlspecialchars($ct['specialization']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="admin-badge" style="background:rgba(99, 102, 241, 0.1); color:#818cf8;"><?= htmlspecialchars($ct['category']) ?></span></td>
                    <td><?= $ct['experience_years'] ?> Years</td>
                    <td style="font-weight:700;">Rs. <?= number_format($ct['price_per_day']) ?></td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button onclick='openModal("edit", <?= json_encode($ct) ?>)' style="background:rgba(255,255,255,0.05); border:1px solid var(--admin-border); color:white; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-pencil-line"></i></button>
                            <button onclick="deleteCaretaker(<?= $ct['id'] ?>)" style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); color:#ef4444; width:35px; height:35px; border-radius:10px; cursor:pointer;"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="manageModal" class="admin-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div style="background:#0f172a; border:1px solid var(--admin-border); width:600px; border-radius:24px; padding:30px; position:relative; max-height:95vh; overflow-y:auto;">
        <h2 id="modalTitle" style="margin-top:0; margin-bottom:20px;">Add New Expert</h2>
        <form id="caretakerForm" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="caretakerId">
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <div class="form-group">
                    <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Full Name</label>
                    <input type="text" name="full_name" id="f_full_name" required style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white;">
                </div>
                <div class="form-group">
                    <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Category</label>
                    <select name="category" id="f_category" required style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white; outline:none;">
                        <option value="">Select Category</option>
                        <?php 
                        $cat_list = $conn->query("SELECT name FROM caretaker_categories ORDER BY name ASC");
                        while($cl = $cat_list->fetch_assoc()): ?>
                            <option value="<?= $cl['name'] ?>"><?= $cl['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:15px;">
                <div class="form-group">
                    <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Specialization</label>
                    <input type="text" name="specialization" id="f_specialization" required style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white;">
                </div>
                <div class="form-group">
                    <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Experience (Years)</label>
                    <input type="number" name="experience_years" id="f_experience" value="0" style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:15px;">
                <div class="form-group">
                    <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Price Per Day (Rs.)</label>
                    <input type="number" name="price_per_day" id="f_price" required style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white;">
                </div>
                <div class="form-group">
                    <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Patients Helped</label>
                    <input type="number" name="patients_helped" id="f_patients" value="0" style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white;">
                </div>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Profile Photo</label>
                <div id="dropzone" class="upload-dropzone">
                    <i class="ri-upload-cloud-2-line"></i>
                    <span>Drag and drop image here or click to browse</span>
                    <input type="file" name="image_file" id="f_image_file" accept="image/*" style="display:none;">
                </div>
                <div id="previewContainer" class="image-preview-container">
                    <img id="imgPreview" src="" alt="Preview">
                </div>
                <input type="hidden" name="image_url" id="f_image_url">
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">Video URL (Intro Video)</label>
                <input type="text" name="video_url" id="f_video_url" placeholder="YouTube or Video path" style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white;">
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label style="display:block; color:var(--admin-text-muted); font-size:12px; margin-bottom:5px;">About Expert</label>
                <textarea name="about_text" id="f_about" rows="3" style="width:100%; background:rgba(255,255,255,0.03); border:1px solid var(--admin-border); border-radius:10px; padding:10px; color:white; font-family:inherit;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:25px;">
                <button type="button" onclick="closeModal()" style="background:transparent; border:1px solid var(--admin-border); color:white; padding:10px 20px; border-radius:10px; cursor:pointer;">Cancel</button>
                <button type="submit" style="background:var(--admin-primary); border:none; color:white; padding:10px 25px; border-radius:10px; font-weight:700; cursor:pointer;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('f_image_file');
const preview = document.getElementById('imgPreview');
const previewContainer = document.getElementById('previewContainer');

// Drag & Drop Handlers
dropzone.addEventListener('click', () => fileInput.click());

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
});

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handlePreview(fileInput.files[0]);
    }
});

fileInput.addEventListener('change', () => {
    if (fileInput.files.length) {
        handlePreview(fileInput.files[0]);
    }
});

function handlePreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        preview.src = e.target.result;
        previewContainer.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function openModal(mode, data = null) {
    const modal = document.getElementById('manageModal');
    const form = document.getElementById('caretakerForm');
    const title = document.getElementById('modalTitle');
    const action = document.getElementById('formAction');
    
    form.reset();
    action.value = mode;
    previewContainer.style.display = 'none';
    
    if (mode === 'add') {
        title.innerText = 'Add New Expert';
        document.getElementById('caretakerId').value = '';
        document.getElementById('f_image_url').value = '';
    } else {
        title.innerText = 'Edit Expert Profile';
        document.getElementById('caretakerId').value = data.id;
        document.getElementById('f_full_name').value = data.full_name;
        document.getElementById('f_category').value = data.category;
        document.getElementById('f_specialization').value = data.specialization;
        document.getElementById('f_experience').value = data.experience_years;
        document.getElementById('f_price').value = data.price_per_day;
        document.getElementById('f_patients').value = data.patients_helped;
        document.getElementById('f_image_url').value = data.image_url;
        document.getElementById('f_video_url').value = data.video_url || '';
        document.getElementById('f_about').value = data.about_text;
        
        if (data.image_url) {
            let img = data.image_url;
            if(!img.startsWith('http')) img = '../../' + img;
            preview.src = img;
            previewContainer.style.display = 'block';
        }
    }
    
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('manageModal').style.display = 'none';
}

document.getElementById('caretakerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/manage_caretaker.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    }
});

async function deleteCaretaker(id) {
    if (!confirm('Are you sure you want to delete this expert? This action cannot be undone.')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    try {
        const response = await fetch('api/manage_caretaker.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('row-' + id).remove();
            alert(result.message);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    }
}
</script>
</body>
</html>
