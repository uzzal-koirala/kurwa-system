<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

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

<?php include '../../includes/sidebar.php'; ?>

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

<script src="../../assets/js/sidebar.js"></script>
<script>
    let cart = [];
    let currentFoodItems = [];
    let currentView = 'canteens';

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
        document.getElementById('canteenSelection').style.display = 'none';
        document.getElementById('menuView').style.display = 'block';
        document.getElementById('currentCanteenName').innerText = name;
        document.getElementById('foodItemsContainer').innerHTML = '';
        document.getElementById('menuLoader').style.display = 'block';
        document.getElementById('foodSearch').value = ''; // Clear search when switching view

        // Fetch food items
        fetch(`fetch_menu.php?canteen_id=${canteenId}`)
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
                            <button class="add-btn" onclick="addToCart('${food.name}', ${food.price})">
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

    function addToCart(name, price) {
        cart.push({name, price});
        updateCartUI();
        
        // Simple bounce effect
        const cartFloat = document.getElementById('cartIndicator');
        cartFloat.style.display = 'flex';
        cartFloat.style.transform = 'scale(1.1)';
        setTimeout(() => cartFloat.style.transform = 'scale(1)', 200);
    }

    function updateCartUI() {
        const count = cart.length;
        const total = cart.reduce((sum, item) => sum + parseFloat(item.price), 0);
        
        document.getElementById('cartCount').innerText = `${count} ${count === 1 ? 'Item' : 'Items'}`;
        document.getElementById('cartTotal').innerText = `Rs. ${total.toLocaleString()}`;
    }
</script>
</body>
</html>
