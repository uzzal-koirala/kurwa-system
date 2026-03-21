<?php
require_once __DIR__ . '/../includes/config.php';

// SQL to create caretaker_reviews table
$sql = "CREATE TABLE IF NOT EXISTS caretaker_reviews (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    caretaker_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    rating TINYINT(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caretaker_id) REFERENCES caretakers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table caretaker_reviews created successfully.\n";
    
    // Check if we have any reviews to avoid duplicates in this script
    $check = $conn->query("SELECT COUNT(*) as count FROM caretaker_reviews");
    $row = $check->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Sample data for reviews (Assuming IDs 1-3 exist for both)
        $sample_sql = "INSERT INTO caretaker_reviews (caretaker_id, user_id, rating, comment) VALUES 
            (1, 1, 5, 'Exceptional care! My father was very comfortable and the caretaker was extremely professional.'),
            (1, 2, 4, 'Very good experience. Punctual and knowledgeable.'),
            (2, 1, 5, 'Highly recommend for elderly care. Very patient and kind.'),
            (3, 2, 5, 'Great support after my surgery. Helped me recover much faster.')";
            
        if ($conn->query($sample_sql) === TRUE) {
            echo "Sample reviews inserted successfully.\n";
        } else {
            echo "Error inserting sample reviews: " . $conn->error . "\n";
        }
    } else {
        echo "Sample reviews already exist.\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
?>
