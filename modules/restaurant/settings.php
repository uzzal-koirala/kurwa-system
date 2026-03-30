<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['restaurant_id'])) {
    header("Location: login.php");
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];
$current_page = 'settings';

$stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$restaurant = $stmt->get_result()->fetch_assoc();
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
    <title>Settings | Restaurant Partner</title>
    <link rel="stylesheet" href="../../assets/css/restaurant_sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title { font-size: 26px; font-weight: 800; color: var(--rest-secondary-dark); margin: 0; letter-spacing: -0.5px; }
        .main-content { margin-left: var(--sidebar-width); padding: 40px 50px; transition: all 0.3s ease; }

        .settings-container {
            display: grid; grid-template-columns: 250px 1fr; gap: 30px; align-items: start;
        }

        .settings-nav {
            background: white; 
            border-radius: 24px; 
            padding: 15px; 
            border: 1px solid #f1f5f9; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            position: sticky;
            top: 20px;
        }

        .settings-link {
            display: flex; 
            align-items: center; 
            gap: 14px; 
            padding: 16px 20px; 
            color: #64748b; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 15px; 
            border-radius: 16px; 
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            margin-bottom: 5px;
        }

        .settings-link i {
            font-size: 20px;
            transition: 0.3s;
        }

        .settings-link.active {
            background: linear-gradient(135deg, rgba(47, 60, 255, 0.1) 0%, rgba(47, 60, 255, 0.02) 100%); 
            color: var(--rest-primary);
            box-shadow: inset 3px 0 0 var(--rest-primary);
        }

        .settings-link:hover:not(.active) {
            background: #f8fafc; 
            color: var(--rest-secondary-dark);
            transform: translateX(4px);
        }

        .panel {
            background: white; 
            border-radius: 28px; 
            padding: 40px; 
            border: 1px solid #f1f5f9; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
        }

        .panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--rest-secondary-dark), var(--rest-primary));
        }

        .panel-title { 
            font-size: 24px; 
            font-weight: 800; 
            color: var(--rest-secondary-dark); 
            margin: 0 0 5px 0; 
        }

        .form-group { margin-bottom: 24px; }
        .form-label { display: block; font-size: 14px; font-weight: 700; color: var(--rest-secondary-dark); margin-bottom: 10px; }
        
        .input-wrapper {
            position: relative;
            transition: 0.3s;
        }

        .input-wrapper i {
            position: absolute; 
            left: 18px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #94a3b8; 
            font-size: 20px;
            transition: 0.3s;
            z-index: 5;
        }

        .form-control { 
            width: 100%; 
            padding: 16px; 
            padding-left: 50px;
            border-radius: 16px; 
            border: 2px solid #f1f5f9; 
            font-family: inherit; 
            font-size: 15px; 
            background: #f8fafc; 
            color: var(--rest-secondary-dark);
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); 
            outline: none;
        }
        
        .form-control:focus { 
            border-color: var(--rest-primary); 
            background: white; 
            box-shadow: 0 0 0 5px rgba(47, 60, 255, 0.1); 
        }

        .form-control:focus + i, .input-wrapper:focus-within i {
            color: var(--rest-primary);
        }
        
        .btn-save {
            background: linear-gradient(135deg, var(--rest-primary) 0%, #1A237E 100%); 
            color: white; 
            border: none; 
            padding: 16px 35px; 
            border-radius: 16px; 
            font-weight: 800; 
            font-size: 16px; 
            cursor: pointer; 
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); 
            box-shadow: 0 10px 25px rgba(47, 60, 255, 0.3); 
            display: inline-flex; 
            align-items: center; 
            gap: 10px;
            letter-spacing: 0.5px;
        }
        
        .btn-save:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 15px 35px rgba(47, 60, 255, 0.4); 
            background: linear-gradient(135deg, #1A237E 0%, var(--rest-primary) 100%);
        }

        .image-upload-area {
            border-radius: 20px; 
            background: #f8fafc; 
            border: 2px dashed #cbd5e1; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            cursor: pointer; 
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); 
            color: #94a3b8; 
            position: relative;
            overflow: hidden;
        }
        
        .image-upload-area:hover { 
            border-color: var(--rest-primary); 
            color: var(--rest-primary); 
            background: rgba(47, 60, 255, 0.02); 
            transform: translateY(-2px);
        }

        @media (max-width: 1024px) {

            .main-content { padding: 20px; margin-left: 0 !important; }
            .mobile-toggle { display: block !important; }
        }
        @media (max-width: 900px) {
            .settings-container { grid-template-columns: 1fr; }
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
                <h1 class="page-title">Settings</h1>
                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Manage your store profile and preferences.</p>
            </div>
        </div>
    </div>

    <div class="settings-container">
        <!-- Sidebar Nav inside Settings -->
        <div class="settings-nav">
            <a href="#" class="settings-link active"><i class="ri-store-2-line"></i> Store Profile</a>
            <a href="#" class="settings-link"><i class="ri-time-line"></i> Operating Hours</a>
            <a href="#" class="settings-link"><i class="ri-bank-card-line"></i> Payout Details</a>
            <a href="#" class="settings-link"><i class="ri-shield-keyhole-line"></i> Security</a>
            <a href="#" class="settings-link"><i class="ri-notification-3-line"></i> Notifications</a>
        </div>

        <div class="panel">
            <div style="border-bottom: 2px solid #f1f5f9; margin-bottom: 35px; padding-bottom: 20px;">
                <h2 class="panel-title">Store Profile</h2>
                <p style="font-size: 14px; color: #64748b; margin: 0;">Update your restaurant's public identity and contact info.</p>
            </div>
            
            <form onsubmit="saveSettings(event)">
                <div style="display: flex; gap: 30px; margin-bottom: 40px; background: #f8fafc; padding: 30px; border-radius: 24px; border: 1px solid #f1f5f9; flex-wrap: wrap;">
                    <div style="text-align: center;">
                        <span class="form-label" style="text-align: center; margin-bottom: 12px;">Store Logo</span>
                        <div class="image-upload-area" style="width: 130px; height: 130px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); background: white;">
                            <i class="ri-image-add-fill" style="font-size: 36px; margin-bottom: 8px;"></i>
                            <span style="font-size: 12px; font-weight: 700;">Upload Logo</span>
                        </div>
                    </div>
                    <div style="flex-grow: 1; min-width: 250px;">
                        <span class="form-label" style="margin-bottom: 12px;">Cover Banner</span>
                        <div class="image-upload-area" style="width: 100%; height: 130px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); background: white;">
                            <i class="ri-landscape-fill" style="font-size: 36px; margin-bottom: 8px;"></i>
                            <span style="font-size: 12px; font-weight: 700;">Change Cover Photo</span>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                    <div class="form-group">
                        <label class="form-label">Store Name</label>
                        <div class="input-wrapper">
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($restaurant['name']) ?>">
                            <i class="ri-store-3-line"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Primary Email</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($restaurant['email']) ?>">
                            <i class="ri-mail-line"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <div class="input-wrapper">
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($restaurant['phone']) ?>">
                            <i class="ri-phone-line"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Service City / Area</label>
                        <div class="input-wrapper">
                            <select name="location_id" class="form-control" required style="appearance: none;">
                                <option value="">Select Location</option>
                                <?php while($loc = $locations_res->fetch_assoc()): ?>
                                    <option value="<?= $loc['id'] ?>" <?= ($restaurant['location_id'] ?? 0) == $loc['id'] ? 'selected' : '' ?>><?= $loc['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                            <i class="ri-map-pin-line"></i>
                            <i class="ri-arrow-down-s-line" style="left: auto; right: 20px; font-size: 24px; pointer-events: none;"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label class="form-label">Full Address & Landmark</label>
                    <div class="input-wrapper">
                        <textarea name="address" class="form-control" rows="3" placeholder="Enter complete address..." style="padding-left: 20px;"><?= htmlspecialchars($restaurant['address']) ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 45px; border-top: 2px dashed #f1f5f9; padding-top: 35px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-save">
                        <i class="ri-checkbox-circle-fill"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../assets/js/sidebar.js"></script>
<script>
    function saveSettings(e) {
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);

        fetch('handlers/settings_handler.php', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: res.message
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred.'
            });
        });
    }
</script>
</body>
</html>
