<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

// Verify Admin Role
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../user/user_dashboard.php");
    exit;
}

$current_page = "settings";
$user_name = $_SESSION['full_name'];

$success_msg = '';
$error_msg = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $onboarding_step1 = trim($_POST['onboarding_step1_question'] ?? '');
    $onboarding_step2 = trim($_POST['onboarding_step2_question'] ?? '');

    if (!empty($onboarding_step1) && !empty($onboarding_step2)) {
        $settings = [
            'onboarding_step1_question' => $onboarding_step1,
            'onboarding_step2_question' => $onboarding_step2
        ];

        $conn->begin_transaction();
        try {
            foreach ($settings as $key => $value) {
                $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->bind_param("sss", $key, $value, $value);
                $stmt->execute();
                $stmt->close();
            }
            $conn->commit();
            $success_msg = "Settings updated successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = "Failed to update settings: " . $e->getMessage();
        }
    } else {
        $error_msg = "All fields are required.";
    }
}

// Fetch current values
$step1_q = get_setting('onboarding_step1_question', 'Where are you located?');
$step2_q = get_setting('onboarding_step2_question', 'Which hospital are you at?');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Settings | Admin Control</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        .settings-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .settings-section {
            margin-bottom: 30px;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--admin-text-muted);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--admin-border);
            border-radius: 12px;
            padding: 15px 20px;
            color: var(--admin-text);
            font-family: inherit;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--admin-primary);
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        .save-btn {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-accent) 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.3);
        }
        
        .save-btn:active {
            transform: scale(0.98);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .section-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--admin-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
    </style>
</head>
<body class="admin-body">

<?php include INC_PATH . '/components/admin_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="admin-header">
        <div class="header-left">
            <h1>Global Settings</h1>
            <p style="color: var(--admin-text-muted);">Manage system-wide configuration and behavior.</p>
        </div>
        <div class="header-right">
             <div class="date-badge" style="background: var(--admin-card-bg); padding: 10px 20px; border-radius: 12px; border: 1px solid var(--admin-border);">
                <i class="ri-settings-4-line"></i> Configuration
            </div>
        </div>
    </div>

    <div class="settings-container">
        <?php if ($success_msg): ?>
            <div class="alert alert-success">
                <i class="ri-checkbox-circle-line"></i>
                <?= $success_msg ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-error">
                <i class="ri-error-warning-line"></i>
                <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Onboarding Section -->
            <div class="admin-panel-box settings-section">
                <div class="admin-panel-header" style="margin-bottom: 30px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="section-icon">
                            <i class="ri-user-add-line"></i>
                        </div>
                        <div>
                            <h3 style="margin-bottom: 2px;">Onboarding Questionnaire</h3>
                            <p style="font-size: 12px; color: var(--admin-text-muted); margin: 0;">Customize questions asked to new users during setup.</p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Step 1 Question (Location)</label>
                    <input type="text" name="onboarding_step1_question" class="form-control" value="<?= htmlspecialchars($step1_q) ?>" placeholder="e.g. Where are you located?">
                </div>

                <div class="form-group">
                    <label class="form-label">Step 2 Question (Hospital)</label>
                    <input type="text" name="onboarding_step2_question" class="form-control" value="<?= htmlspecialchars($step2_q) ?>" placeholder="e.g. Which hospital are you at?">
                </div>
            </div>

            <div style="text-align: right; margin-top: 30px;">
                <button type="submit" name="save_settings" class="save-btn">
                    <i class="ri-save-3-line"></i> Save Global Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
