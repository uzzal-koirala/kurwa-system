<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$caretaker_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($caretaker_id <= 0) {
    echo "Invalid Caretaker ID.";
    exit;
}

// Fetch caretaker data
$sql = "SELECT id, full_name, phone_number, category, specialization, hospital_name, rating, experience_years, price_per_day, patients_helped, about_text, availability, working_hours, image_url, video_url FROM caretakers WHERE id = ?";
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

// Resolve Image Path
$display_img = $caretaker['image_url'];
if ($display_img && !str_starts_with($display_img, 'http')) {
    $display_img = '../../' . $display_img;
} else if (!$display_img) {
    $display_img = 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=80';
}

$status_options = ['Available', 'Busy', 'Active', 'Offline'];
$status = $status_options[array_rand($status_options)];
if (strtolower($caretaker['availability'] ?? '') === 'available') {
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

        /* Video styling */
        .video-container {
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 15px;
        }
        .video-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .no-video {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #64748b;
            background: #f8fafc;
            border: 1px dashed #e2e8f0;
            border-radius: 20px;
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
                        <img src="<?= htmlspecialchars($display_img) ?>" alt="<?= htmlspecialchars($caretaker['full_name']) ?>">
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
                            <span class="value"><?= htmlspecialchars($caretaker['availability'] ?: 'Standard') ?></span>
                        </li>
                        <li>
                            <span class="label">Working Hours:</span>
                            <span class="value"><?= htmlspecialchars($caretaker['working_hours'] ?: 'Flexible') ?></span>
                        </li>
                        <li>
                            <span class="label">Available At:</span>
                            <span class="value"><?= htmlspecialchars($caretaker['hospital_name'] ?: 'Local Center') ?></span>
                        </li>
                        <li>
                            <span class="label">Rate:</span>
                            <span class="value price">Rs. <?= number_format($caretaker['price_per_day']) ?> / day</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right Column: Details & Stats -->
            <div class="profile-right">
                
                <!-- Stats Row -->
                <div class="stats-row" style="margin-bottom: 24px;">
                    <div class="stat-card glass-card" style="padding: 16px 20px !important;">
                        <div class="stat-icon" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);"><i class="ri-star-smile-fill"></i></div>
                        <div class="stat-data">
                            <h4><?= htmlspecialchars($caretaker['rating']) ?></h4>
                            <p>Rating</p>
                        </div>
                    </div>
                    <div class="stat-card glass-card" style="padding: 16px 20px !important;">
                        <div class="stat-icon" style="color: #3b82f6; background: rgba(59, 130, 246, 0.1);"><i class="ri-briefcase-4-fill"></i></div>
                        <div class="stat-data">
                            <h4><?= htmlspecialchars($caretaker['experience_years']) ?> Yrs</h4>
                            <p>Experience</p>
                        </div>
                    </div>
                    <div class="stat-card glass-card" style="padding: 16px 20px !important;">
                        <div class="stat-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.1);"><i class="ri-user-heart-fill"></i></div>
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
                        $video_url = $caretaker['video_url'];
                        $video_id = "";
                        if ($video_url && preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $match)) {
                            $video_id = $match[1];
                        }
                        ?>
                        <?php if ($video_id): ?>
                            <iframe src="https://www.youtube.com/embed/<?= $video_id ?>" frameborder="0" allowfullscreen></iframe>
                        <?php elseif ($video_url): ?>
                            <!-- Local or other video -->
                            <video src="<?= htmlspecialchars($video_url) ?>" controls style="width: 100%; height: 100%;"></video>
                        <?php else: ?>
                            <div class="no-video"><i class="ri-video-off-line" style="font-size: 40px;"></i><p>No video available.</p></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Skills Section -->
                <div class="details-card glass-card">
                    <h3><i class="ri-tools-fill"></i> Skills & Expertise</h3>
                    <div class="skills-container" style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($skills as $skill): ?>
                            <div class="skill-pill" style="background: rgba(47, 60, 255, 0.1); color: #2F3CFF; padding: 6px 12px; border-radius: 10px; font-size: 13px; font-weight: 500;"><?= htmlspecialchars($skill) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeBookingModal">&times;</span>
        <h2 style="margin-top:0; color:#0f172a; display:flex; align-items:center; gap:10px;">
            <i class="ri-calendar-check-fill" style="color:#2F3CFF;"></i> Book Caretaker
        </h2>
        <p style="color:#64748b; font-size:14px; margin-bottom:20px;">Secure your dates with <?= htmlspecialchars($caretaker['full_name']) ?>. Price is Rs. <?= number_format($caretaker['price_per_day']) ?> per day.</p>
        
        <form id="bookingForm" class="booking-form">
            <input type="hidden" name="caretaker_id" value="<?= $caretaker_id ?>">
            <div class="input-group">
                <label for="startDate">Start Date</label>
                <input type="text" id="startDate" name="start_date" placeholder="Select start date" required>
            </div>
            <div class="input-group">
                <label for="endDate">End Date (Inclusive)</label>
                <input type="text" id="endDate" name="end_date" placeholder="Select end date" required>
            </div>
            
            <div class="price-summary" id="priceSummary" style="display:none;">
                <div class="price-row">
                    <span>Daily Rate:</span>
                    <span>Rs. <?= number_format($caretaker['price_per_day']) ?></span>
                </div>
                <div class="price-row">
                    <span>Total Days:</span>
                    <span id="numDays">0</span>
                </div>
                <div class="price-row total">
                    <span>Total Amount:</span>
                    <span id="totalPrice">Rs. 0</span>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; padding:15px; font-weight:700; font-size:16px;">
                <i class="ri-checkbox-circle-line"></i> Confirm Booking
            </button>
        </form>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('bookingModal');
        const bookBtn = document.getElementById('bookNowBtn');
        const closeBtn = document.getElementById('closeBookingModal');
        const bookingForm = document.getElementById('bookingForm');
        const dailyRate = <?= $caretaker['price_per_day'] ?>;
        
        // Initialize Flatpickr
        const startPicker = flatpickr("#startDate", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr) {
                endPicker.set('minDate', dateStr);
                calculateTotal();
            }
        });

        const endPicker = flatpickr("#endDate", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function() {
                calculateTotal();
            }
        });

        function calculateTotal() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            const summary = document.getElementById('priceSummary');

            if (start && end) {
                const d1 = new Date(start);
                const d2 = new Date(end);
                const diffTime = Math.abs(d2 - d1);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                document.getElementById('numDays').innerText = diffDays;
                document.getElementById('totalPrice').innerText = "Rs. " + (diffDays * dailyRate).toLocaleString();
                summary.style.display = 'block';
            } else {
                summary.style.display = 'none';
            }
        }

        // Modal Controls
        bookBtn.onclick = () => modal.style.display = 'flex';
        closeBtn.onclick = () => modal.style.display = 'none';
        window.onclick = (event) => {
            if (event.target == modal) modal.style.display = 'none';
        }

        // Submit Booking
        bookingForm.onsubmit = function(e) {
            e.preventDefault();
            const submitBtn = bookingForm.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Processing...';

            const formData = new FormData(bookingForm);
            
            fetch('handlers/process_booking.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            })
            .catch(err => {
                console.error('Booking error:', err);
                alert("An error occurred. Please try again.");
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            });
        };
    });
</script>
</body>
</html>
