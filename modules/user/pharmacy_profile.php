<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

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
    <title><?= htmlspecialchars($pharmacy['name'] ?? 'Pharmacy') ?> | Profile</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/medicine_order.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; color: #0f172a; }
        .main-content { padding: 40px; }
        .profile-container { max-width: 1200px; margin: 0 auto; }
        
        /* Glassmorphism Header */
        .page-top-nav { margin-bottom: 24px; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #64748b; font-weight: 600; text-decoration: none; padding: 10px 20px; background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: 0.3s; }
        .back-btn:hover { color: #3b82f6; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }

        .hero-section {
            position: relative;
            height: 480px;
            border-radius: 32px;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            padding: 50px;
            margin-bottom: 40px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
        }
        .hero-bg { position: absolute; top:0; left:0; width:100%; height:100%; object-fit: cover; z-index: 0; }
        .hero-overlay { position: absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(to top, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0.5) 50%, rgba(15,23,42,0.1) 100%); z-index: 1; }
        .hero-content { position: relative; z-index: 2; color: white; width: 100%; display: flex; justify-content: space-between; align-items: flex-end; }
        
        .hero-text h1 { font-size: 48px; font-weight: 800; letter-spacing: -1px; margin-bottom: 12px; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .p-badge { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px; display: inline-block; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); }
        .address { display: inline-flex; align-items: center; gap: 8px; font-weight: 500; font-size: 16px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 10px 20px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.2); }

        /* Stats Blocks */
        .stats-grid { display: flex; gap: 20px; z-index: 2; position: relative; }
        .stat-box { background: rgba(255,255,255,0.1); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; padding: 20px 25px; text-align: center; color: white; min-width: 140px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: 0.3s; }
        .stat-box:hover { transform: translateY(-5px); background: rgba(255,255,255,0.15); }
        .stat-box i { font-size: 28px; margin-bottom: 8px; display: block; }
        .stat-box .val { font-size: 20px; font-weight: 700; }
        .stat-box .lbl { font-size: 12px; opacity: 0.8; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }

        /* Content Layout */
        .content-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; }
        
        /* Left Column */
        .section-block { background: white; border-radius: 24px; padding: 40px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; }
        .section-title { font-size: 24px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: #0f172a; }
        .section-title i { color: #3b82f6; }
        
        .description-text { font-size: 16px; line-height: 1.8; color: #475569; }
        
        .profile-video { width: 100%; aspect-ratio: 16/9; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.1); background: #000; position: relative; }
        .profile-video video { width: 100%; height: 100%; object-fit: cover; }
        
        /* Reviews */
        .review-card { padding: 25px; border-radius: 20px; background: #f8fafc; margin-bottom: 20px; transition: 0.3s; border: 1px solid transparent; }
        .review-card:hover { background: white; border-color: #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transform: translateX(5px); }
        .review-header { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .r-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; }
        .r-info h4 { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
        .r-stars { color: #f59e0b; font-size: 14px; }
        .r-comment { color: #475569; line-height: 1.6; font-size: 15px; }

        /* Right CTA Column */
        .sticky-wrapper { position: sticky; top: 40px; }
        .cta-card { background: linear-gradient(145deg, #ffffff, #f8fafc); padding: 40px 30px; border-radius: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; text-align: center; }
        .cta-icon { width: 80px; height: 80px; background: #e0e7ff; color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; margin: 0 auto 20px; box-shadow: 0 10px 20px rgba(59,130,246,0.2); }
        .cta-card h3 { font-size: 22px; font-weight: 800; margin-bottom: 15px; color: #0f172a; }
        .cta-card p { font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 30px; }
        .btn-order { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; padding: 18px 30px; border-radius: 16px; width: 100%; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 25px rgba(59,130,246,0.3); display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; }
        .btn-order:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(59,130,246,0.4); }

        @media (max-width: 1024px) {
            .content-layout { grid-template-columns: 1fr; }
            .hero-section { height: auto; padding: 40px 30px; flex-direction: column; align-items: flex-start; }
            .hero-content { flex-direction: column; align-items: flex-start; gap: 30px; }
            .stats-grid { width: 100%; overflow-x: auto; padding-bottom: 10px; }
            .stat-box { flex: 1; }
        }
        @media (max-width: 768px) {
            .main-content { padding: 80px 20px 20px 20px; }
            .hero-text h1 { font-size: 32px; }
            .address { font-size: 14px; }
            .section-block { padding: 25px; }
        }
    </style>
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

<div class="main-content">
    <div class="profile-container">
        
        <div class="page-top-nav">
            <a href="medicine_orders.php" class="back-btn">
                <i class="ri-arrow-left-line"></i> Back to Pharmacies
            </a>
        </div>

        <!-- Modern Hero Section -->
        <div class="hero-section">
            <img src="<?= htmlspecialchars($pharmacy['image_url'] ?? '') ?>" alt="Pharmacy Cover" class="hero-bg" onerror="this.src='https://images.unsplash.com/photo-1585435557343-3b092031a831?w=1200'">
            <div class="hero-overlay"></div>
            
            <div class="hero-content">
                <div class="hero-text">
                    <span class="p-badge"><i class="ri-checkbox-circle-fill"></i> Verified Partner</span>
                    <h1><?= htmlspecialchars($pharmacy['name'] ?? 'Pharmacy') ?></h1>
                    <div class="address"><i class="ri-map-pin-2-fill" style="color:#60a5fa;"></i> <?= htmlspecialchars($pharmacy['address'] ?? '') ?></div>
                </div>

                <div class="stats-grid">
                    <div class="stat-box">
                        <i class="ri-star-smile-fill" style="color: #fbd38d;"></i>
                        <div class="val"><?= number_format($pharmacy['rating'] ?? 5.0, 1) ?></div>
                        <div class="lbl">Rating</div>
                    </div>
                    <div class="stat-box">
                        <i class="ri-timer-flash-fill" style="color: #6ee7b7;"></i>
                        <div class="val"><?= htmlspecialchars($pharmacy['delivery_time'] ?? '30 mins') ?></div>
                        <div class="lbl">Delivery</div>
                    </div>
                    <div class="stat-box">
                        <i class="ri-phone-fill" style="color: #93c5fd;"></i>
                        <div class="val"><?= htmlspecialchars($pharmacy['phone'] ?? '-') ?></div>
                        <div class="lbl">Contact</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-layout">
            <!-- Left Content Area -->
            <div class="left-col">
                <div class="section-block">
                    <h2 class="section-title"><i class="ri-information-fill"></i> About the Pharmacy</h2>
                    <p class="description-text">
                        <?= nl2br(htmlspecialchars($pharmacy['description'] ?? 'No description provided.')) ?>
                    </p>
                </div>

                <div class="section-block">
                    <h2 class="section-title"><i class="ri-play-circle-fill"></i> Virtual Tour</h2>
                    <div class="profile-video">
                        <video controls width="100%" poster="<?= htmlspecialchars($pharmacy['image_url'] ?? '') ?>">
                            <?php if (!empty($pharmacy['video_url'])): ?>
                            <source src="<?= htmlspecialchars($pharmacy['video_url']) ?>" type="video/mp4">
                            <?php endif; ?>
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>

                <div class="section-block">
                    <h2 class="section-title"><i class="ri-chat-smile-3-fill"></i> Customer Reviews</h2>
                    <div class="reviews-list">
                        <?php if(isset($reviews) && $reviews->num_rows > 0): ?>
                            <?php while($r = $reviews->fetch_assoc()): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="r-avatar"><?= strtoupper(substr($r['user_name'], 0, 1)) ?></div>
                                    <div class="r-info">
                                        <h4><?= htmlspecialchars($r['user_name']) ?></h4>
                                        <div class="r-stars">
                                            <?php for($i=0; $i<$r['rating']; $i++) echo '<i class="ri-star-fill"></i>'; ?>
                                        </div>
                                    </div>
                                </div>
                                <p class="r-comment"><?= htmlspecialchars($r['comment']) ?></p>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="text-align:center; padding: 20px; color:#94a3b8;"><i class="ri-message-3-line" style="font-size:32px; display:block; margin-bottom:10px;"></i>No reviews yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right CTA Area -->
            <div class="right-col">
                <div class="sticky-wrapper">
                    <div class="cta-card">
                        <div class="cta-icon">
                            <i class="ri-capsule-fill"></i>
                        </div>
                        <h3>Need Medicines?</h3>
                        <p>Upload your doctor's prescription safely and securely. We'll handle the rest and deliver it straight to your doorstep in no time!</p>
                        <a href="medicine_orders.php?upload=<?= $pharmacy['id'] ?? 0 ?>" class="btn-order">
                            <i class="ri-file-upload-line"></i> Upload Prescription
                        </a>
                        
                        <div style="margin-top: 25px; display: flex; align-items: center; justify-content: center; gap: 8px; color: #64748b; font-size: 13px;">
                            <i class="ri-shield-check-fill" style="color: #10b981; font-size: 16px;"></i>
                            100% Safe & Secure Order
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
