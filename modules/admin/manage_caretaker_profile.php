<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "caretakers";
$action = isset($_GET['id']) ? 'edit' : 'add';
$caretaker_id = $_GET['id'] ?? null;
$caretaker = null;

if ($action === 'edit' && $caretaker_id) {
    $id = (int)$caretaker_id;
    $res = $conn->query("SELECT * FROM caretakers WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $caretaker = $res->fetch_assoc();
    } else {
        header("Location: caretakers.php?error=not_found");
        exit;
    }
}

// Categories for dropdown
$categories = $conn->query("SELECT name FROM caretaker_categories ORDER BY name ASC");
$all_locations = $conn->query("SELECT id, name FROM locations ORDER BY name ASC");

// Fetch hospitals for current location if in edit mode
$current_hospitals = [];
if ($action === 'edit' && !empty($caretaker['location_id'])) {
    $loc_id = (int)$caretaker['location_id'];
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
    <title><?= ucfirst($action) ?> Expert | Kurwa Admin</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --input-bg: rgba(255, 255, 255, 0.03);
            --input-border: rgba(255, 255, 255, 0.08);
            --brand-primary: #3b82f6;
            --brand-accent: #6366f1;
        }

        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            padding-bottom: 50px;
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--admin-border);
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-5px);
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-section {
            background: var(--admin-card-bg);
            border: 1px solid var(--admin-border);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
        }

        .full-width { grid-column: 1 / -1; }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--admin-primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            background: rgba(59, 130, 246, 0.1);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-size: 18px;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 12px;
            color: var(--admin-text-muted);
            margin-bottom: 8px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: 12px 18px;
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .upload-zone {
            width: 100%;
            height: 200px;
            border: 2px dashed var(--admin-border);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s;
            background: rgba(255, 255, 255, 0.01);
            position: relative;
            overflow: hidden;
        }

        .upload-zone:hover {
            border-color: var(--admin-primary);
            background: rgba(59, 130, 246, 0.03);
        }

        .upload-zone i { font-size: 40px; color: var(--admin-text-muted); margin-bottom: 10px; }
        .upload-zone span { font-size: 13px; color: var(--admin-text-muted); }

        .preview-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
            border-radius: 18px;
        }

        .save-bar {
            position: sticky;
            bottom: 20px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px 30px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            margin-top: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .submit-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        select.form-control option { background: #0f172a; color: white; padding: 10px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        textarea.form-control { resize: none; min-height: 260px; }

        /* Success Modal Styles */
        .success-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(15px);
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
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes cardScale { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .success-icon {
            width: 100px;
            height: 100px;
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            margin: 0 auto 30px;
            border: 2px solid rgba(16, 185, 129, 0.2);
            position: relative;
        }

        .success-icon::after {
            content: '';
            position: absolute;
            inset: -10px;
            border: 2px solid rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse { 
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        .success-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content">
    <div class="profile-container">
        <div class="profile-header">
            <div>
                <a href="caretakers.php" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Database</a>
                <h1 style="margin-top: 20px; font-size: 32px; font-weight: 800;"><?= $action === 'edit' ? 'Edit Profile' : 'Add New Caretaker' ?></h1>
                <p style="color: var(--admin-text-muted); font-size: 14px;">Complete the detailed form below to register or update an expert.</p>
            </div>
        </div>

        <form id="profileForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $action ?>">
            <input type="hidden" name="id" value="<?= $caretaker_id ?>">
            <input type="hidden" name="image_url" id="existing_image_url" value="<?= $caretaker['image_url'] ?? '' ?>">

            <div class="profile-grid">
                <!-- Section 1: Basic Information -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-user-smile-line"></i> Personal Information</h3>
                    
                    <div class="form-group">
                        <label>Full Legal Name</label>
                        <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($caretaker['full_name'] ?? '') ?>" placeholder="e.g. Dr. Sujal Bardewa">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" required value="<?= htmlspecialchars($caretaker['phone_number'] ?? '') ?>" placeholder="e.g. 9841XXXXXX">
                        </div>
                        <div class="form-group">
                            <label>Primary Location / Hospital</label>
                            <input type="text" name="hospital_name" class="form-control" required value="<?= htmlspecialchars($caretaker['hospital_name'] ?? '') ?>" placeholder="e.g. Bir Hospital">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Expertise -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-medal-line"></i> Technical Expertise</h3>
                    
                    <div class="form-group">
                        <label>Service Category</label>
                        <select name="category" class="form-control" required style="background:rgba(99, 102, 241, 0.12); color:#818cf8; border-color:rgba(99, 102, 241, 0.3); font-weight:600;">
                            <option value="">Select Category</option>
                            <?php while($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= $cat['name'] ?>" <?= (isset($caretaker['category']) && $caretaker['category'] === $cat['name']) ? 'selected' : '' ?>>
                                    <?= $cat['name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Skills & Expertise</label>
                            <input type="text" name="specialization" class="form-control" required value="<?= htmlspecialchars($caretaker['specialization'] ?? '') ?>" placeholder="e.g. ICU, Critical Care, Dialysis">
                        </div>
                        <div class="form-group">
                            <label>Experience (Years)</label>
                            <input type="number" name="experience_years" class="form-control" value="<?= $caretaker['experience_years'] ?? 0 ?>">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Performance & Pricing -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-money-dollar-circle-line"></i> Rates & Stats</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Rate Per Day (Rs.)</label>
                            <input type="number" name="price_per_day" class="form-control" required value="<?= $caretaker['price_per_day'] ?? 0 ?>">
                        </div>
                        <div class="form-group">
                            <label>Patients Helped</label>
                            <input type="number" name="patients_helped" class="form-control" value="<?= $caretaker['patients_helped'] ?? 0 ?>">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Initial Rating Score (0-5)</label>
                        <input type="number" step="0.1" max="5" min="0" name="rating" class="form-control" value="<?= $caretaker['rating'] ?? 4.5 ?>">
                    </div>
                </div>

                <!-- Section 4: Account Credentials -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-lock-password-line"></i> Account Credentials</h3>
                    
                    <div class="form-group">
                        <label>Email Address (used for Login)</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($caretaker['email'] ?? '') ?>" placeholder="e.g. expert@kurwa.com">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Login Password <?= $action === 'edit' ? '<span style="color:#ef4444; font-size:10px;">(Leave blank to keep current)</span>' : '' ?></label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="p_pass" class="form-control" placeholder="••••••••" <?= $action === 'add' ? 'required' : '' ?>>
                            <button type="button" onclick="togglePass()" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--admin-text-muted); cursor:pointer;">
                                <i id="p_eye" class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Multimedia -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-image-add-line"></i> Multimedia Assets</h3>
                    
                    <div class="form-group">
                        <label>Intro Video URL (YouTube/Vimeo)</label>
                        <input type="text" name="video_url" class="form-control" value="<?= htmlspecialchars($caretaker['video_url'] ?? '') ?>" placeholder="e.g. https://youtube.com/watch?v=...">
                    </div>

                    <div class="form-group">
                        <label>Profile Photograph</label>
                        <div class="upload-zone" id="uploadZone">
                            <i class="ri-upload-cloud-2-line"></i>
                            <span>Drag and drop profile photo or click</span>
                            <input type="file" name="image_file" id="fileInput" accept="image/*" style="display:none;">
                            <img id="previewImg" class="preview-img" src="<?= (isset($caretaker['image_url']) && !empty($caretaker['image_url'])) ? '../../' . $caretaker['image_url'] : '' ?>" style="<?= (isset($caretaker['image_url']) && !empty($caretaker['image_url'])) ? 'display:block;' : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title"><i class="ri-map-pin-user-line"></i> Work Assignment</h3>
                    <div class="form-row" style="grid-template-columns: 1fr; gap: 15px;">
                        <div class="form-row" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group" style="margin-bottom:0px;">
                                <label><i class="ri-map-pin-2-line"></i> Service Location</label>
                                <select name="location_id" id="locationSelect" class="form-control" onchange="loadHospitals(this.value)" style="background:rgba(99, 102, 241, 0.05); color:#818cf8; font-weight:600; border:1px solid rgba(99, 102, 241, 0.2);">
                                    <option value="">Select Location</option>
                                    <?php while($l = $all_locations->fetch_assoc()): ?>
                                        <option value="<?= $l['id'] ?>" <?= (isset($caretaker['location_id']) && $caretaker['location_id'] == $l['id']) ? 'selected' : '' ?>><?= htmlspecialchars($l['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:0px;">
                                <label><i class="ri-hospital-line"></i> Assigned Hospital</label>
                                <select name="hospital_id" id="hospitalSelect" class="form-control" style="background:rgba(99, 102, 241, 0.05); color:#818cf8; font-weight:600; border:1px solid rgba(99, 102, 241, 0.2);">
                                    <option value="">Select Hospital</option>
                                    <?php foreach($current_hospitals as $h): ?>
                                        <option value="<?= $h['id'] ?>" <?= (isset($caretaker['hospital_id']) && $caretaker['hospital_id'] == $h['id']) ? 'selected' : '' ?>><?= htmlspecialchars($h['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                            <div class="form-group" style="margin-bottom:0px;">
                                <label><i class="ri-time-line"></i> Opening Time</label>
                                <input type="time" name="opening_time" class="form-control" value="<?= $caretaker['opening_time'] ?? '09:00:00' ?>" style="background:rgba(16, 185, 129, 0.05); color:#10b981; border:1px solid rgba(16, 185, 129, 0.2);">
                            </div>
                            <div class="form-group" style="margin-bottom:0px;">
                                <label><i class="ri-time-line"></i> Closing Time</label>
                                <input type="time" name="closing_time" class="form-control" value="<?= $caretaker['closing_time'] ?? '21:00:00' ?>" style="background:rgba(239, 68, 68, 0.05); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.2);">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 6: Biography -->
                <div class="form-section">
                    <h3 class="section-title"><i class="ri-article-line"></i> Professional Biography</h3>
                    <div class="form-group" style="margin-bottom: 0;">
                        <textarea name="about_text" class="form-control" placeholder="Describe the expert's background, education, and professional philosophy..."><?= htmlspecialchars($caretaker['about_text'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Global Action Bar -->
            <div class="save-bar">
                <div style="display:flex; align-items:center; gap:15px;">
                    <i class="ri-shield-flash-line" style="color:var(--admin-primary); font-size:24px;"></i>
                    <div>
                        <div style="font-weight:700; font-size:14px; color:white;">System Ready</div>
                        <div style="font-size:11px; color:var(--admin-text-muted);">Secure data transmission enabled.</div>
                    </div>
                </div>
                <div style="display:flex; gap:15px;">
                    <a href="caretakers.php" style="color:var(--admin-text-muted); text-decoration:none; font-weight:600; padding:12px 20px;">Cancel</a>
                    <button type="submit" class="submit-btn">
                        <i class="ri-check-double-line"></i> 
                        <?= $action === 'edit' ? 'Update Profile' : 'Register Expert' ?>
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
        <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 10px; color: white;">Changes Saved!</h2>
        <p style="color: var(--admin-text-muted); line-height: 1.6;">The expert profile has been updated successfully. Database synchronized.</p>
        <button onclick="window.location.href='caretakers.php'" class="success-btn">Continue to Database</button>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    const zone = document.getElementById('uploadZone');
    const input = document.getElementById('fileInput');
    const preview = document.getElementById('previewImg');

    zone.onclick = () => input.click();

    zone.ondragover = (e) => { e.preventDefault(); zone.style.borderColor = 'var(--admin-primary)'; };
    zone.ondragleave = () => { zone.style.borderColor = 'var(--admin-border)'; };
    zone.ondrop = (e) => {
        e.preventDefault();
        zone.style.borderColor = 'var(--admin-border)';
        if(e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            handlePreview(input.files[0]);
        }
    };

    input.onchange = () => { if(input.files.length) handlePreview(input.files[0]); };

    function handlePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    function togglePass() {
        const p = document.getElementById('p_pass');
        const e = document.getElementById('p_eye');
        if (p.type === 'password') {
            p.type = 'text';
            e.classList.replace('ri-eye-line', 'ri-eye-off-line');
        } else {
            p.type = 'password';
            e.classList.replace('ri-eye-off-line', 'ri-eye-line');
        }
    }

    async function loadHospitals(locationId) {
        const hospitalSelect = document.getElementById('hospitalSelect');
        hospitalSelect.innerHTML = '<option value="">Loading...</option>';
        
        if (!locationId) {
            hospitalSelect.innerHTML = '<option value="">Select Hospital</option>';
            return;
        }

        try {
            const res = await fetch(`api/get_hospitals_by_location.php?location_id=${locationId}`);
            const hospitals = await res.json();
            
            hospitalSelect.innerHTML = '<option value="">Select Hospital</option>';
            hospitals.forEach(h => {
                const opt = document.createElement('option');
                opt.value = h.id;
                opt.textContent = h.name;
                hospitalSelect.appendChild(opt);
            });
        } catch (err) {
            console.error(err);
            hospitalSelect.innerHTML = '<option value="">Error loading hospitals</option>';
        }
    }

    document.getElementById('profileForm').onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const res = await fetch('api/manage_caretaker.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if(data.success) {
                document.getElementById('successModal').style.display = 'flex';
                // Auto-redirect after 3 seconds anyway
                setTimeout(() => {
                    window.location.href = 'caretakers.php';
                }, 3000);
            } else {
                alert('Error: ' + data.message);
            }
        } catch(err) {
            console.error(err);
            alert('An unexpected error occurred.');
        }
    };
</script>
</body>
</html>
