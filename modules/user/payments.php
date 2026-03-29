<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$current_page = 'payments';
$user_id = $_SESSION['user_id'];

// Fetch current user details
$user_data = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$user_balance = $user_data['balance'] ?? 0.00;
$user_name = $user_data['full_name'] ?? "User";
$kurwa_pay_active = $user_data['kurwa_pay_active'] ?? 0;
$kurwa_pay_card = $user_data['kurwa_pay_card_number'] ?? null;

// Format card number nicely
$formatted_card = $kurwa_pay_card ? substr($kurwa_pay_card, 0, 4) . ' ' . substr($kurwa_pay_card, 4, 4) . ' ' . substr($kurwa_pay_card, 8, 2) : '.... .... ....';

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
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        .balance-card-large {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            border-radius: 20px;
            padding: 25px;
            color: white;
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.4);
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .balance-card-large.inactive {
            background: linear-gradient(135deg, #94a3b8 0%, #475569 100%);
            box-shadow: 0 10px 25px rgba(148, 163, 184, 0.3);
        }

        .balance-card-large.inactive .blur-overlay {
            position: absolute;
            top: 0; left: 0;right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 20px;
        }
        
        .activate-btn {
            background: #ffffff;
            color: #3542f3;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: 0.2s;
        }
        .activate-btn:hover {
            transform: scale(1.05);
        }

        .chip-icon {
            width: 45px;
            height: 35px;
            background: url('https://cdn-icons-png.flaticon.com/512/6404/6404100.png') no-select;
            background-size: cover;
            border-radius: 6px;
            opacity: 0.9;
        }

        .brand-logo { font-weight: 800; font-size: 20px; letter-spacing: 1px; font-style: italic; }
        .balance-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; }
        .balance-body .label { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
        .balance-body .amount { font-size: 32px; font-weight: 700; margin-top: 5px; }
        .balance-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 20px; }
        
        .card-number {
            font-family: 'Share Tech Mono', monospace;
            font-size: 22px;
            letter-spacing: 2px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .card-holder { font-size: 14px; text-transform: uppercase; font-weight: 500; margin-top: 5px; opacity: 0.9; }
    </style>
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    
    <!-- Global Header Section -->
    <?php 
    $page_title = "Payments & Wallet";
    $page_subtitle = "Manage your balance and transactions";
    include "../../includes/components/user_header.php"; 
    ?>

    <div class="payments-container">
        <!-- Top Section: Balance & Topup Form -->
        <div class="payment-hero">
            <div class="payment-left">
                <!-- Balance Card (First Row) -->
                <div class="balance-card-large <?= !$kurwa_pay_active ? 'inactive' : '' ?>">
                    <?php if (!$kurwa_pay_active): ?>
                    <div class="blur-overlay">
                        <button class="activate-btn" onclick="activateKurwaPay()" id="btnActivate">
                            <i class="ri-flashlight-line"></i> Activate Card (Rs. 500)
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="balance-header">
                        <div class="chip-icon"></div>
                        <div class="brand-logo">KURWA PAY</div>
                    </div>
                    <div class="balance-body">
                        <span class="label">Available Balance</span>
                        <h2 class="amount">Rs. <span id="displayBalance"><?= number_format($user_balance, 2) ?></span></h2>
                    </div>
                    <div class="balance-footer">
                        <div>
                            <div class="card-number" id="displayCardNumber"><?= $formatted_card ?></div>
                            <div class="card-holder"><?= htmlspecialchars($user_name) ?></div>
                        </div>
                        <i class="ri-visa-line" style="font-size: 40px; opacity: 0.9;"></i>
                    </div>
                </div>

                <!-- Coupon Section (Second Row) -->
                <div class="coupon-section">
                    <h3><i class="ri-ticket-2-line" style="color: #2F3CFF;"></i> Have a Coupon Code?</h3>
                    <div class="coupon-input-group">
                        <input type="text" id="couponCode" placeholder="Enter code (e.g. WELCOME100)">
                        <button class="redeem-btn" onclick="redeemCoupon()">Redeem</button>
                    </div>
                    <p id="couponMessage" style="font-size: 12px; margin-top: 10px; display: none;"></p>
                </div>

                <!-- Transfer Money Section (Third Row) -->
                <div class="transfer-section" style="background:#fff; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-top: 20px;">
                    <h3 style="margin-top:0; color:#0f172a; margin-bottom:5px;"><i class="ri-swap-line" style="color:#2F3CFF; margin-right:5px;"></i> Transfer Money</h3>
                    <p style="font-size:12px; color:#64748b; margin-bottom:15px;">Send money to another user instantly using their 10-digit card number.</p>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <input type="text" id="transferCard" placeholder="Recipient 10-Digit Card" maxlength="10" style="padding:12px; border:1px solid #e2e8f0; border-radius:10px; font-family:inherit; font-weight:600; letter-spacing:1px; outline:none;" onfocus="this.style.borderColor='#3542f3';" onblur="this.style.borderColor='#e2e8f0';">
                        <div style="position:relative;">
                            <span style="position:absolute; left:12px; top:12px; color:#64748b; font-weight:600;">Rs.</span>
                            <input type="number" id="transferAmount" placeholder="Amount" min="10" style="width:100%; padding:12px 12px 12px 40px; border:1px solid #e2e8f0; border-radius:10px; font-family:inherit; outline:none;" onfocus="this.style.borderColor='#3542f3';" onblur="this.style.borderColor='#e2e8f0';">
                        </div>
                        <button class="transfer-btn" id="btnSendMoney" onclick="initiateTransfer()" style="background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:white; padding:14px; border:none; border-radius:10px; font-weight:700; cursor:pointer; font-size:15px; box-shadow:0 4px 10px rgba(16,185,129,0.3);"><i class="ri-send-plane-fill"></i> Send Money</button>
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
        <div class="history-section" id="transaction-history">
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
                                    <?php 
                                        $status_class = 'pending';
                                        if ($tx['status'] === 'completed') $status_class = 'success';
                                        if ($tx['status'] === 'failed' || $tx['status'] === 'canceled') $status_class = 'failed';
                                    ?>
                                    <span class="status-tag <?= $status_class ?>"><?= ucfirst($tx['status']) ?></span>
                                </td>
                                <td class="amount-cell <?= $tx['amount'] > 0 ? 'positive' : 'negative' ?>">
                                    <?= $tx['amount'] > 0 ? '+' : '' ?>Rs. <?= number_format(abs($tx['amount']), 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Mobile Transaction Cards -->
            <div class="transaction-cards">
                <?php if (empty($transactions)): ?>
                    <div style="text-align: center; padding: 20px; color: #a0aec0;">No transactions found.</div>
                <?php else: ?>
                    <?php foreach ($transactions as $tx): ?>
                        <div class="transaction-card">
                            <div class="t-card-icon <?= $tx['amount'] > 0 ? 'topup' : 'payment' ?>" style="background: <?= $tx['amount'] > 0 ? '#e6fffa' : '#fff5f5' ?>; color: <?= $tx['amount'] > 0 ? '#38b2ac' : '#f56565' ?>;">
                                <i class="<?= $tx['amount'] > 0 ? 'ri-arrow-down-line' : 'ri-arrow-up-line' ?>"></i>
                            </div>
                            <div class="t-card-info">
                                <h4><?= ucfirst($tx['type']) ?></h4>
                                <p><?= htmlspecialchars($tx['description']) ?></p>
                                <p style="font-size: 10px; opacity: 0.7;"><?= date('M d, h:i A', strtotime($tx['created_at'])) ?></p>
                            </div>
                            <div class="t-card-amount">
                                <span class="val <?= $tx['amount'] > 0 ? 'positive' : 'negative' ?>" style="color: <?= $tx['amount'] > 0 ? '#10b981' : '#ef4444' ?>;">
                                    <?= $tx['amount'] > 0 ? '+' : '-' ?>Rs. <?= number_format(abs($tx['amount']), 2) ?>
                                </span>
                                <?php 
                                    $status_class = 'pending';
                                    $status_bg = '#fef9c3'; $status_color = '#854d0e';
                                    if ($tx['status'] === 'completed') { $status_class = 'success'; $status_bg = '#ecfdf5'; $status_color = '#059669'; }
                                    if ($tx['status'] === 'failed' || $tx['status'] === 'canceled') { $status_class = 'failed'; $status_bg = '#fee2e2'; $status_color = '#991b1b'; }
                                ?>
                                <span class="status <?= $status_class ?>" style="background: <?= $status_bg ?>; color: <?= $status_color ?>; font-size: 9px; padding: 2px 6px; border-radius: 4px;"><?= ucfirst($tx['status']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- OTP Modal -->
<div id="otpModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.6); backdrop-filter:blur(5px); align-items:center; justify-content:center;">
    <div style="background:white; padding:30px; border-radius:24px; width:100%; max-width:380px; text-align:center; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
        <div style="width:60px; height:60px; background:#e0e7ff; color:#3542f3; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:30px; margin:0 auto 15px;">
            <i class="ri-shield-keyhole-line"></i>
        </div>
        <h2 style="margin-bottom:10px; color:#0f172a; font-size:20px;">Verify Transfer</h2>
        <p style="color:#64748b; font-size:13px; margin-bottom:25px; line-height:1.5;">Enter the 6-digit OTP sent to your registered phone number to confirm the transfer of <strong>Rs. <span id="confirmTransferAmount">0</span></strong> to <strong id="confirmTransferName">User</strong>.</p>
        
        <input type="text" id="transferOtp" placeholder="123456" maxlength="6" style="width:100%; padding:15px; border:2px solid #e2e8f0; border-radius:12px; text-align:center; font-size:24px; letter-spacing:10px; font-weight:700; color:#0f172a; margin-bottom:20px; outline:none; transition:0.3s;" onfocus="this.style.borderColor='#3542f3';" onblur="this.style.borderColor='#e2e8f0';">
        
        <button onclick="verifyTransfer()" id="btnVerifyTransfer" style="width:100%; background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:white; padding:15px; border:none; border-radius:12px; font-weight:700; font-size:16px; cursor:pointer; margin-bottom:10px; box-shadow:0 6px 15px rgba(16,185,129,0.3);"><i class="ri-checkbox-circle-line"></i> Confirm & Transfer</button>
        <button onclick="document.getElementById('otpModal').style.display='none'" style="width:100%; background:transparent; color:#64748b; border:1px solid #e2e8f0; padding:12px; border-radius:12px; font-weight:600; cursor:pointer; transition:0.2s;" onmouseover="this.style.background='#f8fafc'">Cancel</button>
    </div>
</div>

<!-- Activation Modal -->
<div id="activationModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.6); backdrop-filter:blur(5px); align-items:center; justify-content:center;">
    <div style="background:white; padding:35px 30px; border-radius:24px; width:100%; max-width:380px; text-align:center; box-shadow: 0 25px 50px rgba(0,0,0,0.25); position:relative; overflow:hidden;">
        <div style="position:absolute; top:-50px; left:-50px; width:150px; height:150px; background:linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(58, 12, 163, 0.1) 100%); border-radius:50%;"></div>
        <div style="width:70px; height:70px; background:linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:32px; margin:0 auto 20px; box-shadow:0 10px 20px rgba(67, 97, 238, 0.3); position:relative; z-index:2;">
            <i class="ri-flashlight-fill"></i>
        </div>
        <h2 style="margin-bottom:12px; color:#0f172a; font-size:22px; font-weight:700; position:relative; z-index:2;">Activate Kurwa Pay?</h2>
        <p style="color:#475569; font-size:14px; margin-bottom:25px; line-height:1.6; position:relative; z-index:2;">A one-time activation fee of <strong>Rs. 500</strong> will be seamlessly deducted from your wallet to instantly unlock your premium virtual card.</p>
        
        <button onclick="confirmActivateKurwaPay()" id="btnConfirmActivate" style="width:100%; background:linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); color:white; padding:15px; border:none; border-radius:12px; font-weight:700; font-size:16px; cursor:pointer; margin-bottom:12px; box-shadow:0 6px 15px rgba(67, 97, 238, 0.3); transition:0.3s; position:relative; z-index:2;"><i class="ri-checkbox-circle-line"></i> Confirm Activation</button>
        <button onclick="document.getElementById('activationModal').style.display='none'" style="width:100%; background:transparent; color:#64748b; border:1px solid #e2e8f0; padding:12px; border-radius:12px; font-weight:600; cursor:pointer; transition:0.2s; position:relative; z-index:2;" onmouseover="this.style.background='#f8fafc'">Cancel</button>
    </div>
</div>

<!-- Success Activation Modal -->
<div id="successActivationModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.6); backdrop-filter:blur(5px); align-items:center; justify-content:center;">
    <div style="background:white; padding:40px 30px; border-radius:24px; width:100%; max-width:380px; text-align:center; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
        <div style="width:80px; height:80px; background:#ecfdf5; color:#10b981; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:40px; margin:0 auto 20px; box-shadow:0 10px 25px rgba(16, 185, 129, 0.2);">
            <i class="ri-checkbox-circle-fill"></i>
        </div>
        <h2 style="color:#0f172a; font-size:24px; font-weight:700; margin-bottom:12px;">Success!</h2>
        <p style="color:#64748b; font-size:15px; margin-bottom:25px; line-height:1.5;">Your Kurwa Pay card is now active. You received a unique 10-digit number and can start making instant transfers.</p>
        <button onclick="location.reload()" style="width:100%; background:linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); color:white; padding:15px; border:none; border-radius:12px; font-weight:700; font-size:16px; cursor:pointer; box-shadow:0 6px 15px rgba(67, 97, 238, 0.3); transition:0.3s;"><i class="ri-rocket-line" style="margin-right:5px;"></i> Awesome!</button>
    </div>
</div>

<!-- Error Activation Modal -->
<div id="errorActivationModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.6); backdrop-filter:blur(5px); align-items:center; justify-content:center;">
    <div style="background:white; padding:40px 30px; border-radius:24px; width:100%; max-width:380px; text-align:center; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
        <div style="width:80px; height:80px; background:#fef2f2; color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:40px; margin:0 auto 20px; box-shadow:0 10px 25px rgba(239, 68, 68, 0.2);">
            <i class="ri-error-warning-fill"></i>
        </div>
        <h2 style="color:#0f172a; font-size:24px; font-weight:700; margin-bottom:12px;">Activation Failed</h2>
        <p id="errorActivationMessage" style="color:#64748b; font-size:15px; margin-bottom:25px; line-height:1.5;">An error occurred.</p>
        <button onclick="document.getElementById('errorActivationModal').style.display='none'" style="width:100%; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color:white; padding:15px; border:none; border-radius:12px; font-weight:700; font-size:16px; cursor:pointer; box-shadow:0 6px 15px rgba(239, 68, 68, 0.3); transition:0.3s;">Understood</button>
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
        const method = document.querySelector('.method-item.active span').innerText.toLowerCase();

        if (amount < 100) {
            alert('Minimum top up amount is Rs. 100');
            return;
        }
        
        const btn = document.querySelector('.topup-submit-btn');
        const originalHtml = btn.innerHTML;
        
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> initiating payment...';
        btn.disabled = true;

        if (method === 'esewa') {
            // Process via eSewa
            const formData = new FormData();
            formData.append('amount', amount);

            fetch('handlers/process_esewa.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Create a hidden form to submit to eSewa
                    const form = document.createElement('form');
                    form.setAttribute('method', 'POST');
                    form.setAttribute('action', data.url);

                    for (const key in data.params) {
                        const hiddenField = document.createElement('input');
                        hiddenField.setAttribute('type', 'hidden');
                        hiddenField.setAttribute('name', key);
                        hiddenField.setAttribute('value', data.params[key]);
                        form.appendChild(hiddenField);
                    }

                    document.body.appendChild(form);
                    form.submit();
                } else {
                    alert(data.message || 'Error occurred');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection error. Please try again.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        } else {
            // For other methods (Placeholder)
            setTimeout(() => {
                alert(`Redirecting to secure payment page for ${method.toUpperCase()} for Rs. ${parseFloat(amount).toLocaleString()}...`);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }, 1000);
        }
    }

    // Check for URL parameters to show success/error alerts
    window.onload = function() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('success')) {
            const msg = params.get('success');
            const alertBox = document.createElement('div');
            alertBox.style = "position:fixed; top:20px; right:20px; background:#10b981; color:white; padding:15px 25px; border-radius:10px; z-index:9999; box-shadow:0 10px 20px rgba(0,0,0,0.1); animation: slideIn 0.3s ease-out;";
            alertBox.innerHTML = `<i class="ri-checkbox-circle-line"></i> ${msg}`;
            document.body.appendChild(alertBox);
            setTimeout(() => alertBox.remove(), 5000);
        }
        if (params.has('error')) {
            const msg = params.get('error');
            const alertBox = document.createElement('div');
            alertBox.style = "position:fixed; top:20px; right:20px; background:#ef4444; color:white; padding:15px 25px; border-radius:10px; z-index:9999; box-shadow:0 10px 20px rgba(0,0,0,0.1); animation: slideIn 0.3s ease-out;";
            alertBox.innerHTML = `<i class="ri-error-warning-line"></i> ${msg}`;
            document.body.appendChild(alertBox);
            setTimeout(() => alertBox.remove(), 5000);
        }
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

        fetch('handlers/redeem_coupon.php', {
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

    // KURWA PAY SPECIFIC SCRIPTS
    FormatDisplayBalance = (bal) => parseFloat(bal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    function activateKurwaPay() {
        document.getElementById('activationModal').style.display = 'flex';
    }

    function confirmActivateKurwaPay() {
        const btn = document.getElementById('btnConfirmActivate');
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Processing...';
        btn.disabled = true;

        fetch('handlers/activate_kurwa_pay.php', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove inactive overlay
                const card = document.querySelector('.balance-card-large');
                card.classList.remove('inactive');
                const overlay = card.querySelector('.blur-overlay');
                if(overlay) overlay.remove();
                
                // Update text
                document.getElementById('displayBalance').innerText = FormatDisplayBalance(data.new_balance);
                
                // Format card number nicely
                const cn = data.card_number;
                const formattedCN = cn.substring(0,4) + ' ' + cn.substring(4,8) + ' ' + cn.substring(8,10);
                document.getElementById('displayCardNumber').innerText = formattedCN;
                
                // Show beautiful success modal instead of alert
                document.getElementById('activationModal').style.display = 'none';
                document.getElementById('successActivationModal').style.display = 'flex';
            } else {
                document.getElementById('activationModal').style.display = 'none';
                document.getElementById('errorActivationMessage').innerText = data.message;
                document.getElementById('errorActivationModal').style.display = 'flex';
                btn.innerHTML = origText;
                btn.disabled = false;
            }
        })
        .catch(e => {
            console.error(e);
            alert("A network error occurred. Please try again.");
            btn.innerHTML = origText;
            btn.disabled = false;
        });
    }

    function initiateTransfer() {
        const rcptCard = document.getElementById('transferCard').value.trim();
        const amount = document.getElementById('transferAmount').value;

        if (rcptCard.length !== 10) {
            alert("Please enter a valid 10-digit recipient card number.");
            return;
        }
        if (amount <= 0) {
            alert("Please enter a valid transfer amount.");
            return;
        }

        const btn = document.getElementById('btnSendMoney');
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Getting OTP...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('recipient_card', rcptCard);
        formData.append('amount', amount);

        fetch('handlers/send_transfer_otp.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Show OTP modal
                document.getElementById('confirmTransferAmount').innerText = parseFloat(amount).toLocaleString();
                document.getElementById('confirmTransferName').innerText = data.recipient_name;
                document.getElementById('transferOtp').value = '';
                document.getElementById('otpModal').style.display = 'flex';
            } else {
                alert("Notice: " + data.message);
            }
        })
        .catch(e => {
            console.error(e);
            alert("A network error occurred while sending OTP.");
        })
        .finally(() => {
            btn.innerHTML = origText;
            btn.disabled = false;
        });
    }

    function verifyTransfer() {
        const otp = document.getElementById('transferOtp').value.trim();
        if (otp.length < 6) {
            alert("Please enter the 6-digit OTP.");
            return;
        }

        const btn = document.getElementById('btnVerifyTransfer');
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Verifying...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('otp', otp);

        fetch('handlers/verify_transfer.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('otpModal').style.display = 'none';
                document.getElementById('displayBalance').innerText = FormatDisplayBalance(data.new_balance);
                document.getElementById('transferCard').value = '';
                document.getElementById('transferAmount').value = '';
                
                alert("Success: " + data.message);
                location.reload(); // To update history
            } else {
                alert("Verification Failed: " + data.message);
                btn.innerHTML = origText;
                btn.disabled = false;
            }
        })
        .catch(e => {
            console.error(e);
            alert("A network error occurred while verifying the transfer.");
            btn.innerHTML = origText;
            btn.disabled = false;
        });
    }
</script>

</body>
</html>
