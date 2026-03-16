<?php
/**
 * DB Setup Script - Run this once in your browser to setup coupon tables.
 */
require_once 'includes/config.php';

echo "<h2>Kurwa System - Coupon DB Setup</h2>";

$queries = [
    "CREATE TABLE IF NOT EXISTS `coupons` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `code` varchar(50) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `usage_limit` int(11) NOT NULL DEFAULT 1,
        `times_used` int(11) NOT NULL DEFAULT 0,
        `status` enum('active','inactive') DEFAULT 'active',
        `expiry_date` date DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `coupon_usage` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `coupon_id` int(11) NOT NULL,
        `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_redemption` (`user_id`, `coupon_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `transactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `type` enum('topup','payment','bonus','coupon') NOT NULL,
        `description` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "INSERT IGNORE INTO `coupons` (`code`, `amount`, `usage_limit`, `status`) VALUES
    ('WELCOME100', 100.00, 100, 'active'),
    ('KURWA500', 500.00, 10, 'active'),
    ('BONUS1000', 1000.00, 5, 'active'),
    ('HELLO20', 1000.00, 2, 'active');"
];

foreach ($queries as $sql) {
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>Success executing query: " . substr($sql, 0, 50) . "...</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
    }
}

echo "<h3>Setup Complete!</h3>";
?>
