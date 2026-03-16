<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

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

// Mock additional fields
$status_options = ['Available', 'Busy', 'Active', 'Offline'];
$status = $status_options[array_rand($status_options)];
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
    <!-- Flatpickr for better date selection -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            width: 90%;
            max-width: 500px;
            padding: 30px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalSlide 0.3s ease-out;
        }

        @keyframes modalSlide {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #64748b;
        }

        /* Booking Modal Styles */
        .booking-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .input-group label {
            font-weight: 500;
            color: #1e293b;
            font-size: 14px;
        }

        .input-group input {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
        }

        .price-summary {
            background: #f8fafc;
            padding: 20px;
            border-radius: 16px;
            margin-top: 10px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: #64748b;
        }

        .price-row.total {
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: 700;
            color: #1e293b;
            font-size: 18px;
        }

        /* Chat Modal Styles */
        .chat-container {
            height: 400px;
            display: flex;
            flex-direction: column;
            margin-top: 20px;
        }

        #chatMessages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            background: #f8fafc;
            margin-bottom: 15px;
        }

        .msg {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            font-size: 14px;
            line-height: 1.4;
        }

        .msg.sent {
            align-self: flex-end;
            background: #2F3CFF;
            color: white;
            border-bottom-right-radius: 2px;
        }

        .msg.received {
            align-self: flex-start;
            background: white;
            color: #1e293b;
            border-bottom-left-radius: 2px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .chat-input-row {
            display: flex;
            gap: 10px;
        }

        .chat-input-row input {
            flex-grow: 1;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .chat-send-btn {
            background: #2F3CFF;
            color: white;
            border: none;
            width: 45px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

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
                            <a href="chat.php?caretaker_id=<?= $caretaker_id ?>" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="ri-message-3-line"></i> Message
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Availability & Info -->
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

<?php
// Fetch booked dates for this caretaker to disable them in Flatpickr
$booked_dates = [];
$booking_sql = "SELECT start_date, end_date FROM caretaker_bookings WHERE caretaker_id = ? AND status IN ('pending', 'confirmed')";
$b_stmt = $conn->prepare($booking_sql);
if ($b_stmt) {
    $b_stmt->bind_param("i", $caretaker_id);
    $b_stmt->execute();
    $b_res = $b_stmt->get_result();
    while ($row = $b_res->fetch_assoc()) {
        $period = new DatePeriod(
            new DateTime($row['start_date']),
            new DateInterval('P1D'),
            (new DateTime($row['end_date']))->modify('+1 day')
        );
        foreach ($period as $date) {
            $booked_dates[] = $date->format("Y-m-d");
        }
    }
    $b_stmt->close();
} else {
    error_log("MySQL Prepare Error (Booking): " . $conn->error);
    // Optional: display error for debugging if requested, but better to keep it in log for production
    // echo "<!-- DB Error: " . htmlspecialchars($conn->error) . " -->";
}
?>
            
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
                        $youtube_url = "https://www.youtube.com/watch?v=dQw4w9WgXcQ"; 
                        $video_id = "";
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $match)) {
                            $video_id = $match[1];
                        }
                        ?>
                        <?php if ($video_id): ?>
                            <iframe src="https://www.youtube.com/embed/<?= $video_id ?>" frameborder="0" allowfullscreen></iframe>
                        <?php else: ?>
                            <div class="no-video"><i class="ri-video-off-line"></i><p>No video available.</p></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Skills Section -->
                <div class="details-card glass-card">
                    <h3><i class="ri-tools-fill"></i> Skills & Expertise</h3>
                    <div class="skills-container">
                        <?php foreach ($skills as $skill): ?>
                            <div class="skill-pill"><?= htmlspecialchars($skill) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="details-card glass-card">
                    <h3><i class="ri-chat-smile-3-line"></i> Patient Reviews</h3>
                    <div class="reviews-container">
                        <?php 
                        $review_sql = "SELECT r.*, u.full_name as user_name FROM caretaker_reviews r JOIN users u ON r.user_id = u.id WHERE r.caretaker_id = ? ORDER BY r.created_at DESC";
                        $r_stmt = $conn->prepare($review_sql);
                        if ($r_stmt) {
                            $r_stmt->bind_param("i", $caretaker_id);
                            $r_stmt->execute();
                            $rev_res = $r_stmt->get_result();
                            if ($rev_res->num_rows > 0) {
                                while($rev = $rev_res->fetch_assoc()) {
                                    $rev_date = date('M d, Y', strtotime($rev['created_at']));
                                    echo "<div class='review-item'>
                                            <div class='review-header'>
                                                <div class='user-info'>
                                                    <img src='https://ui-avatars.com/api/?name=".urlencode($rev['user_name'] ?? 'User')."&background=random&color=fff&bold=true'>
                                                    <div>
                                                        <h5>".htmlspecialchars($rev['user_name'] ?? 'Anonymous')."</h5>
                                                        <div class='stars'>";
                                    for($i=1; $i<=5; $i++) echo "<i class='ri-star-".($i<=($rev['rating'] ?? 0) ? 'fill' : 'line')."'></i>";
                                    echo "</div>
                                                    </div>
                                                </div>
                                                <span class='review-date'>$rev_date</span>
                                            </div>
                                            <p class='review-text'>".nl2br(htmlspecialchars($rev['comment'] ?? ''))."</p>
                                          </div>";
                                }
                            } else { echo "<div class='glass-card' style='text-align:center; padding: 40px; color: #64748b;'><i class='ri-chat-voice-line' style='font-size: 40px; display: block; margin-bottom: 10px;'></i>No reviews yet. Be the first to book!</div>"; }
                        } else { echo "<p class='no-reviews'>Reviews unavailable.</p>"; }
                        ?>
                    </div>
                </div>
            </div> <!-- End Profile Right -->
        </div>
    </div>
</div>

<!-- Premium Booking Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content" style="max-width: 520px; padding: 0; overflow: hidden;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #3542f3 0%, #7c3aed 100%); padding: 30px; text-align: center; position: relative;">
            <button onclick="closeModal('bookingModal')" style="position: absolute; top: 15px; right: 18px; background: rgba(255,255,255,0.2); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);">&times;</button>
            <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.15); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; backdrop-filter: blur(5px);">
                <i class="ri-calendar-check-line" style="font-size: 30px; color: #fff;"></i>
            </div>
            <h2 style="color: #fff; font-size: 22px; margin: 0 0 5px;">Book <?= htmlspecialchars(explode(' ', $caretaker['full_name'])[0]) ?></h2>
            <p style="color: rgba(255,255,255,0.75); font-size: 13px; margin: 0;">Select your dates and confirm booking</p>
        </div>

        <!-- Modal Body -->
        <div style="padding: 28px;">
            <div class="booking-form">
                <div class="input-group">
                    <label><i class="ri-calendar-line" style="color: #3542f3;"></i> &nbsp;Select Date Range</label>
                    <input type="text" id="dateRangePicker" placeholder="Pick start & end dates..." style="cursor: pointer;">
                </div>

                <!-- Price Summary Card -->
                <div class="price-summary" style="border-radius: 18px; padding: 20px;">
                    <div style="font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">Booking Summary</div>
                    <div class="price-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 14px;">
                        <span style="color: #64748b; display: flex; align-items: center; gap: 6px;"><i class="ri-money-dollar-circle-line"></i> Daily Rate</span>
                        <span style="font-weight: 600; color: #1e293b;">Rs. <?= number_format($caretaker['price_per_day']) ?></span>
                    </div>
                    <div class="price-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 14px;">
                        <span style="color: #64748b; display: flex; align-items: center; gap: 6px;"><i class="ri-calendar-2-line"></i> Duration</span>
                        <span style="font-weight: 600; color: #1e293b;" id="durationText">Select dates</span>
                    </div>
                    <div style="height: 1px; background: #e2e8f0; margin: 14px 0;"></div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 700; color: #0f172a; font-size: 15px;">Total Amount</span>
                        <span style="font-weight: 800; color: #3542f3; font-size: 22px;" id="totalAmountText">Rs. 0</span>
                    </div>
                </div>

                <button class="btn-primary" style="width: 100%; padding: 16px; font-size: 16px; border-radius: 16px; gap: 10px;" onclick="confirmBooking()" id="confirmBtn" disabled>
                    <i class="ri-secure-payment-line"></i> Confirm & Pay Now
                </button>
                <p style="text-align: center; font-size: 12px; color: #94a3b8; margin-top: 10px;"><i class="ri-shield-check-line"></i> Secure booking. Amount deducted from your wallet.</p>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    const caretakerId = <?= $caretaker_id ?>;
    const dailyRate = <?= $caretaker['price_per_day'] ?>;
    const bookedDates = <?= json_encode($booked_dates) ?>;

    let selectedStart = null;
    let selectedEnd = null;

    // Initialize Flatpickr
    const fp = flatpickr("#dateRangePicker", {
        mode: "range",
        minDate: "today",
        dateFormat: "Y-m-d",
        disable: bookedDates,
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                selectedStart = selectedDates[0];
                selectedEnd = selectedDates[1];
                
                const diffTime = Math.abs(selectedEnd - selectedStart);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                document.getElementById('durationText').innerText = diffDays + (diffDays > 1 ? ' Days' : ' Day');
                document.getElementById('totalAmountText').innerText = 'Rs. ' + (diffDays * dailyRate).toLocaleString();
                document.getElementById('confirmBtn').disabled = false;
            } else {
                document.getElementById('durationText').innerText = '0 Days';
                document.getElementById('totalAmountText').innerText = 'Rs. 0';
                document.getElementById('confirmBtn').disabled = true;
                selectedStart = null;
                selectedEnd = null;
            }
        }
    });

    document.getElementById('bookNowBtn').onclick = () => document.getElementById('bookingModal').style.display = 'flex';
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    window.onclick = (e) => { if (e.target.className === 'modal') e.target.style.display = 'none'; }

    async function confirmBooking() {
        if (!selectedStart || !selectedEnd) return;
        
        const btn = document.getElementById('confirmBtn');
        btn.disabled = true; btn.innerText = 'Processing...';

        const formatDate = (date) => date.toISOString().split('T')[0];

        const formData = new FormData();
        formData.append('caretaker_id', caretakerId);
        formData.append('start_date', formatDate(selectedStart));
        formData.append('end_date', formatDate(selectedEnd));

        try {
            const res = await fetch('handlers/process_booking.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) { alert(data.message); location.reload(); }
            else { alert(data.message); }
        } catch (e) { alert('Error occurred.'); }
        finally { btn.disabled = false; btn.innerText = 'Confirm & Pay Now'; }
    }
</script>
</body>
</html>
