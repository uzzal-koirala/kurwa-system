<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$caretaker_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($caretaker_id <= 0) {
    echo "Invalid Caretaker ID.";
    exit;
}

// Fetch caretaker data
$sql = "SELECT id, full_name, category, specialization, rating, experience_years, price_per_day, patients_helped, about_text, availability, working_hours, image_url FROM caretakers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $caretaker_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Caretaker not found.";
    exit;
}

$caretaker = $result->fetch_assoc();
$stmt->close();

$current_page = 'caretakers';

// Mock additional fields requested by the user but potentially not in DB yet
$status_options = ['Available', 'Busy', 'Active', 'Offline'];
$status = $status_options[array_rand($status_options)]; // Mocked random status for now or use algorithm based on availability
if (strtolower($caretaker['availability']) === 'available') {
    $status = 'Available';
}

$certifications = [
    "Certified Nursing Assistant (CNA)",
    "CPR & First Aid Certified",
    "Elderly Care Specialization Certificate"
];

$languages = ["English", "Nepali", "Hindi"];

$skills = [
    "Patient Mobility Assistance",
    "Medication Administration",
    "Vital Signs Monitoring",
    "Meal Preparation & Feeding",
    "Companionship & Emotional Support"
];

// Calendar logic for "Real" feel
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$monthName = date('F', mktime(0, 0, 0, $month, 10));
$todayDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($caretaker['full_name']) ?> - Profile | Kurwa</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/caretaker_profile.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../../includes/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <button class="mobile-menu-btn" id="openSidebar" type="button">
        <i class="ri-menu-line"></i>
    </button>

    <div class="profile-container">
        
        <!-- Back Navigation -->
        <a href="caretaker.php" class="back-link">
            <i class="ri-arrow-left-line"></i> Back to Caretakers
        </a>

        <div class="profile-grid">
            
            <!-- Left Column: Main Identity & Photo -->
            <div class="profile-left">
                <div class="identity-card glass-card">
                    <div class="photo-container">
                        <img src="<?= htmlspecialchars($caretaker['image_url'] ?: 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=80') ?>" alt="<?= htmlspecialchars($caretaker['full_name']) ?>">
                        <?php 
                        $status_class = 'status-offline';
                        if ($status === 'Available' || $status === 'Active') $status_class = 'status-available';
                        if ($status === 'Busy') $status_class = 'status-busy';
                        ?>
                        <div class="status-badge <?= $status_class ?>">
                            <span class="status-dot"></span> <?= $status ?>
                        </div>
                    </div>
                    
                    <div class="identity-info">
                        <h1><?= htmlspecialchars($caretaker['full_name']) ?> <i class="ri-verify-fill verified-icon" title="Verified Caretaker"></i></h1>
                        <p class="ct-id">Caretaker ID: #CT-<?= str_pad($caretaker['id'], 4, '0', STR_PAD_LEFT) ?></p>
                        <p class="specialization"><?= htmlspecialchars($caretaker['specialization']) ?></p>
                        
                        <div class="action-buttons">
                            <button class="btn-primary" id="bookNowBtn"><i class="ri-calendar-check-line"></i> Book Now</button>
                            <button class="btn-secondary"><i class="ri-message-3-line"></i> Message</button>
                        </div>
                    </div>
                </div>

                <!-- Contact & Working Hours -->
                <div class="info-card glass-card">
                    <h3><i class="ri-time-line"></i> Availability</h3>
                    <ul class="info-list">
                        <li>
                            <span class="label">Status:</span>
                            <span class="value"><?= htmlspecialchars($caretaker['availability']) ?></span>
                        </li>
                        <li>
                            <span class="label">Working Hours:</span>
                            <span class="value"><?= htmlspecialchars($caretaker['working_hours']) ?></span>
                        </li>
                        <li>
                            <span class="label">Rate:</span>
                            <span class="value price">Rs. <?= number_format($caretaker['price_per_day']) ?> / day</span>
                        </li>
                    </ul>
                </div>

                <!-- Certifications -->
                <div class="info-card glass-card">
                    <h3><i class="ri-award-fill"></i> Certifications</h3>
                    <ul class="bullet-list">
                        <?php foreach ($certifications as $cert): ?>
                            <li><i class="ri-checkbox-circle-fill"></i> <?= htmlspecialchars($cert) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>


                <!-- Languages -->
                <div class="info-card glass-card">
                    <h3><i class="ri-translate-2"></i> Languages</h3>
                    <div class="tags-container">
                        <?php foreach ($languages as $lang): ?>
                            <span class="tag"><?= htmlspecialchars($lang) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Details & Stats -->
            <div class="profile-right">
                
                <!-- Stats Row -->
                <div class="stats-row">
                    <div class="stat-card glass-card">
                        <div class="stat-icon"><i class="ri-star-smile-fill"></i></div>
                        <div class="stat-data">
                            <h4><?= htmlspecialchars($caretaker['rating']) ?></h4>
                            <p>Rating</p>
                        </div>
                    </div>
                    <div class="stat-card glass-card">
                        <div class="stat-icon"><i class="ri-briefcase-4-fill"></i></div>
                        <div class="stat-data">
                            <h4><?= htmlspecialchars($caretaker['experience_years']) ?> Yrs</h4>
                            <p>Experience</p>
                        </div>
                    </div>
                    <div class="stat-card glass-card">
                        <div class="stat-icon"><i class="ri-user-heart-fill"></i></div>
                        <div class="stat-data">
                            <h4><?= htmlspecialchars($caretaker['patients_helped']) ?>+</h4>
                            <p>Patients</p>
                        </div>
                    </div>
                </div>

                <!-- About Section -->
                <div class="details-card glass-card">
                    <h3>About <?= explode(' ', trim($caretaker['full_name']))[0] ?></h3>
                    <p class="about-text">
                        <?= nl2br(htmlspecialchars($caretaker['about_text'] ?: 'An experienced and compassionate caretaker dedicated to providing excellent patient care and support.')) ?>
                    </p>
                </div>

                <!-- Video Section -->
                <div class="details-card glass-card">
                    <h3><i class="ri-video-line"></i> Caretaker Introduction</h3>
                    <div class="video-container">
                        <?php 
                        // Mock YouTube ID extraction from a link (Real link would come from DB)
                        $youtube_url = "https://www.youtube.com/watch?v=dQw4w9WgXcQ"; 
                        $video_id = "";
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $match)) {
                            $video_id = $match[1];
                        }
                        ?>
                        <?php if ($video_id): ?>
                            <iframe 
                                src="https://www.youtube.com/embed/<?= $video_id ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                            </iframe>
                        <?php else: ?>
                            <div class="no-video">
                                <i class="ri-video-off-line"></i>
                                <p>No introduction video available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Skills Section -->
                <div class="details-card glass-card">
                    <h3><i class="ri-tools-fill"></i> Skills & Expertise</h3>
                    <div class="skills-container">
                        <?php foreach ($skills as $skill): ?>
                            <div class="skill-pill">
                                <?= htmlspecialchars($skill) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Availability Calendar -->
                <div class="details-card glass-card">
                    <h3><i class="ri-calendar-event-line"></i> Caretaker Availability Schedule</h3>
                    <div class="calendar-main-view">
                        <div class="calendar-info-row">
                            <div class="calendar-month-title">
                                <h4><?= $monthName ?> <?= $year ?></h4>
                                <p>Standard working days for selected period</p>
                            </div>
                            <div class="calendar-nav">
                                <a href="?id=<?= $caretaker_id ?>&month=<?= $prevMonth ?>&year=<?= $prevYear ?>#calendar" class="cal-nav-btn"><i class="ri-arrow-left-s-line"></i></a>
                                <a href="?id=<?= $caretaker_id ?>&month=<?= date('m') ?>&year=<?= date('Y') ?>#calendar" class="cal-nav-today">Today</a>
                                <a href="?id=<?= $caretaker_id ?>&month=<?= $nextMonth ?>&year=<?= $nextYear ?>#calendar" class="cal-nav-btn"><i class="ri-arrow-right-s-line"></i></a>
                            </div>
                            <div class="calendar-legend-box" id="calendar">
                                <div class="legend-item">
                                    <span class="l-dot available"></span>
                                    <span>Available</span>
                                </div>
                                <div class="legend-item">
                                    <span class="l-dot booked"></span>
                                    <span>Booked</span>
                                </div>
                            </div>
                        </div>

                        <div class="calendar-modern-grid">
                            <?php 
                            $days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
                            foreach($days as $day) echo "<div class='cal-header-day'>$day</div>";

                            $firstDayOfMonth = date('w', strtotime("$year-$month-01"));
                            $daysInMonth = date('t', strtotime("$year-$month-01"));
                            
                            // Fetch bookings for selected month
                            $booking_sql = "SELECT booking_date FROM caretaker_bookings WHERE caretaker_id = ? AND status = 'confirmed' AND MONTH(booking_date) = ? AND YEAR(booking_date) = ?";
                            $b_stmt = $conn->prepare($booking_sql);
                            $b_stmt->bind_param("iii", $caretaker_id, $month, $year);
                            $b_stmt->execute();
                            $b_res = $b_stmt->get_result();
                            $booked_dates = [];
                            while($brow = $b_res->fetch_assoc()) $booked_dates[] = $brow['booking_date'];
                            $b_stmt->close();

                            for($i = 0; $i < $firstDayOfMonth; $i++) echo "<div class='cal-day empty'></div>";
                            
                            for($day = 1; $day <= $daysInMonth; $day++) {
                                $currentDateStr = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                                $isBooked = in_array($currentDateStr, $booked_dates);
                                $isToday = ($currentDateStr === $todayDate);
                                
                                $class = $isBooked ? 'booked' : 'available';
                                if ($isToday) $class .= ' is-today';
                                
                                $booking_status_text = $isBooked ? 'Reserved' : 'Open';
                                echo "
                                <div class='cal-day $class' title='" . date('jS F, Y', strtotime($currentDateStr)) . "'>
                                    <span class='day-num'>$day</span>
                                    <span class='day-status'>$booking_status_text</span>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="details-card glass-card">
                    <h3><i class="ri-chat-smile-3-line"></i> Patient Reviews</h3>
                    <div class="reviews-container">
                        <?php 
                        // Fetch real reviews from database
                        $review_sql = "SELECT r.*, u.full_name as user_name, u.email 
                                      FROM caretaker_reviews r 
                                      JOIN users u ON r.user_id = u.id 
                                      WHERE r.caretaker_id = ? 
                                      ORDER BY r.created_at DESC";
                        $review_stmt = $conn->prepare($review_sql);
                        $review_stmt->bind_param("i", $caretaker_id);
                        $review_stmt->execute();
                        $reviews_result = $review_stmt->get_result();
                        
                        if ($reviews_result->num_rows > 0):
                            while ($review = $reviews_result->fetch_assoc()):
                                $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($review['user_name']) . '&background=random';
                                $review_date = date('M d, Y', strtotime($review['created_at']));
                        ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <img src="<?= $avatar_url ?>" alt="<?= htmlspecialchars($review['user_name']) ?>" class="review-avatar">
                                    <div class="review-meta">
                                        <h4><?= htmlspecialchars($review['user_name']) ?></h4>
                                        <span><?= $review_date ?></span>
                                    </div>
                                    <div class="review-stars">
                                        <?php for($i=0; $i<$review['rating']; $i++): ?>
                                            <i class="ri-star-fill"></i>
                                        <?php endfor; ?>
                                        <?php for($i=$review['rating']; $i<5; $i++): ?>
                                            <i class="ri-star-line"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="review-comment"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                            </div>
                        <?php 
                            endwhile;
                            $review_stmt->close();
                        else:
                        ?>
                            <div class="no-reviews">
                                <i class="ri-chat-voice-line"></i>
                                <p>No reviews yet for this caretaker.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    // Booking button interaction mock
    document.getElementById('bookNowBtn')?.addEventListener('click', () => {
        alert('Booking modal would open here to book <?= htmlspecialchars($caretaker['full_name']) ?>.');
    });
</script>
</body>
</html>
