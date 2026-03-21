<?php
require_once __DIR__ . '/../includes/config.php';

// SQL to create caretaker_bookings table
$sql = "CREATE TABLE IF NOT EXISTS caretaker_bookings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    caretaker_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    booking_date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caretaker_id) REFERENCES caretakers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table caretaker_bookings created successfully.\n";
    
    // Check if we have any bookings
    $check = $conn->query("SELECT COUNT(*) as count FROM caretaker_bookings");
    $row = $check->fetch_assoc();
    
    if ($row['count'] == 0) {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $dayAfter = date('Y-m-d', strtotime('+2 days'));
        $nextWeek = date('Y-m-d', strtotime('+7 days'));

        $sample_sql = "INSERT INTO caretaker_bookings (caretaker_id, user_id, booking_date, status) VALUES 
            (1, 1, '$tomorrow', 'confirmed'),
            (1, 2, '$dayAfter', 'confirmed'),
            (2, 1, '$today', 'confirmed'),
            (2, 2, '$nextWeek', 'confirmed'),
            (3, 1, '$tomorrow', 'confirmed')";
            
        if ($conn->query($sample_sql) === TRUE) {
            echo "Sample bookings inserted successfully.\n";
        } else {
            echo "Error inserting sample bookings: " . $conn->error . "\n";
        }
    } else {
        echo "Bookings already exist.\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
?>
