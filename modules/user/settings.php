<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$current_page = 'settings';
$user_id = $_SESSION['user_id'];

// Fetch latest user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Handle form submission (Mock logic for UI demo, backend hookable later)
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success_msg = 'Settings updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | Kurwa System</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --danger: #ef4444;
            --success: #10b981;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0; padding: 0;
            overflow-x: hidden;
        }

        .main-content {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 16px;
        }

        .settings-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 40px;
            align-items: start;
        }

        /* Settings Sidebar */
        .settings-sidebar {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 20px 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid var(--border-light);
            position: sticky;
            top: 40px;
        }

        .settings-tab {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            width: 100%;
            border: none;
            background: transparent;
            text-align: left;
            font-size: 16px;
            font-family: inherit;
            font-weight: 600;
            color: var(--text-muted);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .settings-tab i {
            font-size: 22px;
            transition: 0.3s;
        }

        .settings-tab:hover {
            color: var(--primary);
            background: #f1f5f9;
        }

        .settings-tab.active {
            color: var(--primary);
            background: #eff6ff;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.1);
        }

        .settings-tab.active i {
            transform: scale(1.1);
        }

        /* Settings Content */
        .settings-pane {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid var(--border-light);
            display: none;
            animation: fadeIn 0.4s ease-out forwards;
        }

        .settings-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pane-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-light);
        }

        .pane-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .pane-header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            color: var(--text-main);
            background: #fdfdfd;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: #fff;
        }

        /* Toggle Switches */
        .toggle-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: #f8fafc;
            border-radius: 16px;
            margin-bottom: 16px;
            border: 1px solid #f1f5f9;
            transition: 0.3s;
        }

        .toggle-group:hover {
            border-color: var(--border-light);
        }

        .toggle-info h4 { margin: 0 0 4px 0; font-size: 16px; font-weight: 600; }
        .toggle-info p { margin: 0; font-size: 13px; color: var(--text-muted); }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 28px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 20px; width: 20px;
            left: 4px; bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        input:checked + .slider { background-color: var(--success); }
        input:checked + .slider:before { transform: translateX(22px); }

        /* Avatar Upload */
        .avatar-upload {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--border-light);
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            object-fit: cover;
        }

        .avatar-btns {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-family: inherit;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: white;
            border: none;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(59, 130, 246, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-light);
        }
        .btn-outline:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-danger {
            background: #fef2f2;
            color: var(--danger);
            border: 1px solid #fca5a5;
        }
        .btn-danger:hover {
            background: #fee2e2;
        }

        /* Success Alert */
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            border: 1px solid #a7f3d0;
            animation: slideInDown 0.4s ease-out;
        }

        @keyframes slideInDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .settings-layout {
                grid-template-columns: 220px 1fr;
                gap: 30px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 30px 20px;
            }
            .settings-layout {
                grid-template-columns: 1fr;
            }
            .settings-sidebar {
                position: static;
                display: flex;
                overflow-x: auto;
                padding: 10px;
                white-space: nowrap;
                border-radius: 16px;
            }
            .settings-tab {
                width: auto;
                padding: 12px 16px;
                justify-content: center;
            }
            .settings-pane {
                padding: 25px;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .avatar-upload {
                flex-direction: column;
                text-align: center;
            }
            .page-header h1 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1>Account Settings</h1>
        <p>Manage your profile, security, and preferences seamlessly.</p>
    </div>

    <?php if ($success_msg): ?>
    <div class="alert-success">
        <i class="ri-checkbox-circle-fill" style="font-size: 20px;"></i>
        <?= htmlspecialchars($success_msg) ?>
    </div>
    <?php endif; ?>

    <div class="settings-layout">
        <!-- Sidebar Navigation -->
        <div class="settings-sidebar">
            <button class="settings-tab active" onclick="switchTab('profile', this)">
                <i class="ri-user-smile-line"></i> Profile
            </button>
            <button class="settings-tab" onclick="switchTab('security', this)">
                <i class="ri-shield-keyhole-line"></i> Security
            </button>
            <button class="settings-tab" onclick="switchTab('notifications', this)">
                <i class="ri-notification-3-line"></i> Notifications
            </button>
            <button class="settings-tab" onclick="switchTab('appearance', this)">
                <i class="ri-palette-line"></i> Appearance
            </button>
        </div>

        <!-- Settings Content Area -->
        <div class="settings-content">
            
            <!-- Profile Tab -->
            <div id="profile" class="settings-pane active">
                <div class="pane-header">
                    <h2>Public Profile</h2>
                    <p>Update your personal information and how others see you on the platform.</p>
                </div>

                <form method="POST">
                    <div class="avatar-upload">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_data['full_name'] ?? 'User') ?>&background=3b82f6&color=fff&size=150" alt="Avatar" class="avatar-preview">
                        <div>
                            <div class="avatar-btns">
                                <button type="button" class="btn btn-primary"><i class="ri-upload-cloud-line"></i> Upload New</button>
                                <button type="button" class="btn btn-outline">Remove</button>
                            </div>
                            <p style="color: var(--text-muted); font-size: 13px; margin-top: 10px;">JPG, GIF or PNG. Max size of 2MB.</p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user_data['full_name'] ?? '') ?>" placeholder="e.g. Ujwal Koirala">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" placeholder="e.g. ujwal@example.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>" placeholder="+977 98XXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Location Base</label>
                            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($user_data['address'] ?? '') ?>" placeholder="City, Country">
                        </div>
                    </div>

                    <div class="form-group" style="text-align: right; margin-top: 10px;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Security Tab -->
            <div id="security" class="settings-pane">
                <div class="pane-header">
                    <h2>Security Settings</h2>
                    <p>Ensure your account remains highly secure with strong authentication.</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" placeholder="Enter your current password">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" placeholder="Create a new strong password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" placeholder="Repeat the new password">
                        </div>
                    </div>
                    
                    <div class="form-group" style="text-align: right;">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>

                <div class="pane-header" style="margin-top: 40px;">
                    <h2>Two-Factor Authentication (2FA)</h2>
                    <p>Add an extra layer of security to your Kurwa account.</p>
                </div>

                <div class="toggle-group">
                    <div class="toggle-info">
                        <h4>Enable 2FA via Authenticator App</h4>
                        <p>Requires an app like Google Authenticator or Authy to log in.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div id="notifications" class="settings-pane">
                <div class="pane-header">
                    <h2>Notification Preferences</h2>
                    <p>Control what emails and alerts you receive from us.</p>
                </div>

                <div class="toggle-group">
                    <div class="toggle-info">
                        <h4>Order Updates</h4>
                        <p>Get real-time alerts when your food or medicine orders change status.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="toggle-group">
                    <div class="toggle-info">
                        <h4>Promotions and Offers</h4>
                        <p>Receive exclusive coupons and discount deals via email.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="toggle-group">
                    <div class="toggle-info">
                        <h4>System Alerts</h4>
                        <p>Important account security and maintenance updates.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked disabled>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="form-group" style="text-align: right; margin-top: 24px;">
                    <button type="button" class="btn btn-primary" onclick="alert('Preferences saved!')">Save Preferences</button>
                </div>
            </div>

            <!-- Appearance Tab -->
            <div id="appearance" class="settings-pane">
                <div class="pane-header">
                    <h2>Appearance & User Interface</h2>
                    <p>Customize the look and feel of your dashboard.</p>
                </div>

                <div class="form-row">
                    <!-- Light Mode Card -->
                    <div style="border: 2px solid var(--primary); border-radius: 16px; padding: 20px; text-align: center; cursor: pointer;">
                        <img src="https://images.unsplash.com/photo-1544256718-3b623d33ee32?w=300&q=80" alt="Light Mode" style="width: 100%; border-radius: 12px; margin-bottom: 15px; height: 120px; object-fit: cover;">
                        <h4 style="margin: 0 0 5px 0;">Light Mode</h4>
                        <p style="margin: 0; font-size: 13px; color: var(--text-muted);">Clean and bright</p>
                        <input type="radio" name="theme" checked style="margin-top: 15px;">
                    </div>
                    
                    <!-- Dark Mode Card -->
                    <div style="border: 1px solid var(--border-light); border-radius: 16px; padding: 20px; text-align: center; cursor: pointer; background: #0f172a; color: white;">
                        <img src="https://images.unsplash.com/photo-1506316279172-1c251cc801ed?w=300&q=80" alt="Dark Mode" style="width: 100%; border-radius: 12px; margin-bottom: 15px; height: 120px; object-fit: cover; opacity: 0.8;">
                        <h4 style="margin: 0 0 5px 0; color: white;">Dark Mode</h4>
                        <p style="margin: 0; font-size: 13px; color: #cbd5e1;">Easy on the eyes</p>
                        <input type="radio" name="theme" style="margin-top: 15px;">
                    </div>
                </div>

                <div class="pane-header" style="margin-top: 40px;">
                    <h2>Danger Zone</h2>
                    <p>Permanent actions for your account.</p>
                </div>
                
                <div style="background: #fef2f2; border: 1px solid #fca5a5; padding: 24px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="margin: 0 0 5px 0; color: #991b1b; font-size: 16px;">Delete Account</h4>
                        <p style="margin: 0; font-size: 13px; color: #b91c1c;">Once you delete your account, there is no going back. Please be certain.</p>
                    </div>
                    <button type="button" class="btn btn-danger" onclick="confirm('Are you absolutely sure?')">Delete Account</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    function switchTab(tabId, element) {
        // Remove active class from all tabs
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
        // Add active class to clicked tab
        element.classList.add('active');
        
        // Hide all panes
        document.querySelectorAll('.settings-pane').forEach(p => p.classList.remove('active'));
        // Show target pane
        document.getElementById(tabId).classList.add('active');
    }

    // Hide success message after 4 seconds
    const alertMsg = document.querySelector('.alert-success');
    if (alertMsg) {
        setTimeout(() => {
            alertMsg.style.opacity = '0';
            alertMsg.style.transition = 'opacity 0.5s';
            setTimeout(() => alertMsg.style.display = 'none', 500);
        }, 4000);
    }
</script>

</body>
</html>
