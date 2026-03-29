<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "pharmacies";
$action = isset($_GET['id']) ? 'edit' : 'add';
$pharmacy_id = $_GET['id'] ?? null;
$pharmacy = null;

if ($action === 'edit' && $pharmacy_id) {
    $id = (int)$pharmacy_id;
    $res = $conn->query("SELECT * FROM pharmacies WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $pharmacy = $res->fetch_assoc();
    } else {
        header("Location: pharmacies.php?error=not_found");
        exit;
    }
}

$all_locations = $conn->query("SELECT id, name FROM locations ORDER BY name ASC");
$current_hospitals = [];
if ($action === 'edit' && !empty($pharmacy['location_id'])) {
    $loc_id = (int)$pharmacy['location_id'];
    $h_res = $conn->query("SELECT id, name FROM hospitals WHERE location_id = $loc_id ORDER BY name ASC");
    while ($h = $h_res->fetch_assoc()) {
        $current_hospitals[] = $h;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($action) ?> Pharmacy | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --input-bg: rgba(255, 255, 255, 0.03);
            --input-border: rgba(255, 255, 255, 0.08);
            --brand-primary: #8b5cf6;
            --brand-accent: #6d28d9;
        }

        .profile-container { max-width: 1000px; margin: 0 auto; padding-bottom: 50px; }
        .profile-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .back-btn { background: rgba(255, 255, 255, 0.05); border: 1px solid var(--admin-border); color: white; padding: 10px 20px; border-radius: 12px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600; transition: 0.3s; }
        .back-btn:hover { background: rgba(255, 255, 255, 0.1); transform: translateX(-5px); }

        .profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .form-section { background: var(--admin-card-bg); border: 1px solid var(--admin-border); border-radius: 24px; padding: 25px; margin-bottom: 25px; backdrop-filter: blur(10px); }
        .full-width { grid-column: 1 / -1; }
        .section-title { font-size: 16px; font-weight: 700; color: var(--brand-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .section-title i { background: rgba(139, 92, 246, 0.1); width: 32px; height: 32px; border-radius: 8px; display: grid; place-items: center; font-size: 18px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 12px; color: var(--admin-text-muted); margin-bottom: 8px; font-weight: 500; letter-spacing: 0.5px; }
        .form-control { width: 100%; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 12px; padding: 12px 18px; color: white; font-family: 'Poppins', sans-serif; font-size: 14px; outline: none; transition: 0.3s; }
        .form-control:focus { border-color: var(--brand-primary); background: rgba(255, 255, 255, 0.05); box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }

        .upload-zone { width: 100%; height: 200px; border: 2px dashed var(--admin-border); border-radius: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; background: rgba(255, 255, 255, 0.01); position: relative; overflow: hidden; }
        .upload-zone:hover { border-color: var(--brand-primary); background: rgba(139, 92, 246, 0.03); }
        .upload-zone i { font-size: 40px; color: var(--admin-text-muted); margin-bottom: 10px; }
        .preview-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: none; border-radius: 18px; }

        .save-bar { position: sticky; bottom: 20px; background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.1); padding: 15px 30px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; z-index: 100; margin-top: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
        .submit-btn { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; border: none; padding: 12px 40px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 10px; }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3); }

        textarea.form-control { resize: none; min-height: 120px; }
        select.form-control option { background: #0f172a; color: white; padding: 10px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        /* Success Modal Styles */
        .success-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(20px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .success-card {
            background: var(--admin-card-bg);
            border: 1px solid var(--admin-border);
            padding: 50px;
            border-radius: 40px;
            text-align: center;
            max-width: 450px;
            width: 90%;
            transform: scale(0.9);
            animation: cardScale 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes cardScale { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .success-icon {
            width: 100px;
            height: 100px;
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            margin: 0 auto 30px;
            border: 2px solid rgba(139, 92, 246, 0.2);
            position: relative;
        }

        .success-icon::after {
            content: '';
            position: absolute;
            inset: -10px;
            border: 2px solid rgba(139, 92, 246, 0.1);
            border-radius: 50%;
            animation: pulse-purple 2s infinite;
        }

        @keyframes pulse-purple { 
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        .success-btn {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 30px;
            width: 100%;
            font-size: 16px;
            transition: 0.3s;
        }

        .success-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(139, 92, 246, 0.3);
        }
    </style>
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="profile-container">
        <div class="profile-header">
            <div>
                <a href="pharmacies.php" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Database</a>
                <h1 style="margin-top: 20px; font-size: 32px; font-weight: 800;"><?= $action === 'edit' ? 'Pharmacy details' : 'Add New Pharmacy' ?></h1>
                <p style="color: var(--admin-text-muted); font-size: 14px;">Define medicine availability, operating hours, and retail mapping profiles.</p>
            </div>
        </div>

        <form id="profileForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $action ?>">
            <input type="hidden" name="id" value="<?= $pharmacy_id ?>">
            <input type="hidden" name="image_url" id="existing_image_url" value="<?= $pharmacy['image_url'] ?? '' ?>">

            <div class="profile-grid">
                <!-- Section 1: Basic Information -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-capsule-line"></i> Retail Information</h3>
                    
                    <div class="form-group">
                        <label>Pharmacy Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($pharmacy['name'] ?? '') ?>" placeholder="e.g. Life Care Pharmacy">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Hotline Number</label>
                            <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($pharmacy['phone'] ?? '') ?>" placeholder="e.g. 01XXXXXXX">
                        </div>
                        <div class="form-group">
                            <label>Delivery Efficiency (Label)</label>
                            <input type="text" name="delivery_time" class="form-control" value="<?= htmlspecialchars($pharmacy['delivery_time'] ?? '30-45 min') ?>" placeholder="e.g. 20-30 min">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Availability & Mapping -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-map-pin-line"></i> Mapping & Assignment</h3>
                    
                    <div class="form-group">
                        <label>Physical Address</label>
                        <input type="text" name="address" class="form-control" required value="<?= htmlspecialchars($pharmacy['address'] ?? '') ?>" placeholder="e.g. Opposite Bir Hospital Gate">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Service Area Location</label>
                            <select name="location_id" id="locationSelect" class="form-control" onchange="loadHospitals(this.value)" required>
                                <option value="">Select Area</option>
                                <?php while($l = $all_locations->fetch_assoc()): ?>
                                    <option value="<?= $l['id'] ?>" <?= (isset($pharmacy['location_id']) && $pharmacy['location_id'] == $l['id']) ? 'selected' : '' ?>><?= htmlspecialchars($l['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Hospital Assignment</label>
                            <select name="hospital_id" id="hospitalSelect" class="form-control">
                                <option value="0">General/External</option>
                                <?php foreach($current_hospitals as $h): ?>
                                    <option value="<?= $h['id'] ?>" <?= (isset($pharmacy['hospital_id']) && $pharmacy['hospital_id'] == $h['id']) ? 'selected' : '' ?>><?= htmlspecialchars($h['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Operating Hours -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-time-line"></i> Operating Hours</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Opening Time</label>
                            <input type="time" name="opening_time" class="form-control" required value="<?= $pharmacy['opening_time'] ?? '08:00' ?>">
                        </div>
                        <div class="form-group">
                            <label>Closing Time</label>
                            <input type="time" name="closing_time" class="form-control" required value="<?= $pharmacy['closing_time'] ?? '20:00' ?>">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Operational Status</label>
                        <select name="status" class="form-control" required>
                            <option value="open" <?= (isset($pharmacy['status']) && $pharmacy['status'] === 'open') ? 'selected' : '' ?>>Open / Active</option>
                            <option value="closed" <?= (isset($pharmacy['status']) && $pharmacy['status'] === 'closed') ? 'selected' : '' ?>>Closed / Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Section 4: Multimedia -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-video-line"></i> Store Branding</h3>
                    
                    <div class="form-group">
                        <label>Introduction Video (YouTube URL)</label>
                        <input type="text" name="video_url" class="form-control" value="<?= htmlspecialchars($pharmacy['video_url'] ?? '') ?>" placeholder="e.g. https://youtube.com/...">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Initial Customer Review Rating (0-5)</label>
                        <input type="number" step="0.1" name="rating" class="form-control" value="<?= $pharmacy['rating'] ?? 5.0 ?>" max="5" min="0">
                    </div>
                </div>

                <!-- Section 5: Biography -->
                <div class="form-section full-width">
                    <h3 class="section-title"><i class="ri-article-line"></i> Pharmacy Credentials & description </h3>
                    <div class="form-group">
                        <textarea name="description" class="form-control" style="min-height: 200px;" placeholder="Describe medicine availability, specialization, and facility features..."><?= htmlspecialchars($pharmacy['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Store Photograph</label>
                        <div class="upload-zone" id="uploadZone">
                            <i class="ri-upload-cloud-2-line"></i>
                            <span>Drag and drop store photo or click</span>
                            <input type="file" name="image_file" id="fileInput" accept="image/*" style="display:none;">
                            <img id="previewImg" class="preview-img" src="<?= (isset($pharmacy['image_url']) && !empty($pharmacy['image_url'])) ? '../../' . $pharmacy['image_url'] : '' ?>" style="<?= (isset($pharmacy['image_url']) && !empty($pharmacy['image_url'])) ? 'display:block;' : '' ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Action Bar -->
            <div class="save-bar">
                <div style="display:flex; align-items:center; gap:15px;">
                    <i class="ri-medicine-bottle-line" style="color:var(--brand-primary); font-size:24px;"></i>
                    <div>
                        <div style="font-weight:700; font-size:14px; color:white;">Global Resource</div>
                        <div style="font-size:11px; color:var(--admin-text-muted);">Secure medicine database synchronization.</div>
                    </div>
                </div>
                <div style="display:flex; gap:15px;">
                    <a href="pharmacies.php" style="color:var(--admin-text-muted); text-decoration:none; font-weight:600; padding:12px 20px;">Cancel</a>
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="ri-check-double-line"></i> 
                        <?= $action === 'edit' ? 'Update detail' : 'Commit Pharmacy' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Beautiful Success Modal -->
<div id="successModal" class="success-overlay">
    <div class="success-card">
        <div class="success-icon">
            <i class="ri-check-line"></i>
        </div>
        <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 10px; color: white;">Pharmacy Saved!</h2>
        <p style="color: var(--admin-text-muted); line-height: 1.6;">The retail pharmacy profile and medicine allocation have been synchronized successfully.</p>
        <button onclick="window.location.href='pharmacies.php'" class="success-btn">Return to Database</button>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    const zone = document.getElementById('uploadZone');
    const input = document.getElementById('fileInput');
    const preview = document.getElementById('previewImg');

    zone.onclick = () => input.click();
    input.onchange = () => { if(input.files.length) handlePreview(input.files[0]); };

    function handlePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    async function loadHospitals(locationId) {
        const hospitalSelect = document.getElementById('hospitalSelect');
        hospitalSelect.innerHTML = '<option value="">Loading...</option>';
        if (!locationId) { hospitalSelect.innerHTML = '<option value="0">General/External</option>'; return; }
        try {
            const res = await fetch(`api/get_hospitals_by_location.php?location_id=${locationId}`);
            const hospitals = await res.json();
            hospitalSelect.innerHTML = '<option value="0">General/External</option>';
            hospitals.forEach(h => {
                const opt = document.createElement('option');
                opt.value = h.id; opt.textContent = h.name;
                hospitalSelect.appendChild(opt);
            });
        } catch (err) { console.error(err); }
    }

    document.getElementById('profileForm').onsubmit = async (e) => {
        e.preventDefault();
        const subBtn = document.getElementById('submitBtn');
        subBtn.disabled = true;
        subBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Saving...';

        const formData = new FormData(e.target);
        try {
            const res = await fetch('api/manage_pharmacies.php', { method: 'POST', body: formData });
            const data = await res.json();
            if(data.success) {
                document.getElementById('successModal').style.display = 'flex';
                setTimeout(() => { window.location.href = 'pharmacies.php'; }, 3000);
            } else {
                alert('Error: ' + data.message);
                subBtn.disabled = false;
                subBtn.innerHTML = '<i class="ri-check-double-line"></i> Commit Pharmacy';
            }
        } catch(err) {
            console.error(err);
            alert('An unexpected error occurred.');
            subBtn.disabled = false;
        }
    };
</script>
</body>
</html>
