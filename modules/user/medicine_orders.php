<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$current_page = 'medicine_orders';
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];

// Fetch pharmacies linked to user's hospital
$hospital_id = $_SESSION['hospital_id'];
$pharmacies_sql = "SELECT id, name, address, image_url, status, rating, delivery_time, opening_time, closing_time 
                  FROM pharmacies 
                  WHERE status = 'open' AND hospital_id = $hospital_id 
                  ORDER BY rating DESC";
$pharmacies_res = $conn->query($pharmacies_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Orders | Kurwa</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/medicine_order.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .closed-vendor { opacity: 0.7; filter: grayscale(0.6); cursor: not-allowed !important; }
        .status-badge.status-closed { background: #ef4444; }
        .status-badge.status-open { background: #22c55e; }

        /* Store Closed Modal CSS */
        .closed-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        .closed-modal-card {
            background: white;
            border-radius: 32px;
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.2);
            transform: scale(0.9);
            animation: cardPop 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardPop {
            to { transform: scale(1); opacity: 1; }
        }

        .closed-icon-ctn {
            width: 90px;
            height: 90px;
            background: #f5f3ff;
            color: #8b5cf6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 25px;
            border: 2px solid #ede9fe;
            position: relative;
        }

        .closed-icon-ctn::after {
            content: '';
            position: absolute;
            inset: -8px;
            border: 2px solid #f5f3ff;
            border-radius: 50%;
            animation: pulse-ring-purple 2s infinite;
        }

        @keyframes pulse-ring-purple {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        .closed-time-badge {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 20px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
            font-weight: 600;
            color: #475569;
        }

        .closed-btn {
            background: #0f172a;
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 16px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: 0.3s;
        }

        .closed-btn:hover {
            background: #334155;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <button class="mobile-menu-btn" id="openSidebar" type="button">
        <i class="ri-menu-line"></i>
    </button>

    <div class="med-order-container">
        
        <div class="med-header">
            <h1><i class="ri-capsule-fill"></i> Order Medicines</h1>
            <div class="search-area">
                <div class="search-box">
                    <i class="ri-search-line"></i>
                    <input type="text" id="storeSearch" placeholder="Search pharmacies..." oninput="filterStores()">
                </div>
            </div>
        </div>

        <div id="noResults" style="display: none; text-align: center; padding: 60px 20px; color: #94a3b8;">
            <i class="ri-search-eye-line" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
            <p>No pharmacies match your search.</p>
        </div>

        <div class="pharmacy-grid" id="pharmacyGrid">
            <?php while($p = $pharmacies_res->fetch_assoc()): 
                $curr_time = date('H:i:s');
                $open = $p['opening_time'];
                $close = $p['closing_time'];
                
                if ($open <= $close) {
                    $is_open = ($curr_time >= $open && $curr_time <= $close);
                } else {
                    $is_open = ($curr_time >= $open || $curr_time <= $close);
                }
                
                $status_text = $is_open ? 'Open' : 'Closed';
                $status_class = $is_open ? 'status-open' : 'status-closed';
                $open_fmt = date('h:i A', strtotime($open));
                $close_fmt = date('h:i A', strtotime($close));
                $click_action = $is_open ? "openUploadModal({$p['id']}, '" . addslashes($p['name']) . "')" : "showClosedModal(this)";
            ?>
            <div class="pharmacy-card <?= !$is_open ? 'closed-vendor' : '' ?>" 
                 data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>" 
                 data-full-name="<?= htmlspecialchars($p['name']) ?>"
                 data-open-time="<?= $open_fmt ?>"
                 data-close-time="<?= $close_fmt ?>"
                 onclick="<?= $click_action ?>">
                <button class="view-profile-btn" onclick="goToProfile(event, <?= $p['id'] ?>)" title="View Store Profile">
                    <i class="ri-eye-line"></i>
                </button>
                <div class="pharmacy-image">
                    <?php 
                        $img = !empty($p['image_url']) ? '../../'.$p['image_url'] : 'https://images.unsplash.com/photo-1576602976047-174e57a47881?w=500&q=80';
                    ?>
                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    <span class="status-badge <?= $status_class ?>" style="position: absolute; top: 10px; left: 10px; padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 700; text-transform: uppercase; color: white;"><?= $status_text ?></span>
                </div>
                <div class="pharmacy-info">
                    <h3><?= htmlspecialchars($p['name']) ?></h3>
                    <p><i class="ri-map-pin-2-line"></i> <?= htmlspecialchars($p['address']) ?></p>
                    <div class="pharmacy-meta">
                        <span class="p-meta-item rating"><i class="ri-star-fill"></i> <?= $p['rating'] ?></span>
                        <span class="p-meta-item time"><i class="ri-truck-line"></i> <?= $p['delivery_time'] ?></span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </div>
</div>

<!-- Prescription Upload Modal -->
<div class="med-modal" id="uploadModal">
    <div class="med-modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 id="modalStoreName" style="font-size: 20px;">Upload Prescription</h2>
            <button onclick="closeModal()" style="background:none; border:none; font-size: 24px; cursor:pointer; color: #94a3b8;"><i class="ri-close-line"></i></button>
        </div>
        
        <p style="color: #64748b; font-size: 14px;">Please upload a clear photo of your doctor's prescription (receipt) to proceed.</p>
        
        <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
            <input type="file" id="fileInput" style="display: none;" onchange="handleFile(this)">
            <i class="ri-file-upload-line"></i>
            <p id="uploadText">Drag & Drop or <strong>Browse File</strong></p>
        </div>

        <div id="filePreview" style="display:none; margin-bottom: 20px; text-align: center;">
            <div style="background: #f1f5f9; padding: 10px; border-radius: 10px; display: inline-flex; align-items: center; gap: 10px;">
                <i class="ri-image-line" style="color: #3542f3;"></i>
                <span id="fileName" style="font-size: 14px; font-weight: 500;">prescription.jpg</span>
            </div>
        </div>

        <button class="btn-primary" id="submitBtn" disabled onclick="submitOrder()">Place Order & Wait for Delivery</button>
    </div>
</div>

<!-- Pharmacy Closed Modal -->
<div id="closedModal" class="closed-modal-overlay">
    <div class="closed-modal-card">
        <div class="closed-icon-ctn">
            <i class="ri-time-line"></i>
        </div>
        <h2 id="closedStoreName" style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Pharmacy Closed</h2>
        <p style="color: #64748b; font-size: 15px; line-height: 1.5;">This pharmacy is currently off-duty and not accepting new prescriptions at this moment.</p>
        
        <div class="closed-time-badge">
            <i class="ri-calendar-todo-line" style="color: #8b5cf6;"></i>
            <span>Hours: <span id="closedOperatingHours">08:00 AM - 10:00 PM</span></span>
        </div>

        <button onclick="closeClosedModal()" class="closed-btn">Understood</button>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    let selectedStoreId = null;

    function filterStores() {
        const query = document.getElementById('storeSearch').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.pharmacy-card');
        let found = false;
        
        cards.forEach(card => {
            const name = card.dataset.name;
            if (name.includes(query)) {
                card.style.display = 'flex';
                found = true;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('noResults').style.display = found ? 'none' : 'block';
        document.getElementById('pharmacyGrid').style.display = found ? 'grid' : 'none';
    }

    function goToProfile(event, id) {
        event.stopPropagation(); // Prevent opening upload modal
        window.location.href = `pharmacy_profile.php?id=${id}`;
    }

    function openUploadModal(id, name) {
        selectedStoreId = id;
        document.getElementById('modalStoreName').innerText = name;
        document.getElementById('uploadModal').style.display = 'flex';
        resetUpload();
    }

    function closeModal() {
        document.getElementById('uploadModal').style.display = 'none';
    }

    function handleFile(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('fileName').innerText = file.name;
            document.getElementById('filePreview').style.display = 'block';
            document.getElementById('uploadText').innerText = "File selected successfully!";
            document.getElementById('submitBtn').disabled = false;
        }
    }

    function resetUpload() {
        document.getElementById('fileInput').value = '';
        document.getElementById('filePreview').style.display = 'none';
        document.getElementById('uploadText').innerHTML = "Drag & Drop or <strong>Browse File</strong>";
        document.getElementById('submitBtn').disabled = true;
    }

    function submitOrder() {
        const btn = document.getElementById('submitBtn');
        btn.innerText = "Submitting Order...";
        btn.disabled = true;

        // Mock submission
        setTimeout(() => {
            alert("Order placed successfully! Please wait while the pharmacy verifies your prescription.");
            closeModal();
            btn.innerText = "Place Order & Wait for Delivery";
            btn.disabled = false;
        }, 1500);
    }

    // Auto-open modal if store ID is passed in URL
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const uploadId = urlParams.get('upload');
        if (uploadId) {
            const card = document.querySelector(`.pharmacy-card[onclick*="openUploadModal(${uploadId}"]`);
            if (card) {
                // Find the name from the card to pass to openUploadModal
                const name = card.dataset.name.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                openUploadModal(parseInt(uploadId), name);
            }
        }
    };

    // Close on outside click
    window.onclick = function(event) {
        if (event.target == document.getElementById('uploadModal')) {
            closeModal();
        }
        if (event.target == document.getElementById('closedModal')) {
            closeClosedModal();
        }
    }

    function showClosedModal(el) {
        const name = el.dataset.fullName;
        const openTime = el.dataset.openTime;
        const closeTime = el.dataset.closeTime;
        
        document.getElementById('closedStoreName').innerText = name + ' is Closed';
        document.getElementById('closedOperatingHours').innerText = `${openTime} - ${closeTime}`;
        document.getElementById('closedModal').style.display = 'flex';
    }

    function closeClosedModal() {
        document.getElementById('closedModal').style.display = 'none';
    }
</script>
</body>
</html>
