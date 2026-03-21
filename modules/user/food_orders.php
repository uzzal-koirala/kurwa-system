<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$current_page = 'food_orders';
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];

// Fetch canteens for the initial view
$canteens_sql = "SELECT * FROM canteens WHERE status = 'open' ORDER BY rating DESC";
$canteens_res = $conn->query($canteens_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Orders | Kurwa</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/food_order.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
            font-size: 24px;
            color: #3542f3;
        }
    </style>
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <button class="mobile-menu-btn" id="openSidebar" type="button">
        <i class="ri-menu-line"></i>
    </button>

    <div class="food-order-container">
        
        <!-- View 1: Canteen Selection -->
        <div id="canteenSelection">
            <div class="order-header">
                <h1><i class="ri-restaurant-fill"></i> Order Delicious Food</h1>
            </div>

            <div class="search-area">
                <div class="search-box">
                    <i class="ri-search-line"></i>
                    <input type="text" id="foodSearch" placeholder="Search for food or restaurants..." oninput="handleSearch()">
                </div>
            </div>

            <div class="canteens-grid" id="canteensGrid">
                <?php 
                $canteens_res->data_seek(0);
                while($canteen = $canteens_res->fetch_assoc()): 
                ?>
                <div class="canteen-card" data-name="<?= strtolower(htmlspecialchars($canteen['name'])) ?>" onclick="loadMenu(<?= $canteen['id'] ?>, '<?= htmlspecialchars($canteen['name']) ?>')">
                    <div class="canteen-img-wrapper">
                        <img src="<?= $canteen['image_url'] ?>" alt="<?= htmlspecialchars($canteen['name']) ?>">
                        <span class="canteen-badge"><?= htmlspecialchars($canteen['type']) ?></span>
                        <span class="canteen-status">Open</span>
                    </div>
                    <div class="canteen-info">
                        <h3><?= htmlspecialchars($canteen['name']) ?></h3>
                        <p class="canteen-tags">Fast Food • Healthy • Quick Delivery</p>
                        <div class="canteen-meta">
                            <span class="meta-item rating"><i class="ri-star-fill"></i> <?= $canteen['rating'] ?></span>
                            <span class="meta-item time"><i class="ri-history-line"></i> <?= $canteen['delivery_time'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- View 2: Menu Browser (Hidden by default) -->
        <div id="menuView">
            <div class="menu-header">
                <div class="back-btn" onclick="showCanteens()">
                    <i class="ri-arrow-left-line"></i>
                </div>
                <div class="menu-title-area">
                    <h2 id="currentCanteenName">Canteen Name</h2>
                    <p id="canteenMetaInfo">Rating: 4.8 • Healthy & Fresh</p>
                </div>
            </div>

            <div class="loading-spinner" id="menuLoader">
                <i class="ri-loader-4-line ri-spin"></i>
            </div>

            <div class="foods-grid" id="foodItemsContainer">
                <!-- Food Items will be injected here via JS -->
            </div>
        </div>

    </div>
</div>

<!-- Floating Cart Indicator -->
<div class="cart-float" id="cartIndicator" onclick="alert('Proceeding to Checkout...')">
    <i class="ri-shopping-basket-fill"></i>
    <span id="cartCount">0 Items</span>
    <span class="divider">|</span>
    <span id="cartTotal">Rs. 0</span>
</div>

<!-- Checkout Modal -->
<div id="checkoutModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeCheckout()">&times;</span>
        <h2 style="margin-top:0; color:#0f172a;"><i class="ri-shopping-basket-fill" style="color:var(--primary);"></i> Checkout</h2>
        <div id="checkoutItems" style="margin-bottom: 20px; max-height:200px; overflow-y:auto; border-bottom:1px solid #e2e8f0; padding-bottom:15px;"></div>
        
        <div class="price-summary" style="background:#f8fafc; padding:15px; border-radius:12px; margin-bottom:15px;">
            <div style="display:flex; justify-content:space-between; font-weight:700; font-size:18px; color:#1e293b;">
                <span>Total Amount:</span>
                <span id="checkoutTotal">Rs. 0</span>
            </div>
        </div>

        <form id="checkoutForm" onsubmit="submitOrder(event)">
            <div style="display:flex; flex-direction:column; gap:8px;">
                <label style="font-weight:600; font-size:14px; color:#475569;">Delivery Address</label>
                <input type="text" id="deliveryAddress" required placeholder="Apt 4B, 123 Main St..." style="padding:12px; border:1px solid #e2e8f0; border-radius:10px; font-family:inherit;">
            </div>
            <button type="submit" style="width:100%; margin-top:20px; background:#3542f3; color:#fff; border:none; padding:14px; border-radius:12px; font-weight:700; cursor:pointer; font-size:15px;">
                <i class="ri-checkbox-circle-line"></i> Place Order
            </button>
        </form>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    let cart = [];
    let currentFoodItems = [];
    let currentView = 'canteens';
    let currentCanteenId = 0;

    function handleSearch() {
        const query = document.getElementById('foodSearch').value.toLowerCase().trim();
        
        if (currentView === 'canteens') {
            const cards = document.querySelectorAll('.canteen-card');
            cards.forEach(card => {
                const name = card.dataset.name;
                if (name.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        } else {
            const filteredFoods = currentFoodItems.filter(food => 
                food.name.toLowerCase().includes(query) || 
                food.description.toLowerCase().includes(query)
            );
            renderFoods(filteredFoods);
        }
    }

    function loadMenu(canteenId, name) {
        currentView = 'menu';
        currentCanteenId = canteenId;
        document.getElementById('canteenSelection').style.display = 'none';
        document.getElementById('menuView').style.display = 'block';
        document.getElementById('currentCanteenName').innerText = name;
        document.getElementById('foodItemsContainer').innerHTML = '';
        document.getElementById('menuLoader').style.display = 'block';
        document.getElementById('foodSearch').value = ''; // Clear search when switching view

        // Fetch food items
        fetch(`handlers/fetch_menu.php?canteen_id=${canteenId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('menuLoader').style.display = 'none';
                currentFoodItems = data;
                renderFoods(data);
            })
            .catch(err => {
                console.error('Error fetching menu:', err);
                document.getElementById('menuLoader').innerHTML = 'Error loading menu. Please try again.';
            });
    }

    function renderFoods(foods) {
        const container = document.getElementById('foodItemsContainer');
        if (foods.length === 0) {
            container.innerHTML = `<p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #94a3b8;">No food items match your search.</p>`;
            return;
        }

        container.innerHTML = '';
        foods.forEach(food => {
            const card = `
                <div class="food-card">
                    <img src="${food.image_url}" alt="${food.name}" class="food-img-sm">
                    <div class="food-details">
                        <h4>${food.name}</h4>
                        <p class="food-desc">${food.description}</p>
                        <div class="food-price-cta">
                            <span class="price-tag">Rs. ${parseFloat(food.price).toLocaleString()}</span>
                            <button class="add-btn" onclick="addToCart(${food.id}, '${food.name.replace(/'/g, "\\'")}', ${food.price})">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    }

    function showCanteens() {
        currentView = 'canteens';
        document.getElementById('menuView').style.display = 'none';
        document.getElementById('canteenSelection').style.display = 'block';
        document.getElementById('foodSearch').value = ''; // Clear search when returning
        
        // Reset canteen cards display
        document.querySelectorAll('.canteen-card').forEach(card => card.style.display = 'flex');
    }

    function addToCart(id, name, price) {
        cart.push({id, name, price});
        updateCartUI();
        
        // Simple bounce effect
        const cartFloat = document.getElementById('cartIndicator');
        cartFloat.style.display = 'flex';
        cartFloat.style.transform = 'scale(1.1)';
        cartFloat.onclick = openCheckout;
        setTimeout(() => cartFloat.style.transform = 'scale(1)', 200);
    }

    function updateCartUI() {
        const count = cart.length;
        const total = cart.reduce((sum, item) => sum + parseFloat(item.price), 0);
        
        document.getElementById('cartCount').innerText = `${count} ${count === 1 ? 'Item' : 'Items'}`;
        document.getElementById('cartTotal').innerText = `Rs. ${total.toLocaleString()}`;
        
        if(count === 0) {
            document.getElementById('cartIndicator').style.display = 'none';
        }
    }

    function openCheckout() {
        if(cart.length === 0) {
            alert('Your cart is empty!');
            return;
        }
        document.getElementById('checkoutModal').style.display = 'flex';
        
        const container = document.getElementById('checkoutItems');
        container.innerHTML = '';
        cart.forEach(item => {
            container.innerHTML += `<div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:14px; color:#334155;">
                <span>${item.name}</span>
                <strong style="color:#0f172a;">Rs. ${item.price.toLocaleString()}</strong>
            </div>`;
        });
        
        const total = cart.reduce((sum, item) => sum + parseFloat(item.price), 0);
        document.getElementById('checkoutTotal').innerText = `Rs. ${total.toLocaleString()}`;
    }

    function closeCheckout() {
        document.getElementById('checkoutModal').style.display = 'none';
        document.getElementById('deliveryAddress').value = '';
    }

    async function submitOrder(e) {
        e.preventDefault();
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Processing...';
        submitBtn.disabled = true;

        const address = document.getElementById('deliveryAddress').value;
        const total = cart.reduce((sum, item) => sum + parseFloat(item.price), 0);
        
        const payload = {
            restaurant_id: currentCanteenId,
            delivery_address: address,
            total_amount: total,
            items: cart
        };
        
        try {
            const response = await fetch('handlers/place_food_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (result.success) {
                alert('Order placed successfully! The restaurant has received your order.');
                cart = [];
                updateCartUI();
                closeCheckout();
            } else {
                alert('Failed to place order: ' + result.message);
            }
        } catch (error) {
            console.error('Checkout error:', error);
            alert('An unexpected error occurred during checkout.');
        } finally {
            submitBtn.innerHTML = '<i class="ri-checkbox-circle-line"></i> Place Order';
            submitBtn.disabled = false;
        }
    }
</script>
</body>
</html>
