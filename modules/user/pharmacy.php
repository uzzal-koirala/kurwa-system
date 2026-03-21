<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$current_page = 'pharmacy';
$user_id = $_SESSION['user_id'];

// Get user profile info
$user_res = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user_data = $user_res->fetch_assoc();

// Fetch all open pharmacies
$pharmacies = [];
$res = $conn->query("SELECT * FROM pharmacies WHERE status = 'open' ORDER BY rating DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $pharmacies[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Services | Kurwa System</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <!-- Reusing the delivery dashboard CSS for the item grid structure but overriding theme colors inline -->
    <link rel="stylesheet" href="../../assets/css/delivery_dashboard.css"> 
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .page-header {
            margin-bottom: 30px;
        }
        
        /* Pharmacy Specific Colors */
        .search-bar .ri-search-line { color: #0d9488; }
        .search-bar input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,0.1); }
        
        .canteen-card:hover {
            border-color: #99f6e4;
            box-shadow: 0 10px 20px -5px rgba(13,148,136,0.15);
        }
        .c-rating i { color: #10b981; }
        .tags span { background: #f0fdfa; color: #0f766e; }

        .back-btn {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 24px;
            color: #334155;
            transition: 0.2s;
        }
        .back-btn:hover { background: #f8fafc; color: #0d9488; }

        .add-btn { background: #0d9488; }
        .add-btn:hover { background: #0f766e; }
        
        .cart-float { background: #059669; }
        .cart-float:hover { background: #047857; }
        
        .rx-badge {
            background: #fef2f2;
            color: #ef4444;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            margin-left:auto;
        }

        /* Modal specific */
        .checkout-btn {
            background: #059669; color: white; border: none; padding: 16px; border-radius: 12px;
            width: 100%; font-weight: 600; font-size: 16px; cursor: pointer; transition: 0.2s; margin-top:20px;
        }
        .checkout-btn:hover { background: #047857; transform: translateY(-2px); }
    </style>
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-header">
        <h1 style="font-size: 28px; font-weight: 700; color: #0f172a;">Online Pharmacy</h1>
        <p style="color: #64748b;">Get medicines and healthcare supplies delivered to your door.</p>
    </div>

    <!-- View 1: Pharmacies List -->
    <div id="pharmacySelection">
        <div class="search-bar" style="margin-bottom: 30px;">
            <i class="ri-search-line"></i>
            <input type="text" id="pharmacySearch" placeholder="Search for nearby pharmacies..." oninput="filterPharmacies()">
        </div>

        <div class="canteen-grid" id="pharmacyGrid">
            <?php if(empty($pharmacies)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #94a3b8; background: white; border-radius: 20px;">
                    <i class="ri-store-3-line" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                    No pharmacies are currently open.
                </div>
            <?php else: ?>
                <?php foreach($pharmacies as $p): ?>
                    <div class="canteen-card" onclick="loadPharmacy(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>')">
                        <img src="<?= htmlspecialchars($p['image_url'] ?? 'https://images.unsplash.com/photo-1585435557343-3b092031a831?auto=format&fit=crop&w=600&q=80') ?>" alt="Pharmacy" class="c-img">
                        <div class="c-info">
                            <div class="c-header">
                                <h3><?= htmlspecialchars($p['name']) ?></h3>
                                <div class="c-rating"><i class="ri-star-fill"></i> <?= number_format($p['rating'], 1) ?></div>
                            </div>
                            <p style="color: #64748b; font-size: 13px; margin-bottom: 12px;">
                                <i class="ri-map-pin-line"></i> <?= htmlspecialchars($p['address']) ?>
                            </p>
                            <div class="tags">
                                <span><i class="ri-medicine-bottle-line"></i> Rx Meds</span>
                                <span><i class="ri-truck-line"></i> Delivery</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- View 2: Medicine Menu -->
    <div id="medicineView" style="display: none;">
        <button class="back-btn" onclick="showPharmacies()">
            <i class="ri-arrow-left-line"></i> Back to Pharmacies
        </button>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 id="currentPharmacyName" style="font-size: 24px;"></h2>
            <div class="search-bar" style="max-width: 300px; margin: 0;">
                <i class="ri-search-line"></i>
                <input type="text" id="medSearch" placeholder="Search medicines..." oninput="filterMedicines()">
            </div>
        </div>

        <div id="menuLoader" class="loading-spinner">
            <i class="ri-loader-4-line ri-spin"></i> Loading medicines...
        </div>

        <div class="food-grid" id="medicinesContainer">
            <!-- Medicines populated via JS -->
        </div>
    </div>

    <!-- Floating Cart Button -->
    <div class="cart-float" id="cartIndicator" onclick="openCheckout()" style="display:none;">
        <div class="cart-icon-wrapper">
            <i class="ri-shopping-cart-2-fill"></i>
            <span class="cart-badge" id="cartCount"></span>
        </div>
        <div class="cart-total" id="cartTotal"></div>
    </div>

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.6); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
        <div style="background:white; border-radius:24px; width:100%; max-width:480px; padding:30px; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
            <i class="ri-close-line" onclick="closeCheckout()" style="position:absolute; right:24px; top:24px; font-size:28px; cursor:pointer; color:#94a3b8; transition:0.2s;"></i>
            
            <h2 style="font-size:24px; color:#0f172a; margin-bottom:24px; font-weight:700;">Complete Order</h2>
            
            <div id="checkoutItems" style="max-height: 250px; overflow-y: auto; margin-bottom: 20px;"></div>
            
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:14px; font-weight:600; color:#334155; margin-bottom:8px;">Delivery Address</label>
                <input type="text" id="deliveryAddress" placeholder="Enter your full delivery address" value="<?= htmlspecialchars($user_data['address'] ?? '') ?>" required style="width:100%; padding:14px; border:1px solid #e2e8f0; border-radius:12px; font-family:inherit;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:14px; font-weight:600; color:#334155; margin-bottom:8px;">Prescription Link (If Required)</label>
                <input type="url" id="prescriptionLink" placeholder="URL to hosted image (Google Drive/Imgur)" style="width:100%; padding:14px; border:1px solid #e2e8f0; border-radius:12px; font-family:inherit;">
                <span style="font-size:12px; color:#64748b; margin-top:4px; display:block;">Some medicines may be rejected if Rx is not provided.</span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; border-top:2px solid #f1f5f9; padding-top:20px;">
                <span style="color:#64748b; font-weight:600;">Wallet Deduction:</span>
                <span id="checkoutTotal" style="font-size:24px; font-weight:700; color:#059669;"></span>
            </div>
            
            <button id="submitOrderBtn" class="checkout-btn" onclick="submitOrder()">
                Secure Medical Checkout
            </button>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    let cart = JSON.parse(localStorage.getItem('rx_cart')) || [];
    let currentPharmacyId = parseInt(localStorage.getItem('rx_pharmacy_id')) || 0;
    let currentMedicines = [];

    window.onload = function() {
        updateCartUI();
    };

    function filterPharmacies() {
        const query = document.getElementById('pharmacySearch').value.toLowerCase();
        document.querySelectorAll('.canteen-card').forEach(card => {
            const name = card.querySelector('h3').innerText.toLowerCase();
            card.style.display = name.includes(query) ? 'flex' : 'none';
        });
    }

    function filterMedicines() {
        const query = document.getElementById('medSearch').value.toLowerCase();
        document.querySelectorAll('.food-card').forEach(card => {
            const name = card.querySelector('h4').innerText.toLowerCase();
            card.style.display = name.includes(query) ? 'block' : 'none';
        });
    }

    function loadPharmacy(pharmacyId, name) {
        if(currentPharmacyId !== pharmacyId && cart.length > 0) {
            if(!confirm("Changing pharmacies will clear your current cart. Proceed?")) return;
            cart = [];
            updateCartUI();
        }
        
        currentPharmacyId = pharmacyId;
        localStorage.setItem('rx_pharmacy_id', pharmacyId);
        
        document.getElementById('pharmacySelection').style.display = 'none';
        document.getElementById('medicineView').style.display = 'block';
        document.getElementById('currentPharmacyName').innerText = name;
        document.getElementById('medicinesContainer').innerHTML = '';
        document.getElementById('menuLoader').style.display = 'block';

        // Custom direct fetch for UI simplicity since we don't have a fetch API setup yet.
        // Let's create `handlers/fetch_medicines.php` simultaneously.
        fetch(`handlers/fetch_medicines.php?pharmacy_id=${pharmacyId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('menuLoader').style.display = 'none';
                currentMedicines = data;
                renderMedicines(data);
            });
    }

    function renderMedicines(meds) {
        const container = document.getElementById('medicinesContainer');
        if (meds.length === 0) {
            container.innerHTML = `<p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #94a3b8;">This pharmacy has no medicines listed yet.</p>`;
            return;
        }

        container.innerHTML = '';
        meds.forEach(med => {
            const rxTag = med.requires_prescription == 1 ? `<span class="rx-badge">Rx Required</span>` : '';
            const card = `
                <div class="food-card">
                    <img src="${med.image_url}" alt="${med.name}" class="food-img-sm">
                    <div class="food-details">
                        <div style="display:flex; align-items:flex-start; margin-bottom:4px;">
                            <h4 style="margin:0;">${med.name}</h4>
                            ${rxTag}
                        </div>
                        <p class="food-desc">${med.description}</p>
                        <div class="food-price-cta">
                            <span class="price-tag">Rs. ${parseFloat(med.price).toLocaleString()}</span>
                            <button class="add-btn" onclick="addToCart(${med.id}, '${med.name.replace(/'/g, "\\'")}', ${med.price})">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    }

    function showPharmacies() {
        document.getElementById('medicineView').style.display = 'none';
        document.getElementById('pharmacySelection').style.display = 'block';
    }

    function addToCart(id, name, price) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({id, name, price, quantity: 1});
        }
        updateCartUI();
        
        const float = document.getElementById('cartIndicator');
        float.style.display = 'flex';
        float.style.transform = 'scale(1.1)';
        setTimeout(() => float.style.transform = 'scale(1)', 200);
    }

    function updateCartUI() {
        localStorage.setItem('rx_cart', JSON.stringify(cart));
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);
        const total = cart.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
        
        if (count > 0) {
            document.getElementById('cartCount').innerText = `${count} ${count === 1 ? 'Item' : 'Items'}`;
            document.getElementById('cartTotal').innerText = `Rs. ${total.toLocaleString()}`;
            document.getElementById('cartIndicator').style.display = 'flex';
        } else {
            document.getElementById('cartIndicator').style.display = 'none';
        }
    }

    function openCheckout() {
        if(cart.length === 0) return;
        document.getElementById('checkoutModal').style.display = 'flex';
        
        const container = document.getElementById('checkoutItems');
        container.innerHTML = '';
        cart.forEach((item, index) => {
            const itemTotal = parseFloat(item.price) * item.quantity;
            container.innerHTML += `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                <div style="flex: 1;">
                    <span style="font-weight:600; font-size:15px; display:block;">${item.name}</span>
                    <span style="color:#64748b; font-size:12px;">Rs. ${parseFloat(item.price).toLocaleString()} each</span>
                </div>
                
                <div style="display:flex; align-items:center; gap:12px;">
                    <strong style="color:#0d9488; width: 70px; text-align:right;">Rs. ${itemTotal.toLocaleString()}</strong>
                    
                    <div style="display:flex; align-items:center; background:#fff; border:1px solid #cbd5e1; border-radius:8px; overflow:hidden;">
                        <button type="button" onclick="updateQuantity(${index}, -1)" style="border:none; background:transparent; padding:4px 10px; cursor:pointer; font-weight:700;">-</button>
                        <span style="width:24px; text-align:center; font-weight:600; font-size:14px;">${item.quantity}</span>
                        <button type="button" onclick="updateQuantity(${index}, 1)" style="border:none; background:transparent; padding:4px 10px; cursor:pointer; font-weight:700; color:#0d9488;">+</button>
                    </div>
                </div>
            </div>`;
        });
        
        const total = cart.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
        document.getElementById('checkoutTotal').innerText = `Rs. ${total.toLocaleString()}`;
    }

    function updateQuantity(index, change) {
        if (cart[index].quantity + change > 0) {
            cart[index].quantity += change;
            updateCartUI();
            openCheckout();
        } else {
            cart.splice(index, 1);
            updateCartUI();
            cart.length === 0 ? closeCheckout() : openCheckout();
        }
    }

    function closeCheckout() {
        document.getElementById('checkoutModal').style.display = 'none';
    }

    function submitOrder() {
        if (cart.length === 0) return;
        
        const addr = document.getElementById('deliveryAddress').value.trim();
        const rxLink = document.getElementById('prescriptionLink').value.trim();
        
        if (!addr) {
            alert("Please enter a delivery address.");
            return;
        }

        const total = cart.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
        
        const payload = {
            pharmacy_id: currentPharmacyId,
            delivery_address: addr,
            prescription_url: rxLink,
            total_amount: total,
            items: cart
        };

        const btn = document.getElementById('submitOrderBtn');
        const oldTxt = btn.innerText;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Processing Payment...';
        btn.disabled = true;

        fetch('handlers/place_pharmacy_order.php', {
            method: 'POST',
            body: JSON.stringify(payload),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Medical Order placed successfully! Amount deducted from your wallet.');
                cart = [];
                updateCartUI();
                closeCheckout();
            } else {
                alert('Failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            alert('A network error occurred.');
            console.error(err);
        })
        .finally(() => {
            btn.innerHTML = oldTxt;
            btn.disabled = false;
        });
    }
</script>
</body>
</html>
