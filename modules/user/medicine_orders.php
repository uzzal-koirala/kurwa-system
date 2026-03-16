<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$current_page = 'medicine_orders';
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];

// Fetch pharmacies
$pharmacies_sql = "SELECT * FROM pharmacies WHERE status = 'open' ORDER BY rating DESC";
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
            <?php while($p = $pharmacies_res->fetch_assoc()): ?>
            <div class="pharmacy-card" data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>" onclick="openUploadModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name']) ?>')">
                <button class="view-profile-btn" onclick="goToProfile(event, <?= $p['id'] ?>)" title="View Store Profile">
                    <i class="ri-eye-line"></i>
                </button>
                <div class="pharmacy-image">
                    <img src="<?= $p['image_url'] ?>" alt="<?= htmlspecialchars($p['name']) ?>">
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
    }
</script>
</body>
</html>
