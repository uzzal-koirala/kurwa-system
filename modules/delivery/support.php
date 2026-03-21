<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['delivery_id'])) {
    header("Location: login.php");
    exit();
}
$current_page = 'support';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Support | Kurwa</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/delivery_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/delivery_dashboard.css">
    <style>
        .support-grid { display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
        .faq-item { margin-bottom: 20px; padding: 15px; border-radius: 12px; border: 1px solid #f1f5f9; cursor: pointer; transition: 0.3s; }
        .faq-item:hover { background: #f8fafc; border-color: var(--rider-primary); }
        .faq-title { font-size: 14px; font-weight: 700; color: var(--rider-secondary); margin-bottom: 5px; display: flex; align-items: center; justify-content: space-between; }
        .faq-content { font-size: 12px; color: var(--rider-text-muted); line-height: 1.6; }
        
        .contact-methods { display: flex; flex-direction: column; gap: 15px; }
        .contact-card { display: flex; align-items: center; gap: 15px; padding: 20px; border-radius: 20px; background: white; box-shadow: var(--rider-shadow); text-decoration: none; color: inherit; transition: 0.3s; }
        .contact-card:hover { transform: translateY(-3px); }
        .c-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    </style>
</head>
<body>

    <?php include "../../includes/components/delivery_sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="welcome-msg">
                <h1>Rider Support</h1>
                <p>We're here to help you, Suraj!</p>
            </div>
        </header>

        <div class="support-grid">
            <div class="rider-panel">
                <h4 class="panel-title">Frequently Asked Questions</h4>
                <div class="faq-item">
                    <div class="faq-title">How do I request a payout? <i class="ri-add-line"></i></div>
                    <div class="faq-content">Go to the Earnings page and click on "Request Early Payout". Standard payouts are processed every Monday.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-title">What if the customer is not reachable? <i class="ri-add-line"></i></div>
                    <div class="faq-content">Wait for at least 10 minutes at the location. Try calling them 3 times. If they still don't answer, contact support immediately via the call button.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-title">How is my rating calculated? <i class="ri-add-line"></i></div>
                    <div class="faq-content">Your rating is an average of the last 50 customer feedbacks. High ratings increase your chances of getting Priority Orders.</div>
                </div>
            </div>

            <div class="contact-methods">
                <a href="tel:+9779800000000" class="contact-card">
                    <div class="c-icon" style="background: #ecfdf5; color: #10b981;"><i class="ri-phone-fill"></i></div>
                    <div>
                        <h5 style="margin:0; font-size:14px; font-weight:700;">Emergency Call</h5>
                        <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Instant support during delivery</p>
                    </div>
                </a>
                <a href="https://wa.me/9779800000000" class="contact-card">
                    <div class="c-icon" style="background: #f0fdf4; color: #25d366;"><i class="ri-whatsapp-line"></i></div>
                    <div>
                        <h5 style="margin:0; font-size:14px; font-weight:700;">WhatsApp Support</h5>
                        <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Chat with our support team</p>
                    </div>
                </a>
                <div class="rider-panel" style="margin-top: 15px;">
                    <h5 style="margin:0 0 10px; font-size:14px; font-weight:700;">Shift Hours</h5>
                    <p style="font-size:12px; color:#64748b; line-height:1.6;">Your active shift: **09:00 AM - 09:00 PM**<br>Managed by: Kathmandu Hub</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
