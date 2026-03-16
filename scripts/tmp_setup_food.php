<?php
require_once __DIR__ . '/../includes/config.php';

// SQL to create tables
$sql = "
CREATE TABLE IF NOT EXISTS canteens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) DEFAULT 'Canteen',
    rating DECIMAL(2,1) DEFAULT 4.5,
    delivery_time VARCHAR(50) DEFAULT '20-30 min',
    image_url VARCHAR(255),
    status ENUM('open', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    canteen_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100),
    is_veg BOOLEAN DEFAULT TRUE,
    image_url VARCHAR(255),
    FOREIGN KEY (canteen_id) REFERENCES canteens(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS food_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    canteen_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (canteen_id) REFERENCES canteens(id)
);
";

if ($conn->multi_query($sql)) {
    echo "Tables created successfully.<br>";
    while ($conn->next_result()) {;} // flush multi_queries
} else {
    echo "Error creating tables: " . $conn->error . "<br>";
}

// Insert sample canteens
$canteens = [
    ['Main Hospital Canteen', 'Healthy & Fresh', 4.8, '15-20 min', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80'],
    ['Sunrise Cafe', 'Coffee & Snacks', 4.5, '10-15 min', 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80'],
    ['Green Leaf Bistro', 'Organic & Vegan', 4.9, '25-35 min', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80'],
    ['City Diner', 'Fast Food', 4.2, '20-30 min', 'https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?auto=format&fit=crop&w=800&q=80']
];

foreach ($canteens as $c) {
    $stmt = $conn->prepare("INSERT INTO canteens (name, type, rating, delivery_time, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $c[0], $c[1], $c[2], $c[3], $c[4]);
    $stmt->execute();
    $canteen_id = $stmt->insert_id;

    // Insert sample foods for each canteen
    $foods = [
        ['Healthy Veggie Bowl', 'Roasted vegetables with quinoa', 250, 'Main Course', 1, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=200&q=80'],
        ['Fresh Fruit Salad', 'Seasonal mixed fruits', 150, 'Starters', 1, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=200&q=80'],
        ['Grilled Chicken Salad', 'Organic greens with grilled chicken', 350, 'Main Course', 0, 'https://images.unsplash.com/photo-1543332164-6e82f355badc?auto=format&fit=crop&w=200&q=80']
    ];

    foreach ($foods as $f) {
        $f_stmt = $conn->prepare("INSERT INTO food_items (canteen_id, name, description, price, category, is_veg, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $f_stmt->bind_param("issdsis", $canteen_id, $f[0], $f[1], $f[2], $f[3], $f[5], $f[6]);
        $f_stmt->execute();
    }
}

echo "Sample data inserted successfully.";
?>
