<?php
require_once '../../includes/core/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['caretaker_id'])) {
    header("Location: login.php");
    exit;
}

$caretaker_id = $_SESSION['caretaker_id'];
$caretaker_name = $_SESSION['caretaker_name'] ?? 'Caretaker';
$current_page = 'earnings';

// Fetch caretaker details inkl. balance
$caretaker = $conn->query("SELECT * FROM caretakers WHERE id = $caretaker_id")->fetch_assoc();
$balance = $caretaker['balance'] ?? 0.00;

// Fetch lifetime earnings
$lifetime_query = $conn->query("SELECT SUM(total_price) as total FROM caretaker_bookings WHERE caretaker_id = $caretaker_id AND status = 'completed'");
$lifetime_earnings = $lifetime_query->fetch_assoc()['total'] ?? 0.00;

// Fetch transaction history
$transactions_query = $conn->prepare("
    SELECT * FROM transactions 
    WHERE caretaker_id = ? 
    ORDER BY created_at DESC 
    LIMIT 20
");
$transactions_query->bind_param("i", $caretaker_id);
$transactions_query->execute();
$transactions_result = $transactions_query->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earnings & Payouts | Caretaker</title>
    <link rel="stylesheet" href="../../assets/css/caretaker_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/caretaker_earnings.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="caretaker-body">

<?php include '../../includes/components/caretaker_sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    <div class="dashboard-header">
        <div class="header-left" style="display: flex; align-items: center; gap: 15px;">
            <i class="ri-menu-2-line mobile-toggle" id="openSidebarUniversal" style="font-size: 24px; color: #1b2559; cursor: pointer; display: none;"></i>
            <h1 class="page-main-title">Earnings & Payouts</h1>
        </div>
        <div class="header-right">
            <div class="header-icons">
                <i class="ri-notification-3-line"></i>
                <span class="notification-badge"></span>
            </div>
        </div>
    </div>

    <div class="earnings-grid">
        <div class="earnings-left">
            <!-- Balance Card -->
            <div class="balance-card-premium">
                <div class="card-glass-overlay"></div>
                <div class="card-header-row">
                    <div class="chip"></div>
                    <span class="brand">KURWA PAY</span>
                </div>
                <div class="balance-display">
                    <span class="label">Available for Withdrawal</span>
                    <h2 class="amount">Rs. <?= number_format($balance, 2) ?></h2>
                </div>
                <div class="card-footer-row">
                    <div class="caretaker-id">ID: <?= str_pad($caretaker_id, 6, '0', STR_PAD_LEFT) ?></div>
                    <div class="lifetime">
                        <span class="label">Lifetime Earnings</span>
                        <span class="val">Rs. <?= number_format($lifetime_earnings, 0) ?></span>
                    </div>
                </div>
            </div>

            <!-- Stats Mini Cards -->
            <div class="stats-mini-grid">
                <div class="mini-card">
                    <div class="icon" style="background: #eef2ff; color: #4361ee;"><i class="ri-arrow-up-circle-line"></i></div>
                    <div class="info">
                        <span class="label">Last Payout</span>
                        <span class="val">Rs. 0.00</span>
                    </div>
                </div>
                <div class="mini-card">
                    <div class="icon" style="background: #f0fdf4; color: #22c55e;"><i class="ri-hand-coin-line"></i></div>
                    <div class="info">
                        <span class="label">Service Fee</span>
                        <span class="val">0%</span>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="content-card history-card">
                <div class="card-header">
                    <h3>Recent Transactions</h3>
                    <a href="#" class="view-all">View All</a>
                </div>
                <div class="transaction-list">
                    <?php if ($transactions_result->num_rows > 0): ?>
                        <?php while($tx = $transactions_result->fetch_assoc()): ?>
                            <div class="tx-item">
                                <div class="tx-icon <?= $tx['type'] ?>">
                                    <i class="<?= $tx['type'] === 'withdrawal' ? 'ri-arrow-up-line' : 'ri-arrow-down-line' ?>"></i>
                                </div>
                                <div class="tx-info">
                                    <h4><?= ucfirst($tx['type']) ?></h4>
                                    <p><?= htmlspecialchars($tx['description']) ?></p>
                                    <span class="time"><?= date('d M, h:i A', strtotime($tx['created_at'])) ?></span>
                                </div>
                                <div class="tx-amount <?= $tx['type'] === 'withdrawal' ? 'neg' : 'pos' ?>">
                                    <?= $tx['type'] === 'withdrawal' ? '-' : '+' ?> Rs. <?= number_format(abs($tx['amount']), 2) ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="ri-history-line"></i>
                            <p>No transactions yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="earnings-right">
            <!-- Withdrawal Form -->
            <div class="content-card withdraw-card">
                <h3>Quick Withdrawal</h3>
                <p class="subtitle">Get your earnings instantly to your preferred wallet.</p>
                
                <form id="withdrawForm" action="handlers/withdraw.php" method="POST">
                    <div class="form-group">
                        <label>Withdraw Amount</label>
                        <div class="amount-input">
                            <span class="currency">Rs.</span>
                            <input type="number" name="amount" id="withdrawAmount" placeholder="0.00" min="500" max="<?= $balance ?>" required>
                        </div>
                        <span class="hint">Min: Rs. 500 | Max: Rs. <?= number_format($balance, 0) ?></span>
                    </div>

                    <div class="form-group">
                        <label>Withdraw To</label>
                        <div class="method-selector">
                            <label class="method-item active">
                                <input type="radio" name="method" value="esewa" checked>
                                <img src="../../assets/images/esewa.png" alt="eSewa">
                                <span>eSewa Wallet</span>
                            </label>
                            <label class="method-item">
                                <input type="radio" name="method" value="bank">
                                <i class="ri-bank-line"></i>
                                <span>Bank Transfer</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="walletFormGroup">
                        <label id="walletLabelText">eSewa ID / Phone</label>
                        <div class="wallet-input-container">
                            <i class="ri-smartphone-line" id="walletIcon"></i>
                            <input type="text" name="target" id="walletInput" placeholder="98XXXXXXXX" required>
                        </div>
                    </div>

                    <button type="submit" class="withdraw-submit-btn" <?= $balance < 500 ? 'disabled' : '' ?>>
                        <i class="ri-bank-card-line"></i> Withdraw Now
                    </button>
                    
                    <?php if($balance < 500): ?>
                        <p class="warning-text"><i class="ri-information-line"></i> Minimum balance of Rs. 500 required.</p>
                    <?php endif; ?>
                </form>
            </div>

            <div class="content-card help-card">
                <h4>Need help?</h4>
                <p>Learn more about how our payout system works and when to expect your funds.</p>
                <a href="#" class="help-link">Payout Policy <i class="ri-arrow-right-s-line"></i></a>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.method-item').forEach(item => {
        item.addEventListener('click', () => {
            document.querySelectorAll('.method-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            
            const method = item.querySelector('input').value;
            const walletLabel = document.querySelector('#walletLabelText');
            const walletInput = document.querySelector('#walletInput');
            const walletIcon = document.querySelector('#walletIcon');
            
            if (method === 'esewa') {
                walletLabel.innerText = 'eSewa ID / Phone';
                walletInput.placeholder = '98XXXXXXXX';
                if (walletIcon) walletIcon.className = 'ri-smartphone-line';
            } else {
                walletLabel.innerText = 'Bank Account Number';
                walletInput.placeholder = 'Account Number (Last 4 digits for privacy)';
                if (walletIcon) walletIcon.className = 'ri-bank-card-line';
            }
        });
    });

    // Check for success/error messages in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        alert('Withdrawal request submitted successfully!');
    } else if (urlParams.has('error')) {
        alert('Error: ' + urlParams.get('error'));
    }
</script>
<script src="../../assets/js/sidebar.js"></script>
</body>
</html>
