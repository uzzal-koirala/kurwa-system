<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$current_page = 'payments';
$user_id = $_SESSION['user_id'];

// Fetch current user details
$user_data = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$user_balance = $user_data['balance'] ?? 0.00;
$user_name = $user_data['full_name'] ?? "User";

// Fetch real transaction history
$transactions = [];
$transactions_sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$tx_stmt = $conn->prepare($transactions_sql);

if ($tx_stmt) {
    $tx_stmt->bind_param("i", $user_id);
    $tx_stmt->execute();
    $transactions_res = $tx_stmt->get_result();
    while ($row = $transactions_res->fetch_assoc()) {
        $transactions[] = $row;
    }
} else {
    // If table doesn't exist, we'll show an empty history instead of crashing
    // This often happens before the user runs setup_coupons.php
    error_log("Transactions table might be missing: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments & Topup | Kurwa System</title>
    
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/payments.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../../includes/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <header class="dashboard-header">
        <div class="header-left">
            <h1>Payments & Topup</h1>
            <p>Manage your wallet balance and view transaction history</p>
        </div>
        <div class="header-right">
            <div class="user-display">
                <div class="info">
                    <h4><?= htmlspecialchars($user_name) ?></h4>
                    <p>Wallet Verified</p>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=3b82f6&color=fff" alt="User">
            </div>
        </div>
    </header>

    <div class="payments-container">
        <!-- Top Section: Balance & Topup Form -->
        <div class="payment-hero">
            <div class="payment-left">
                <!-- Coupon Section (First Row) -->
                <div class="coupon-section">
                    <h3><i class="ri-ticket-2-line" style="color: #2F3CFF;"></i> Have a Coupon Code?</h3>
                    <div class="coupon-input-group">
                        <input type="text" id="couponCode" placeholder="Enter code (e.g. WELCOME100)">
                        <button class="redeem-btn" onclick="redeemCoupon()">Redeem</button>
                    </div>
                    <p id="couponMessage" style="font-size: 12px; margin-top: 10px; display: none;"></p>
                </div>

                <!-- Balance Card (Second Row) -->
                <div class="balance-card-large">
                    <div class="balance-header">
                        <div class="chip-icon"></div>
                        <div class="brand-logo">KURWA PAY</div>
                    </div>
                    <div class="balance-body">
                        <span class="label">Available Balance</span>
                        <h2 class="amount">Rs. <?= number_format($user_balance, 2) ?></h2>
                    </div>
                    <div class="balance-footer">
                        <div class="card-number">.... .... .... <?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?></div>
                        <i class="ri-visa-line" style="font-size: 32px; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>

            <!-- Top Up Form Card (Right Column) -->
            <div class="topup-card">
                <h2>Quick Top Up</h2>
                <div class="amount-selector">
                    <div class="amount-preset" onclick="setAmount(500)">Rs. 500</div>
                    <div class="amount-preset active" onclick="setAmount(1000)">Rs. 1,000</div>
                    <div class="amount-preset" onclick="setAmount(5000)">Rs. 5,000</div>
                    <div class="amount-preset" onclick="setAmount(10000)">Rs. 10,000</div>
                </div>

                <div class="custom-amount">
                    <label>Or Enter Custom Amount</label>
                    <div class="input-wrapper">
                        <span>Rs.</span>
                        <input type="number" id="topupAmount" value="1000" min="100">
                    </div>
                </div>

                <span class="method-label">Select Payment Method</span>
                <div class="payment-methods">
                    <div class="method-item active" onclick="setMethod(this, 'esewa')">
                        <img src="../../assets/images/esewa.png" alt="eSewa">
                        <span>eSewa</span>
                    </div>
                    <div class="method-item" onclick="setMethod(this, 'khalti')">
                        <img src="../../assets/images/khalti.png" alt="Khalti">
                        <span>Khalti</span>
                    </div>
                    <div class="method-item" onclick="setMethod(this, 'iphay')">
                         <i class="ri-bank-card-line" style="font-size: 24px; color: #2F3CFF;"></i>
                        <span>Card/Bank</span>
                    </div>
                </div>

                <button class="topup-submit-btn" onclick="processTopUp()">
                    <i class="ri-flashlight-line"></i> Top Up Wallet Now
                </button>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="history-section">
            <div class="history-header">
                <h2>Transaction History</h2>
                <div class="filter-btn">
                    <i class="ri-filter-3-line"></i> Filter History
                </div>
            </div>

            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Transaction Detail</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #a0aec0;">
                                <i class="ri-history-line" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                                No transactions found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td>
                                    <div class="type-cell">
                                        <div class="type-icon <?= $tx['amount'] > 0 ? 'topup' : 'payment' ?>">
                                            <i class="<?= $tx['amount'] > 0 ? 'ri-arrow-down-line' : 'ri-arrow-up-line' ?>"></i>
                                        </div>
                                        <div class="type-info">
                                            <h4><?= ucfirst($tx['type']) ?></h4>
                                            <p><?= htmlspecialchars($tx['description']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; color: #4a5568; font-weight: 500;">
                                        <?= date('M d, Y', strtotime($tx['created_at'])) ?>
                                    </div>
                                    <div style="font-size: 11px; color: #a0aec0;">
                                        <?= date('h:i A', strtotime($tx['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-tag success">Completed</span>
                                </td>
                                <td class="amount-cell <?= $tx['amount'] > 0 ? 'positive' : 'negative' ?>">
                                    <?= $tx['amount'] > 0 ? '+' : '' ?>Rs. <?= number_format(abs($tx['amount']), 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    function setAmount(amount) {
        document.getElementById('topupAmount').value = amount;
        
        // Update active state in presets
        const presets = document.querySelectorAll('.amount-preset');
        presets.forEach(p => {
            if (p.innerText === `Rs. ${amount.toLocaleString()}`) {
                p.classList.add('active');
            } else {
                p.classList.remove('active');
            }
        });
    }

    function setMethod(element, method) {
        const methods = document.querySelectorAll('.method-item');
        methods.forEach(m => m.classList.remove('active'));
        element.classList.add('active');
    }

    function processTopUp() {
        const amount = document.getElementById('topupAmount').value;
        if (amount < 100) {
            alert('Minimum top up amount is Rs. 100');
            return;
        }
        
        const btn = document.querySelector('.topup-submit-btn');
        const originalHtml = btn.innerHTML;
        
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> redirecting to payment gateway...';
        btn.disabled = true;
        
        setTimeout(() => {
            alert(`Redirecting to secure payment page for Rs. ${parseFloat(amount).toLocaleString()}...`);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }, 1500);
    }

    function redeemCoupon() {
        const code = document.getElementById('couponCode').value.trim();
        const msg = document.getElementById('couponMessage');
        const btn = document.querySelector('.redeem-btn');
        
        if (!code) {
            alert('Please enter a coupon code');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';
        
        // Real coupon validation via AJAX
        const formData = new FormData();
        formData.append('code', code);

        fetch('redeem_coupon.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            msg.style.display = 'block';
            if (data.success) {
                msg.style.color = '#059669';
                msg.innerHTML = `<i class="ri-checkbox-circle-line"></i> ${data.message}`;
                
                // Show celebration and reload after 2 seconds
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                msg.style.color = '#dc2626';
                msg.innerHTML = `<i class="ri-error-warning-line"></i> ${data.message}`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            msg.style.display = 'block';
            msg.style.color = '#dc2626';
            msg.innerHTML = '<i class="ri-error-warning-line"></i> An error occurred. Please try again.';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Redeem';
        });
    }
</script>

</body>
</html>
