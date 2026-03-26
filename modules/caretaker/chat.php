<?php
require_once '../../includes/core/config.php';

if (!isset($_SESSION['caretaker_id'])) {
    header("Location: login.php");
    exit;
}

$caretaker_id = $_SESSION['caretaker_id'];
$caretaker_name = $_SESSION['caretaker_name'] ?? 'Caretaker';
$current_page = 'chat';

$target_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$target_user = null;

if ($target_user_id > 0) {
    $stmt = $conn->prepare("SELECT id, full_name, profile_image FROM users WHERE id = ?");
    $stmt->bind_param("i", $target_user_id);
    $stmt->execute();
    $target_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Caretaker | Kurwa</title>
    <link rel="stylesheet" href="../../assets/css/caretaker_sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-light: #f4f7fe;
            --white: #ffffff;
            --text-primary: #1b2559;
            --text-muted: #a3aed0;
            --chat-primary: #4361ee;
            --primary-gradient: linear-gradient(135deg, #4361ee 0%, #7c3aed 100%);
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.05);
            --radius-lg: 24px;
            --radius-md: 16px;
        }

        body.caretaker-body {
            background-color: var(--bg-light); 
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
        }

        .main-content {
            margin-left: 320px;
            padding: 30px 40px;
            transition: all 0.3s ease;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .page-main-title {
            font-size: 24px;
            font-weight: 800;
            color: #1b2559;
            margin: 0;
        }

        .chat-layout {
            display: flex;
            height: calc(100vh - 120px);
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        /* ====== SIDEBAR ====== */
        .chat-sidebar {
            width: 350px;
            flex-shrink: 0;
            background: var(--white);
            display: flex;
            flex-direction: column;
            border-right: 1px solid #f1f5f9;
        }

        .sidebar-top {
            padding: 25px;
        }

        .sidebar-search {
            display: flex;
            align-items: center;
            background: #f8faff;
            border-radius: 12px;
            padding: 12px 16px;
            gap: 12px;
            border: 2px solid transparent;
            transition: 0.3s;
        }

        .sidebar-search:focus-within {
            border-color: var(--chat-primary);
            background: #fff;
        }

        .sidebar-search i { color: var(--text-muted); font-size: 18px; }

        .sidebar-search input {
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-primary);
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
            border: 1px solid transparent;
        }

        .chat-item:hover { background: #f8faff; border-color: #eef2ff;}

        .chat-item.active {
            background: #f0f7ff;
            border-color: #eef2ff;
        }

        .chat-avatar-wrap { position: relative; flex-shrink: 0; }

        .chat-item img {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .online-dot {
            width: 12px;
            height: 12px;
            background: #22c55e;
            border-radius: 50%;
            border: 2px solid #fff;
            position: absolute;
            bottom: -2px;
            right: -2px;
        }

        .chat-item-info { flex-grow: 1; overflow: hidden; }

        .chat-item-info h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .chat-item-info p {
            font-size: 13px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-item-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
        }
        
        .chat-time {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ====== MAIN CHAT AREA ====== */
        .chat-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: #f8faff;
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
            color: var(--text-muted);
            cursor: pointer;
            margin-right: 10px;
        }

        .chat-user-info img {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            object-fit: cover;
        }

        .chat-user-info h4 { font-size: 17px; font-weight: 700; color: var(--text-primary); }

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
            border-radius: 20px;
            font-size: 14.5px;
            line-height: 1.6;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .msg-bubble.sent {
            align-self: flex-end;
            background: var(--chat-primary);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .msg-bubble.received {
            align-self: flex-start;
            background: #fff;
            color: var(--text-primary);
            border-bottom-left-radius: 4px;
            border: 1px solid #f1f5f9;
        }

        .msg-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            font-size: 10px;
            opacity: 0.8;
            justify-content: flex-end;
        }

        .msg-bubble.received .msg-meta {
            justify-content: flex-start;
            color: var(--text-muted);
        }

        /* Input area */
        .chat-input-area {
            padding: 20px 30px;
            background: #fff;
            border-top: 1px solid #f1f5f9;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            background: #f8faff;
            padding: 10px 10px 10px 20px;
            border-radius: 20px;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .input-wrapper:focus-within {
            border-color: var(--chat-primary);
            background: #fff;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.1);
        }

        .input-wrapper input {
            flex: 1;
            border: none;
            background: none;
            outline: none;
            font-size: 15px;
            font-family: inherit;
            color: var(--text-primary);
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

        .send-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3); }

        .chat-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-muted);
            text-align: center;
            padding: 40px;
        }
        
        .chat-empty-icon {
            font-size: 60px;
            color: #e2e8f0;
            margin-bottom: 20px;
        }
        
        .chat-empty h3 {
            font-size: 20px;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .date-separator {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .date-separator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
            z-index: 1;
        }

        .date-separator span {
            background: #f8faff;
            padding: 0 15px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 500;
            position: relative;
            z-index: 2;
        }

        @media (max-width: 1024px) {
            .chat-sidebar { width: 320px; }
            .main-content { margin-left: 280px; padding: 20px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 15px; }
            .chat-layout { height: calc(100vh - 140px); }
            .chat-sidebar { 
                width: 100%; 
                display: <?= $target_user_id > 0 ? 'none' : 'flex' ?>;
            }
            .chat-main { 
                display: <?= $target_user_id > 0 ? 'flex' : 'none' ?>;
            }
            .mobile-back-btn { display: block; }
        }
    </style>
</head>
<body class="caretaker-body">

<?php include '../../includes/components/caretaker_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-header">
        <h1 class="page-main-title">Messages</h1>
    </div>

    <div class="chat-layout">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-top">
                <div class="sidebar-search">
                    <i class="ri-search-line"></i>
                    <input type="text" placeholder="Search conversations...">
                </div>
            </div>
            <div class="chat-list" id="chatList">
                <div class="chat-empty">
                    <i class="ri-loader-4-line" style="animation: spin 1s linear infinite; font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Loading chats...</p>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main">
            <?php if ($target_user_id > 0 && $target_user): ?>
                <!-- Header -->
                <div class="chat-header">
                    <div class="chat-user-info">
                        <button class="mobile-back-btn" onclick="window.location.href='chat.php'"><i class="ri-arrow-left-s-line"></i></button>
                        <img src="<?= htmlspecialchars($target_user['profile_image'] ?? 'https://ui-avatars.com/api/?name='.urlencode($target_user['full_name']).'&background=4361ee&color=fff') ?>" alt="">
                        <div>
                            <h4><?= htmlspecialchars($target_user['full_name']) ?></h4>
                            <div class="online-status" style="font-size: 11px; color: #22c55e; font-weight: 600;">Patient</div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="chat-messages" id="chatMessages">
                    <div style="text-align:center; color:#a3aed0; padding: 20px;">Loading messages...</div>
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
                    <p>Select a conversation from the list to start chatting.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../../assets/js/sidebar.js"></script>
<script>
    const userId = <?= $target_user_id ?>;
    let lastMsgHash = '';

    async function loadChatList() {
        try {
            const res = await fetch('handlers/chat_handler.php?action=list');
            const data = await res.json();
            const list = document.getElementById('chatList');
            if (data.length > 0) {
                list.innerHTML = data.map(c => `
                    <div class="chat-item ${c.id == userId ? 'active' : ''}" onclick="window.location.href='chat.php?user_id=${c.id}'">
                        <div class="chat-avatar-wrap">
                            <img src="${c.profile_image || `https://ui-avatars.com/api/?name=${encodeURIComponent(c.full_name)}&background=4361ee&color=fff`}" alt="">
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
                list.innerHTML = `<div class="chat-empty" style="padding: 20px;">
                    <i class="ri-chat-off-line" style="font-size: 40px; color: #e2e8f0;"></i>
                    <p style="margin-top: 10px;">No patients yet</p>
                </div>`;
            }
        } catch(e) { console.error(e); }
    }

    function formatTime(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        const now = new Date();
        const diffH = (now - d) / 3600000;
        if (diffH < 24) return d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        return d.toLocaleDateString([], {month:'short', day:'numeric'});
    }

    async function loadMessages() {
        if (userId <= 0) return;
        const box = document.getElementById('chatMessages');
        try {
            const res = await fetch(`handlers/chat_handler.php?action=fetch&user_id=${userId}`);
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
                    const isSent = m.receiver_type === 'user';
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
                    <p>This is the beginning of your conversation with the patient.</p>
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
        if (!text || userId <= 0) return;

        input.value = '';
        btn.innerHTML = '<i class="ri-loader-4-line" style="animation: spin 0.8s linear infinite;"></i>';
        btn.disabled = true;

        const fd = new FormData();
        fd.append('user_id', userId);
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

    const style = document.createElement('style');
    style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(style);

    loadChatList();
    loadMessages();
    setInterval(() => { loadMessages(); loadChatList(); }, 3000);
</script>
</body>
</html>
