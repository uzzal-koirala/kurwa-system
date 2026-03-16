<?php
require_once '../../includes/core/config.php';
require_once '../../includes/core/auth_check.php';

$current_page = 'chat';
$target_caretaker_id = isset($_GET['caretaker_id']) ? intval($_GET['caretaker_id']) : 0;

$target_caretaker = null;
$is_booked = false;
if ($target_caretaker_id > 0) {
    $stmt = $conn->prepare("SELECT id, full_name, image_url, specialization FROM caretakers WHERE id = ?");
    $stmt->bind_param("i", $target_caretaker_id);
    $stmt->execute();
    $target_caretaker = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Check if this caretaker is booked by the current user
    $user_id = $_SESSION['user_id'] ?? 0;
    $bk_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM caretaker_bookings WHERE user_id = ? AND caretaker_id = ? AND status IN ('pending','confirmed')");
    $bk_stmt->bind_param("ii", $user_id, $target_caretaker_id);
    $bk_stmt->execute();
    $bk_res = $bk_stmt->get_result()->fetch_assoc();
    $is_booked = ($bk_res['cnt'] ?? 0) > 0;
    $bk_stmt->close();
}
$user_name = $_SESSION['full_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Kurwa</title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-light: #f8fafc;
            --white: #ffffff;
            --text-primary: #0f172a;
            --text-muted: #64748b;
            --chat-primary: #2F3CFF;
            --primary-gradient: linear-gradient(135deg, #2F3CFF 0%, #7c3aed 100%);
            --shadow-soft: 0 10px 25px rgba(0, 0, 0, 0.05);
            --radius-lg: 30px;
            --radius-md: 20px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            background-color: var(--bg-light); 
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
        }

        .chat-layout {
            display: flex;
            height: calc(100vh - 120px);
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            border: 1px solid #f1f5f9;
        }

        /* ====== SIDEBAR ====== */
        .chat-sidebar {
            width: 380px;
            flex-shrink: 0;
            background: var(--white);
            display: flex;
            flex-direction: column;
            border-right: 1px solid #f1f5f9;
        }

        .sidebar-top {
            padding: 30px 25px;
        }

        .sidebar-top h2 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 20px;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .sidebar-search {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 12px 16px;
            gap: 12px;
        }

        .sidebar-search i { color: #94a3b8; font-size: 18px; }

        .sidebar-search input {
            background: transparent;
            border: none;
            outline: none;
            color: #1e293b;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            width: 100%;
        }

        .chat-list {
            flex-grow: 1;
            overflow-y: auto;
            padding: 10px 15px;
        }

        .chat-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 8px;
        }

        .chat-item:hover { background: #f8fafc; transform: translateX(5px); }

        .chat-item.active {
            background: #f1f5ff;
        }

        .chat-avatar-wrap { position: relative; flex-shrink: 0; }

        .chat-item img {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .online-dot {
            width: 14px;
            height: 14px;
            background: #22c55e;
            border-radius: 50%;
            border: 3px solid #fff;
            position: absolute;
            bottom: -2px;
            right: -2px;
        }

        .chat-item-info { flex-grow: 1; overflow: hidden; }

        .chat-item-info h4 {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .chat-item-info p {
            font-size: 13px;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ====== MAIN CHAT AREA ====== */
        .chat-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: #fafbfc;
        }

        .chat-header {
            padding: 20px 30px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f1f5f9;
            z-index: 10;
        }

        .chat-user-info { display: flex; align-items: center; gap: 15px; }
        
        .mobile-back-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: #64748b;
            cursor: pointer;
            margin-right: 10px;
        }

        .chat-user-info img {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            object-fit: cover;
        }

        .chat-user-info h4 { font-size: 17px; font-weight: 700; color: #1e293b; }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .msg-bubble {
            max-width: 70%;
            padding: 14px 20px;
            border-radius: 22px;
            font-size: 14.5px;
            line-height: 1.6;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .msg-bubble.sent {
            align-self: flex-end;
            background: var(--primary-gradient);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .msg-bubble.received {
            align-self: flex-start;
            background: #fff;
            color: #1e293b;
            border-bottom-left-radius: 4px;
            border: 1px solid #f1f5f9;
        }

        .msg-time {
            font-size: 10px;
            margin-top: 8px;
            opacity: 0.7;
            display: block;
        }

        .msg-bubble.sent .msg-time { text-align: right; }

        /* Input area */
        .chat-input-area {
            padding: 25px 30px;
            background: #fff;
            border-top: 1px solid #f1f5f9;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            background: #f8fafc;
            padding: 10px 10px 10px 20px;
            border-radius: 20px;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .input-wrapper:focus-within {
            border-color: #2F3CFF;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(47, 60, 255, 0.1);
        }

        .input-wrapper input {
            flex: 1;
            border: none;
            background: none;
            outline: none;
            font-size: 15px;
            font-family: inherit;
        }

        .send-btn {
            width: 45px;
            height: 45px;
            border-radius: 15px;
            background: var(--chat-primary);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.2s;
        }

        .send-btn:hover { transform: scale(1.05); background: #2430D8; }

        @media (max-width: 1024px) {
            .chat-sidebar { width: 320px; }
        }

        @media (max-width: 768px) {
            .chat-layout { height: calc(100vh - 180px); }
            .chat-sidebar { 
                width: 100%; 
                display: <?= $target_caretaker_id > 0 ? 'none' : 'flex' ?>;
            }
            .chat-main { 
                display: <?= $target_caretaker_id > 0 ? 'flex' : 'none' ?>;
            }
            .mobile-back-btn { display: block; }
        }
    </style>
</head>
<body>

<?php include '../../includes/components/sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="chat-layout">

        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-top">
                <h2>Messages</h2>
                <div class="sidebar-search">
                    <i class="ri-search-line"></i>
                    <input type="text" placeholder="Search conversations...">
                </div>
            </div>
            <div class="chat-list" id="chatList">
                <div class="empty-chat-list">
                    <i class="ri-loader-4-line"></i>
                    Loading chats...
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main">
            <?php if ($target_caretaker_id > 0 && $target_caretaker): ?>
                <!-- Header -->
                <div class="chat-header">
                    <div class="chat-user-info">
                        <button class="mobile-back-btn" onclick="window.location.href='chat.php'"><i class="ri-arrow-left-s-line"></i></button>
                        <img src="<?= htmlspecialchars($target_caretaker['image_url'] ?? 'https://ui-avatars.com/api/?name='.urlencode($target_caretaker['full_name']).'&background=2F3CFF&color=fff') ?>" alt="">
                        <div>
                            <h4><?= htmlspecialchars($target_caretaker['full_name']) ?></h4>
                            <div class="online-status" style="font-size: 11px; color: #22c55e; font-weight: 600;">Active Now</div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="chat-messages" id="chatMessages">
                    <div style="text-align:center; color:#94a3b8; padding: 20px;">Loading messages...</div>
                </div>

                <!-- Input -->
                <div class="chat-input-area">
                    <div class="input-wrapper">
                        <input type="text" id="msgInput" placeholder="Write something..." onkeypress="if(event.key==='Enter') sendMsg()">
                        <button class="send-btn" onclick="sendMsg()" id="sendBtn">
                            <i class="ri-send-plane-fill"></i>
                        </button>
                    </div>
                </div>

            <?php else: ?>
                <div class="chat-empty">
                    <div class="chat-empty-icon"><i class="ri-chat-smile-3-line"></i></div>
                    <h3>Your messages</h3>
                    <p>Select a conversation from the list, or open a caretaker's profile to start chatting.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    const caretakerId = <?= $target_caretaker_id ?>;
    let lastMsgHash = '';

    async function loadChatList() {
        try {
            const res = await fetch('handlers/chat_handler.php?action=list');
            const data = await res.json();
            const list = document.getElementById('chatList');
            if (data.length > 0) {
                list.innerHTML = data.map(c => `
                    <div class="chat-item ${c.id == caretakerId ? 'active' : ''}" onclick="window.location.href='chat.php?caretaker_id=${c.id}'">
                        <div class="chat-avatar-wrap">
                            <img src="${c.image_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(c.full_name)}&background=3542f3&color=fff`}" alt="">
                            <div class="online-dot"></div>
                        </div>
                        <div class="chat-item-info">
                            <h4>${c.full_name}</h4>
                            <p>${c.last_msg ? c.last_msg.substring(0, 35) + (c.last_msg.length > 35 ? '...' : '') : 'Start chatting...'}</p>
                        </div>
                        <div class="chat-item-meta">
                            <span class="chat-time">${c.last_time ? formatTime(c.last_time) : ''}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = `<div class="empty-chat-list">
                    <i class="ri-chat-off-line"></i>
                    <p>No conversations yet</p>
                </div>`;
            }
        } catch(e) { console.error(e); }
    }

    function formatTime(dateStr) {
        const d = new Date(dateStr);
        const now = new Date();
        const diffH = (now - d) / 3600000;
        if (diffH < 24) return d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        return d.toLocaleDateString([], {month:'short', day:'numeric'});
    }

    async function loadMessages() {
        if (caretakerId <= 0) return;
        const box = document.getElementById('chatMessages');
        try {
            const res = await fetch(`handlers/chat_handler.php?action=fetch&caretaker_id=${caretakerId}`);
            const data = await res.json();
            const hash = JSON.stringify(data.map(m => m.id));
            if (hash === lastMsgHash) return;
            lastMsgHash = hash;

            if (data.length > 0) {
                let lastDate = '';
                box.innerHTML = data.map(m => {
                    const msgDate = new Date(m.created_at).toLocaleDateString([], {weekday:'long', month:'long', day:'numeric'});
                    let sep = '';
                    if (msgDate !== lastDate) {
                        lastDate = msgDate;
                        sep = `<div class="date-separator"><span>${msgDate}</span></div>`;
                    }
                    const isSent = m.receiver_type === 'caretaker';
                    const time = new Date(m.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
                    return `${sep}
                        <div class="msg-bubble ${isSent ? 'sent' : 'received'}">
                            ${escapeHtml(m.message)}
                            <div class="msg-meta">
                                <span>${time}</span>
                                ${isSent ? '<i class="ri-check-double-line"></i>' : ''}
                            </div>
                        </div>`;
                }).join('');
                box.scrollTop = box.scrollHeight;
            } else {
                box.innerHTML = `<div class="chat-empty" style="height:100%;">
                    <div class="chat-empty-icon"><i class="ri-chat-new-line"></i></div>
                    <h3>Say hello!</h3>
                    <p>This is the beginning of your conversation.</p>
                </div>`;
            }
        } catch(e) { console.error(e); }
    }

    function escapeHtml(t) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(t));
        return d.innerHTML;
    }

    async function sendMsg() {
        const input = document.getElementById('msgInput');
        const btn = document.getElementById('sendBtn');
        const text = input.value.trim();
        if (!text || caretakerId <= 0) return;

        input.value = '';
        btn.innerHTML = '<i class="ri-loader-4-line" style="animation: spin 0.8s linear infinite;"></i>';
        btn.disabled = true;

        const fd = new FormData();
        fd.append('caretaker_id', caretakerId);
        fd.append('message', text);

        try {
            const res = await fetch('handlers/chat_handler.php?action=send', { method:'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                lastMsgHash = '';
                await loadMessages();
                await loadChatList();
            }
        } catch(e) { console.error(e); }
        finally {
            btn.innerHTML = '<i class="ri-send-plane-fill"></i>';
            btn.disabled = false;
            input.focus();
        }
    }

    // Add spin animation
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(style);

    loadChatList();
    loadMessages();
    setInterval(() => { loadMessages(); loadChatList(); }, 5000);
</script>
</body>
</html>
