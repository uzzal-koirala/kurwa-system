<?php
require_once '../includes/core/config.php';

echo "<h2>Seeding Random Bookings...</h2>";

// Get some users and caretakers
$users = $conn->query("SELECT id FROM users LIMIT 10")->fetch_all(MYSQLI_ASSOC);
$caretakers = $conn->query("SELECT id, price_per_day FROM caretakers")->fetch_all(MYSQLI_ASSOC);

if (empty($users) || empty($caretakers)) {
    die("Error: No users or caretakers found to seed.");
}

$statuses = ['pending', 'confirmed', 'completed'];

foreach ($caretakers as $ct) {
    // Generate 3 random bookings per caretaker
    for ($i = 0; $i < 3; $i++) {
        $user = $users[array_rand($users)];
        $random_days = rand(1, 20);
        $start_date = date('Y-m-d', strtotime("+$random_days days"));
        $duration = rand(1, 5);
        $end_date = date('Y-m-d', strtotime("$start_date +$duration days"));
        $status = $statuses[array_rand($statuses)];
        $total_price = $ct['price_per_day'] * ($duration + 1);

        $sql = "INSERT INTO caretaker_bookings (user_id, caretaker_id, start_date, end_date, total_price, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissss", $user['id'], $ct['id'], $start_date, $end_date, $total_price, $status);
        
        if ($stmt->execute()) {
            echo "Created $status booking for CT #{$ct['id']} from $start_date to $end_date<br>";
        } else {
            echo "Error seeding: " . $conn->error . "<br>";
        }
        $stmt->close();
    }
}

echo "<h3>Seeding Complete!</h3>";
?>
