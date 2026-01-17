<?php
session_start();
include 'db.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Fetch Group Details
$sql = "SELECT * FROM savings_groups WHERE group_id = '$group_id'";
$group = $conn->query($sql)->fetch_assoc();

if (!$group) {
    die("Group not found.");
}

// 3. Security Check
$check = $conn->query("SELECT * FROM group_members WHERE group_id = '$group_id' AND user_id = '$user_id'");
if ($check->num_rows == 0) {
    die("You are not a member.");
}

// 4. Fetch Latest Announcement
$admin_id = $group['admin_id'];
$sql_announce = "SELECT message_content, sent_at FROM chat_messages 
                 WHERE group_id = '$group_id' AND user_id = '$admin_id' 
                 ORDER BY sent_at DESC LIMIT 1";
$latest_update = $conn->query($sql_announce)->fetch_assoc();

// 5. Get Member Count for the header
$count_sql = $conn->query("SELECT COUNT(*) as total FROM group_members WHERE group_id = '$group_id'");
$member_count = $count_sql->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($group['group_name']); ?> - Home</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* --- NEW MODERN HEADER STYLES --- */
        .group-hero {
            background-color: var(--card-bg);
            padding: 30px 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid #ccc;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Avatar Image (Auto-generated initials) */
        .group-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .hero-details h1 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--text-color);
        }

        .hero-details p {
            margin: 5px 0 10px 0;
            color: var(--text-muted);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Badges with Icons */
        .badge-container {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-money { background: rgba(32, 159, 54, 0.15); color: var(--success-color); }
        .badge-time { background: rgba(0, 123, 255, 0.15); color: var(--primary-color); }
        .badge-users { background: rgba(255, 193, 7, 0.15); color: #d39e00; }

        /* Icon styling (SVGs) */
        .icon { width: 16px; height: 16px; fill: currentColor; }

        /* Announcement Board */
        .announcement-board {
            max-width: 800px;
            margin: 20px auto;
            background: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--primary-color);
        }

        /* Action Grid */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            max-width: 800px;
            margin: 20px auto;
            padding: 0 10px;
        }

        .action-card {
            background: var(--card-bg);
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-color);
            box-shadow: var(--shadow);
            transition: transform 0.2s, border 0.2s;
            border: 1px solid transparent;
        }
        .action-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary-color);
        }

        /* Dark mode adjustment for text icons */
        [data-theme="dark"] .badge-users { color: #ffd54f; }
        
        /* Responsive: Stack vertically on small phones */
        @media (max-width: 500px) {
            .group-hero { flex-direction: column; text-align: center; }
            .badge-container { justify-content: center; }
        }
    </style>
</head>
<body>
    <div id="loader-wrapper">
<div id="loader-wrapper">
    <div class="loader-spinner"></div>
</div>
</div>

    <nav>
        <div class="logo">Ikibina<span style="color:var(--success-color)">.rw</span></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            
            <a href="group_chat.php?id=<?php echo $group_id; ?>" 
               style="background: var(--primary-color); color: white; padding: 8px 15px; border-radius: 20px; text-decoration:none; font-size:0.9rem; display:flex; align-items:center; gap:5px;">
               <svg style="width:16px; height:16px; fill:white;" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
               Chat
            </a>

            <button id="theme-toggle">🌗</button>
        </div>
    </nav>

    <header class="group-hero">
        
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($group['group_name']); ?>&background=random&size=128" 
             alt="Group Icon" class="group-avatar">
        
        <div class="hero-details">
            <h1><?php echo htmlspecialchars($group['group_name']); ?></h1>
            <p>
                <svg class="icon" viewBox="0 0 24 24" style="width:14px; height:14px;"><path d="M12 17c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6-9h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z"/></svg>
                Private Savings Group
            </p>
            
            <div class="badge-container">
                <span class="badge badge-money">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                    <?php echo number_format($group['contribution_amount']); ?> RWF
                </span>

                <span class="badge badge-time">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                    <?php echo ucfirst($group['frequency']); ?>
                </span>

                <span class="badge badge-users">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    <?php echo $member_count; ?> Members
                </span>
            </div>
        </div>
    </header>

    <div class="announcement-board">
        <h3 style="margin-top:0; display:flex; align-items:center; gap:10px;">
            <svg class="icon" style="width:24px; height:24px; fill:var(--primary-color);" viewBox="0 0 24 24"><path d="M21 11.01L3 11v2h18zM3 16h12v-2H3zM3 8h18V6H3z"/></svg>
            Group Details & Rules
        </h3>
        
        <p style="font-size:1.1rem; line-height:1.6; color:var(--text-color);">
            <?php echo $group['description'] ? nl2br(htmlspecialchars($group['description'])) : "Welcome to the group! Please ensure you pay your contributions on time. Respect all members."; ?>
        </p>

        <?php if($latest_update): ?>
            <div style="margin-top:20px; padding:15px; background:rgba(0,0,0,0.03); border-radius:8px; border-left:4px solid var(--accent-color);">
                <strong style="color:var(--text-muted); font-size:0.9rem;">LATEST UPDATE</strong>
                <p style="margin:5px 0 0 0; font-style:italic;">"<?php echo htmlspecialchars($latest_update['message_content']); ?>"</p>
                <small style="color:var(--text-muted); display:block; margin-top:5px; text-align:right;">
                    <?php echo date('M d, H:i', strtotime($latest_update['sent_at'])); ?>
                </small>
            </div>
        <?php endif; ?>
    </div>

    <div class="action-grid">
        
        <a href="group_chat.php?id=<?php echo $group_id; ?>" class="action-card">
            <div style="font-size:2rem; margin-bottom:10px;">💬</div>
            <strong>Open Chat</strong>
            <div style="font-size:0.8rem; color:var(--text-muted); margin-top:5px;">Talk & View History</div>
        </a>

        <a href="momo_form.php?group_id=<?php echo $group_id; ?>" class="action-card">
            <div style="font-size:2rem; margin-bottom:10px;">📲</div>
            <strong>Send Money</strong>
            <div style="font-size:0.8rem; color:var(--text-muted); margin-top:5px;">Via Mobile Money</div>
        </a>

        <div class="action-card" onclick="alert('Invite Code: <?php echo $group['invite_token']; ?>')" style="cursor:pointer;">
            <div style="font-size:2rem; margin-bottom:10px;">👋</div>
            <strong>Invite Friends</strong>
            <div style="font-size:0.8rem; color:var(--text-muted); margin-top:5px;">Share Code</div>
        </div>

    </div>

    <script src="script.js"></script>
</body>
</html>