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
            background: white; border-radius: 20px; padding: 10px; border: 1px solid #f1f5f9; box-shadow: 0 5px 20px rgba(0,0,0,0.02);
        }

        .settings-link {
            display: flex; align-items: center; gap: 12px; padding: 15px 20px; color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 12px; transition: 0.3s;
        }

        .settings-link.active {
            background: #fff2ed; color: var(--rest-primary);
        }

        .settings-link:hover:not(.active) {
            background: #f8fafc; color: var(--rest-secondary-dark);
        }

        .panel {
            background: white; border-radius: 20px; padding: 35px; border: 1px solid #f1f5f9; box-shadow: 0 5px 20px rgba(0,0,0,0.02);
        }

        .panel-title { font-size: 20px; font-weight: 800; color: var(--rest-secondary-dark); margin: 0 0 25px 0; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9; }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 14px; border-radius: 12px; border: 2px solid #f1f5f9; font-family: inherit; font-size: 14px; background: #f8fafc; transition: 0.3s; }
        .form-control:focus { border-color: var(--rest-primary); background: white; outline: none; }
        
        .btn-save {
            background: linear-gradient(135deg, #1b2559 0%, #2f3cff 100%); color: white; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer; transition: 0.3s; box-shadow: 0 8px 20px rgba(47, 60, 255, 0.2); width: fit-content; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(47, 60, 255, 0.3); }

        .image-upload-area {
            width: 120px; height: 120px; border-radius: 20px; background: #eef2ff; border: 2px dashed #cbd5e1; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; color: var(--rest-secondary); overflow: hidden; position: relative;
        }
        .image-upload-area:hover { border-color: var(--rest-primary); color: var(--rest-primary); background: #fff2ed; }

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
            <a href="#" class="settings-link active"><i class="ri-store-2-line" style="font-size: 18px;"></i> Store Profile</a>
            <a href="#" class="settings-link"><i class="ri-time-line" style="font-size: 18px;"></i> Operating Hours</a>
            <a href="#" class="settings-link"><i class="ri-bank-card-line" style="font-size: 18px;"></i> Payout Details</a>
            <a href="#" class="settings-link"><i class="ri-lock-password-line" style="font-size: 18px;"></i> Security</a>
        </div>

        <!-- Settings Content -->
        <div class="panel">
            <h2 class="panel-title">Store Profile</h2>
            <form onsubmit="saveSettings(event)">
                <div style="display: flex; gap: 30px; margin-bottom: 30px;">
                    <div>
                        <span class="form-label">Store Logo</span>
                        <div class="image-upload-area">
                            <i class="ri-upload-cloud-2-line" style="font-size: 24px; margin-bottom: 5px;"></i>
                            <span style="font-size: 11px; font-weight: 600;">Upload Logo</span>
                        </div>
                    </div>
                    <div>
                        <span class="form-label">Cover Banner</span>
                        <div class="image-upload-area" style="width: 250px;">
                            <i class="ri-image-add-line" style="font-size: 24px; margin-bottom: 5px;"></i>
                            <span style="font-size: 11px; font-weight: 600;">Upload Cover</span>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Store Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($restaurant['name']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Owner Name</label>
                        <input type="text" name="owner_name" class="form-control" value="<?= htmlspecialchars($restaurant['owner_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($restaurant['email']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($restaurant['phone']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Service Location</label>
                        <select name="location_id" class="form-control" required>
                            <option value="">Select Location</option>
                            <?php while($loc = $locations_res->fetch_assoc()): ?>
                                <option value="<?= $loc['id'] ?>" <?= ($restaurant['location_id'] ?? 0) == $loc['id'] ? 'selected' : '' ?>><?= $loc['name'] ?></option>
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
                                <option value="<?= $hosp['id'] ?>" <?= ($restaurant['hospital_id'] ?? 0) == $hosp['id'] ? 'selected' : '' ?>><?= $hosp['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($restaurant['address']) ?></textarea>
                </div>

                <div style="margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 25px;">
                    <button type="submit" class="btn-save"><i class="ri-save-line"></i> Save Changes</button>
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
