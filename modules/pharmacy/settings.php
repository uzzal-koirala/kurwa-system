<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['pharmacy_id'])) {
    header("Location: login.php");
    exit;
}

$pharmacy_id = $_SESSION['pharmacy_id'];
$current_page = 'settings';

$stmt = $conn->prepare("SELECT * FROM pharmacies WHERE id = ?");
$stmt->bind_param("i", $pharmacy_id);
$stmt->execute();
$pharmacy = $stmt->get_result()->fetch_assoc();
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
    <title>Pharmacy Settings | Kurwa</title>
    <!-- Assuming pharmacy uses a similar sidebar or standard design -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pharm-primary: #10b981;
            --pharm-secondary: #065f46;
            --bg-light: #f0fdf4;
            --white: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; color: var(--text-dark); margin: 0; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .header { margin-bottom: 30px; }
        .header h1 { font-size: 28px; font-weight: 800; margin: 0; color: var(--pharm-secondary); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 14px; border-radius: 12px; border: 2px solid #f1f5f9; font-family: inherit; font-size: 14px; background: #f8fafc; box-sizing: border-box; }
        .form-control:focus { border-color: var(--pharm-primary); background: white; outline: none; }
        .btn-save { background: var(--pharm-primary); color: white; border: none; padding: 16px 32px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; width: 100%; font-size: 16px; margin-top: 20px; }
        .btn-save:hover { background: var(--pharm-secondary); transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="dashboard.php" style="text-decoration: none; color: var(--pharm-primary); font-weight: 700; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px;">
                <i class="ri-arrow-left-line"></i> Back to Dashboard
            </a>
            <h1>Pharmacy Profile</h1>
            <p style="color: var(--text-muted);">Manage your pharmacy details and service hospital.</p>
        </div>

        <form id="pharmacyForm">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Pharmacy Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($pharmacy['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($pharmacy['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($pharmacy['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Service Location</label>
                    <select name="location_id" class="form-control" required>
                        <option value="">Select Location</option>
                        <?php while($loc = $locations_res->fetch_assoc()): ?>
                            <option value="<?= $loc['id'] ?>" <?= ($pharmacy['location_id'] ?? 0) == $loc['id'] ? 'selected' : '' ?>><?= $loc['name'] ?></option>
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
                            <option value="<?= $hosp['id'] ?>" <?= ($pharmacy['hospital_id'] ?? 0) == $hosp['id'] ? 'selected' : '' ?>><?= $hosp['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="open" <?= ($pharmacy['status'] ?? '') == 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= ($pharmacy['status'] ?? '') == 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Pharmacy Address</label>
                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($pharmacy['address']) ?></textarea>
            </div>

            <button type="submit" class="btn-save" id="saveBtn">Save Profile Changes</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('pharmacyForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('saveBtn');
            btn.innerText = 'Saving...';
            btn.disabled = true;

            const fd = new FormData(e.target);
            try {
                const res = await fetch('handlers/settings_handler.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Settings Saved!', timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: data.message });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Network connection error.' });
            } finally {
                btn.innerText = 'Save Profile Changes';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
