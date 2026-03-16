<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch pharmacy details
$stmt = $conn->prepare("SELECT * FROM pharmacies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$pharmacy = $stmt->get_result()->fetch_assoc();

if (!$pharmacy) {
    header("Location: medicine_orders.php");
    exit;
}

// Fetch reviews
$reviews_stmt = $conn->prepare("SELECT * FROM pharmacy_reviews WHERE pharmacy_id = ? ORDER BY created_at DESC");
$reviews_stmt->bind_param("i", $id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();

$current_page = 'medicine_orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pharmacy['name']) ?> | Profile</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/medicine_order.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-hero {
            background: #fff;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            border: 1px solid #f1f5f9;
        }
        .hero-banner {
            height: 300px;
            position: relative;
        }
        .hero-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .hero-info {
            padding: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
        }
        .info-main { flex: 1; }
        .info-main h1 { font-size: 32px; margin-bottom: 10px; color: #0f172a; }
        .info-main .address { color: #64748b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .profile-video {
            aspect-ratio: 16/9;
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 30px;
        }
        .review-section { margin-top: 50px; }
        .review-card {
            background: #fff;
            padding: 24px;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            margin-bottom: 20px;
        }
        .review-user { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .user-avatar { width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #3542f3; font-weight: 700; }
        .cta-box {
            background: #f8fbff;
            padding: 30px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            width: 300px;
            position: sticky;
            top: 30px;
        }
        .p-badge { background: #3542f3; color: #fff; padding: 4px 12px; border-radius: 10px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    </style>
</head>
<body>

<?php include '../../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="med-order-container">
        
        <div style="margin-bottom: 24px;">
            <a href="medicine_orders.php" style="color: #64748b; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="ri-arrow-left-line"></i> Back to Pharmacies
            </a>
        </div>

        <div class="profile-hero">
            <div class="hero-banner">
                <img src="<?= $pharmacy['image_url'] ?>" alt="<?= htmlspecialchars($pharmacy['name']) ?>">
            </div>
            <div class="hero-info">
                <div class="info-main">
                    <span class="p-badge">Verified Store</span>
                    <h1 style="margin-top: 15px;"><?= htmlspecialchars($pharmacy['name']) ?></h1>
                    <div class="address"><i class="ri-map-pin-2-line"></i> <?= htmlspecialchars($pharmacy['address']) ?></div>
                    
                    <p style="color: #475569; line-height: 1.8; margin-bottom: 30px;">
                        <?= htmlspecialchars($pharmacy['description']) ?>
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px;">
                        <div style="background: #f8fafc; padding: 20px; border-radius: 16px; text-align: center;">
                            <i class="ri-star-fill" style="color: #f59e0b; font-size: 24px;"></i>
                            <div style="font-weight: 700; margin-top: 5px;"><?= $pharmacy['rating'] ?> Rating</div>
                        </div>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 16px; text-align: center;">
                            <i class="ri-truck-line" style="color: #3b82f6; font-size: 24px;"></i>
                            <div style="font-weight: 700; margin-top: 5px;"><?= $pharmacy['delivery_time'] ?></div>
                        </div>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 16px; text-align: center;">
                            <i class="ri-phone-line" style="color: #10b981; font-size: 24px;"></i>
                            <div style="font-weight: 700; margin-top: 5px;"><?= $pharmacy['phone'] ?></div>
                        </div>
                    </div>

                    <h2 style="font-size: 22px; margin-bottom: 20px;">Virtual Pharmacy Tour</h2>
                    <div class="profile-video">
                        <video controls width="100%" poster="<?= $pharmacy['image_url'] ?>">
                            <source src="<?= $pharmacy['video_url'] ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>

                    <div class="review-section">
                        <h2 style="font-size: 22px; margin-bottom: 30px;">Customer Reviews</h2>
                        <?php while($r = $reviews->fetch_assoc()): ?>
                        <div class="review-card">
                            <div class="review-user">
                                <div class="user-avatar"><?= substr($r['user_name'], 0, 1) ?></div>
                                <div>
                                    <div style="font-weight: 700;"><?= htmlspecialchars($r['user_name']) ?></div>
                                    <div style="color: #f59e0b; font-size: 14px;">
                                        <?php for($i=0; $i<$r['rating']; $i++) echo '<i class="ri-star-fill"></i>'; ?>
                                    </div>
                                </div>
                            </div>
                            <p style="color: #475569;"><?= htmlspecialchars($r['comment']) ?></p>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="cta-area">
                    <div class="cta-box">
                        <h3 style="font-size: 18px; margin-bottom: 15px;">Order from this Store</h3>
                        <p style="font-size: 14px; color: #64748b; margin-bottom: 20px;">Upload your prescription and get medicines delivered at your doorstep.</p>
                        <button class="btn-primary" onclick="window.location.href='medicine_orders.php?upload=<?= $pharmacy['id'] ?>'">
                            Upload Prescription
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
