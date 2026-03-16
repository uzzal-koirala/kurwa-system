<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
$current_page = 'caretakers';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hire Caretaker - Kurwa</title>

    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/caretaker.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<?php include '../../includes/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <button class="mobile-menu-btn" id="openSidebar" type="button">
        <i class="ri-menu-line"></i>
    </button>

    <div class="caretaker-page">
        <div class="caretaker-main">
            <!-- top welcome -->
            <div class="welcome-card">
                <div>
                    <h1>Find a Trusted Caretaker</h1>
                    <p>Choose the right caretaker for patient support, daily assistance, and hospital help.</p>
                </div>

                <div class="date-badge">
                    <i class="ri-calendar-line"></i>
                    <span>Available Today</span>
                </div>
            </div>

            <!-- search bar row -->
            <div class="search-container">
                <div class="search-box">
                    <i class="ri-search-line"></i>
                    <input type="text" id="caretakerSearch" placeholder="Search by name or specialty...">
                </div>
            </div>

            <!-- category filters -->
            <div class="category-row">
                <button class="category-card active" data-category="All">
                    <i class="ri-grid-fill"></i>
                    <span>All</span>
                </button>
                <button class="category-card" data-category="General Care">
                    <i class="ri-user-heart-line"></i>
                    <span>General Care</span>
                </button>
                <button class="category-card" data-category="Elderly Care">
                    <i class="ri-wheelchair-line"></i>
                    <span>Elderly Care</span>
                </button>
                <button class="category-card" data-category="Post-Surgery">
                    <i class="ri-heart-pulse-line"></i>
                    <span>Post-Surgery</span>
                </button>
                <button class="category-card" data-category="Special Needs">
                    <i class="ri-mental-health-line"></i>
                    <span>Special Needs</span>
                </button>

            </div>

            <!-- section header -->
            <div class="section-head">
                <h2>Recommended Caretakers</h2>
            </div>

            <!-- caretaker cards injected by JS -->
            <div class="caretaker-grid">
                 <p style="text-align:center; grid-column: 1/-1;">Loading...</p>
            </div>
    </div>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="booking-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Book Caretaker</h2>
            <button class="close-modal" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <p class="modal-subtitle">Complete your booking for <strong id="modalCaretakerName"></strong></p>
            <form id="bookingForm" class="booking-form">
                <div class="input-group">
                    <label>Your Name</label>
                    <input type="text" required placeholder="Enter your full name">
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="tel" required placeholder="Enter your contact number">
                </div>
                <div class="input-group">
                    <label>Start Date</label>
                    <input type="date" required>
                </div>
                <div class="input-group">
                    <label>Additional Notes</label>
                    <textarea placeholder="Any specific requirements..."></textarea>
                </div>
                <button type="submit" class="confirm-btn">Confirm Booking</button>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script src="../../assets/js/caretaker.js"></script>
</body>
</html>