<?php
require_once '../includes/core/config.php';

// Drop tables if they exist to clear bad structure
$conn->query("DROP TABLE IF EXISTS `messages` ");
$conn->query("DROP TABLE IF EXISTS `caretaker_bookings` ");

echo "Tables dropped.\n";

$queries = [
    "CREATE TABLE `caretaker_bookings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `caretaker_id` int(11) NOT NULL,
        `start_date` date NOT NULL,
        `end_date` date NOT NULL,
        `total_price` decimal(10,2) NOT NULL,
        `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`caretaker_id`) REFERENCES `caretakers`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE `messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `sender_id` int(11) NOT NULL,
        `receiver_id` int(11) NOT NULL,
        `receiver_type` enum('user','caretaker') NOT NULL,
        `message` text NOT NULL,
        `is_read` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($queries as $sql) {
    if ($conn->query($sql)) {
        echo "Success: " . substr($sql, 0, 30) . "...\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}
?>
