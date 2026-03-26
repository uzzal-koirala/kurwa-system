<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['caretaker_id'])) {
    header("Location: login.php");
    exit;
}

$caretaker_id = $_SESSION['caretaker_id'];
$caretaker_name = $_SESSION['caretaker_name'] ?? 'Caretaker';
$current_page = 'settings';

// Fetch caretaker details
$stmt = $conn->prepare("SELECT * FROM caretakers WHERE id = ?");
$stmt->bind_param("i", $caretaker_id);
$stmt->execute();
$caretaker = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all locations and hospitals
$locations_res = $conn->query("SELECT * FROM locations ORDER BY name ASC");
$hospitals_res = $conn->query("SELECT * FROM hospitals ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Caretaker</title>
    <link rel="stylesheet" href="../../assets/css/caretaker_sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #1b2559;
            --bg-color: #f4f7fe;
            --white: #ffffff;
            --text-main: #1b2559;
            --text-muted: #a3aed0;
            --success: #2ed573;
            --danger: #ff4757;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            --border-radius: 20px;
        }

        body.caretaker-body {
            background-color: var(--bg-color);
            font-family: 'Poppins', sans-serif;
            color: var(--text-main);
            margin: 0;
        }

        .main-content {
            margin-left: 320px;
            padding: 40px 50px;
            transition: all 0.3s ease;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-main-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--secondary);
            margin: 0;
            letter-spacing: -0.5px;
        }

        .settings-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            align-items: start;
        }

        /* Tabs Menu */
        .settings-sidebar {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 40px;
        }

        .tab-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tab-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .tab-item i {
            font-size: 20px;
            transition: 0.3s;
        }

        .tab-item:hover {
            background: var(--bg-color);
            color: var(--secondary);
        }

        .tab-item.active {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        /* Tab Content Area */
        .settings-content-area {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: var(--shadow);
            min-height: 500px;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease forwards;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-header h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
            font-weight: 800;
        }

        .section-header p {
            margin: 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Form Elements */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            border: 2px solid #edf2f7;
            background: #f8fafc;
            font-family: inherit;
            font-size: 14px;
            color: var(--secondary);
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Profile Photo section */
        .photo-upload-section {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 40px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px dashed #cbd5e1;
        }

        .current-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .photo-actions h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: 700;
        }

        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline {
            background: var(--white);
            color: var(--text-main);
            border: 1px solid #cbd5e1;
        }

        .btn-outline:hover {
            background: #f1f5f9;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
            background: #3553ce;
        }

        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }

        .form-actions {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .settings-container {
                grid-template-columns: 1fr;
            }
            .settings-sidebar {
                position: static;
                display: flex;
                overflow-x: auto;
                padding: 10px;
            }
            .tab-menu {
                display: flex;
                flex-direction: row;
                gap: 10px;
                width: 100%;
            }
            .tab-item { margin-bottom: 0; white-space: nowrap; }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .photo-upload-section {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body class="caretaker-body">

<?php include '../../includes/components/caretaker_sidebar.php'; ?>

<div class="main-content">
    <div class="dashboard-header">
        <div class="header-left" style="display: flex; align-items: center; gap: 15px;">
            <h1 class="page-main-title">Settings</h1>
        </div>
    </div>

    <div class="settings-container">
        <aside class="settings-sidebar">
            <ul class="tab-menu" id="tabMenu">
                <li class="tab-item active" data-tab="profile">
                    <i class="ri-user-settings-line"></i> Profile Info
                </li>
                <li class="tab-item" data-tab="security">
                    <i class="ri-lock-password-line"></i> Security
                </li>
            </ul>
        </aside>

        <main class="settings-content-area">
            <!-- Profile Tab -->
            <div class="tab-content active" id="profile-tab">
                <div class="section-header">
                    <h2>Profile Information</h2>
                    <p>Update your photo and personal details here.</p>
                </div>
                
                <form id="profileForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="photo-upload-section">
                        <img src="<?= htmlspecialchars($caretaker['image_url'] ?? 'https://ui-avatars.com/api/?name='.urlencode($caretaker['full_name'] ?? 'C').'&background=random') ?>" alt="Profile" class="current-photo" id="previewImage">
                        <div class="photo-actions">
                            <h3>Profile Photo</h3>
                            <div class="upload-btn-wrapper">
                                <button class="btn btn-outline" type="button"><i class="ri-upload-cloud-2-line"></i> Upload New</button>
                                <input type="file" name="profile_photo" id="photoInput" accept="image/*" onchange="previewFile()">
                            </div>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">JPG, GIF or PNG. Max size 2MB</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($caretaker['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($caretaker['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($caretaker['phone'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Price per Day (Rs.)</label>
                            <input type="number" name="price_per_day" class="form-control" value="<?= htmlspecialchars($caretaker['price_per_day'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Years of Experience</label>
                            <input type="number" name="experience_years" class="form-control" value="<?= htmlspecialchars($caretaker['experience_years'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control" required>
                                <option value="General Care" <?= ($caretaker['category'] ?? '') == 'General Care' ? 'selected' : '' ?>>General Care</option>
                                <option value="Post-Surgery" <?= ($caretaker['category'] ?? '') == 'Post-Surgery' ? 'selected' : '' ?>>Post-Surgery</option>
                                <option value="Elderly Care" <?= ($caretaker['category'] ?? '') == 'Elderly Care' ? 'selected' : '' ?>>Elderly Care</option>
                                <option value="Night Support" <?= ($caretaker['category'] ?? '') == 'Night Support' ? 'selected' : '' ?>>Night Support</option>
                                <option value="Special Needs" <?= ($caretaker['category'] ?? '') == 'Special Needs' ? 'selected' : '' ?>>Special Needs</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Service Location</label>
                            <select name="location_id" class="form-control" required>
                                <option value="">Select Location</option>
                                <?php while($loc = $locations_res->fetch_assoc()): ?>
                                    <option value="<?= $loc['id'] ?>" <?= ($caretaker['location_id'] ?? 0) == $loc['id'] ? 'selected' : '' ?>><?= $loc['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Service Hospital</label>
                            <select name="hospital_id" class="form-control" required>
                                <option value="">Select Hospital</option>
                                <?php 
                                $hospitals_res->data_seek(0);
                                while($hosp = $hospitals_res->fetch_assoc()): ?>
                                    <option value="<?= $hosp['id'] ?>" <?= ($caretaker['hospital_id'] ?? 0) == $hosp['id'] ? 'selected' : '' ?>><?= $hosp['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Specialization Summary</label>
                            <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($caretaker['specialization'] ?? '') ?>" placeholder="e.g. Wound Care, Mobility Assistance" required>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">About Me</label>
                            <textarea name="about_text" class="form-control" placeholder="Tell patients about your experience and care approach..."><?= htmlspecialchars($caretaker['about_text'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                            <i class="ri-save-3-line"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Tab -->
            <div class="tab-content" id="security-tab">
                <div class="section-header">
                    <h2>Password & Security</h2>
                    <p>Update your password to keep your account secure.</p>
                </div>
                
                <form id="passwordForm">
                    <input type="hidden" name="action" value="update_password">
                    
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="savePasswordBtn">
                            <i class="ri-lock-unlock-line"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    // Tab Switching
    const tabs = document.querySelectorAll('.tab-item');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            tab.classList.add('active');
            document.getElementById(`${tab.dataset.tab}-tab`).classList.add('active');
        });
    });

    // Image Preview
    function previewFile() {
        const preview = document.getElementById('previewImage');
        const file = document.getElementById('photoInput').files[0];
        const reader = new FileReader();

        reader.onloadend = function () {
            preview.src = reader.result;
        }

        if (file) {
            reader.readAsDataURL(file);
        }
    }

    // Forms Submission
    document.getElementById('profileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('saveProfileBtn');
        const icon = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Saving...';
        btn.disabled = true;

        const fd = new FormData(e.target);
        
        try {
            const res = await fetch('handlers/settings_handler.php', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Profile updated successfully!',
                    confirmButtonColor: '#4361ee'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Something went wrong',
                    confirmButtonColor: '#4361ee'
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({icon: 'error', title: 'Error', text: 'Network error occurred.'});
        } finally {
            btn.innerHTML = icon;
            btn.disabled = false;
        }
    });

    document.getElementById('passwordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('savePasswordBtn');
        const icon = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Updating...';
        btn.disabled = true;

        const fd = new FormData(e.target);
        
        try {
            const res = await fetch('handlers/settings_handler.php', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Updated!',
                    text: 'Your password was changed successfully.',
                    confirmButtonColor: '#4361ee'
                });
                e.target.reset();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: data.message || 'Could not change password.',
                    confirmButtonColor: '#4361ee'
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({icon: 'error', title: 'Error', text: 'Network error occurred.'});
        } finally {
            btn.innerHTML = icon;
            btn.disabled = false;
        }
    });
</script>
</body>
</html>
