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

// 2. Security Check: Member?
$check_member = $conn->query("SELECT * FROM group_members WHERE group_id = '$group_id' AND user_id = '$user_id'");
if ($check_member->num_rows == 0) {
    die("You are not a member of this group.");
}

// 3. Handle Message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_content'])) {
    $content = $conn->real_escape_string($_POST['message_content']);
    if (!empty($content)) {
        $conn->query("INSERT INTO chat_messages (group_id, user_id, message_content) VALUES ('$group_id', '$user_id', '$content')");
        header("Location: group_chat.php?id=$group_id"); 
        exit();
    }
}

// 4. Group Info
$group_info = $conn->query("SELECT * FROM savings_groups WHERE group_id = '$group_id'")->fetch_assoc();

// 5. Messages
$sql_msgs = "SELECT m.message_content, m.sent_at, u.full_name, m.user_id 
             FROM chat_messages m 
             JOIN users u ON m.user_id = u.user_id 
             WHERE m.group_id = '$group_id' 
             ORDER BY m.sent_at ASC";
$messages = $conn->query($sql_msgs);

// 6. Members List (UPDATED to select profile_pic)
$sql_members = "SELECT u.user_id, u.full_name, u.phone_number, u.profile_pic, gm.role 
                FROM group_members gm 
                JOIN users u ON gm.user_id = u.user_id 
                WHERE gm.group_id = '$group_id'";
$members = $conn->query($sql_members);

// 7. Money Totals
$savings_map = []; 
$sql_money = "SELECT user_id, SUM(amount) as total FROM transactions WHERE group_id = '$group_id' AND amount > 0 GROUP BY user_id";
$money_result = $conn->query($sql_money);
while($row = $money_result->fetch_assoc()) {
    $savings_map[$row['user_id']] = $row['total'];
}

// 8. Admin Check
$is_admin = ($group_info['admin_id'] == $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?php echo htmlspecialchars($group_info['group_name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar {
            width: 350px; flex: 0 0 350px; display: flex;
            flex-direction: column; border-right: 1px solid #333;
            max-height: calc(100vh - 70px);
        }
        .sidebar-content { flex: 1; overflow-y: auto; padding: 20px; }
        .invite-box { padding: 20px; background: rgba(0,0,0,0.2); border-top: 1px solid #444; text-align: center; }
        .chat-area { flex: 1; display: flex; flex-direction: column; max-height: calc(100vh - 70px); }
    </style>
</head>
<body style="overflow:hidden;"> 
<div id="loader-wrapper">
    <div class="loader-spinner"></div>
</div>
</div>
    
    <nav>
        <div class="logo">Ikibina<span style="color:var(--success-color)">.rw</span></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <button id="theme-toggle">🌗 Mode</button>
        </div>
    </nav>

    <div class="group-container">
        
        <div class="sidebar">
            <div class="sidebar-content">
                <h2 style="color:var(--primary-color); margin-top:0;"><?php echo htmlspecialchars($group_info['group_name']); ?></h2>
                <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom: 20px;">
                    Contribution: <strong><?php echo number_format($group_info['contribution_amount']); ?> RWF</strong><br>
                    Next Payout: <em>Friday</em>
                </p>
                
                <hr style="border:0; border-top:1px solid #444; margin:15px 0;">
                
                <h3>Members (<?php echo $members->num_rows; ?>)</h3>
                <ul style="list-style:none; padding:0; margin-bottom: 30px;">
                    <?php while($mem = $members->fetch_assoc()): ?>
                        <?php 
                            $saved = isset($savings_map[$mem['user_id']]) ? $savings_map[$mem['user_id']] : 0; 
                            
                            // PROFILE PIC LOGIC
                            $pic = isset($mem['profile_pic']) ? $mem['profile_pic'] : 'default.png';
                            $pic_src = "uploads/" . $pic;
                            // Fallback if file doesn't exist
                            if (!file_exists($pic_src) || $pic == 'default.png') {
                                $pic_src = "https://ui-avatars.com/api/?name=" . urlencode($mem['full_name']) . "&background=random";
                            }
                        ?>
                        <li style="padding:15px 0; border-bottom:1px solid rgba(255,255,255,0.05);">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img src="<?php echo $pic_src; ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid var(--primary-color);">
                                    
                                    <div>
                                        <strong><?php echo htmlspecialchars($mem['full_name']); ?></strong>
                                        <?php if($mem['role'] == 'admin') echo " <span style='color:orange; font-size:0.8em;'>★</span>"; ?>
                                        <br>
                                        <span style="color:var(--success-color); font-size:0.9rem; font-weight:bold;">
                                            <?php echo number_format($saved); ?> RWF
                                        </span>
                                    </div>
                                </div>

                                <?php if($is_admin): ?>
                                    <form action="record_payment.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                        <input type="hidden" name="member_id" value="<?php echo $mem['user_id']; ?>"> 
                                        <div style="display:flex; gap:5px; align-items: center;">
                                           <input type="number" name="amount" placeholder="Amt" required style="width:50px; padding:5px; border-radius:4px; border:none;">
                                           <button type="submit" style="background:var(--success-color); color:white; border:none; border-radius:4px; cursor:pointer; padding:5px 8px; font-weight:bold;">+</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>

                <a href="momo_form.php?group_id=<?php echo $group_id; ?>" 
                   style="display:block; background-color: #f4c430; color: black; font-weight: bold; text-decoration: none; padding: 12px; border-radius: 8px; text-align:center; margin-bottom: 15px;">
                   📲 Pay via MoMo
                </a>

                <?php if($is_admin): ?>
                    <div style="border-top: 1px dashed #555; padding-top: 15px;">
                        <p style="font-size:0.8rem; color:#aaa; text-align:center; margin-bottom:10px;">ADMIN CONTROLS</p>
                        
                        <a href="tombola.php?group_id=<?php echo $group_id; ?>" 
                           style="display:block; border: 1px solid var(--primary-color); color: var(--primary-color); font-weight: bold; text-decoration: none; padding: 10px; border-radius: 8px; text-align:center; margin-bottom: 10px;">
                           🎲 Launch Tombola
                        </a>

                        <form action="delete_group.php" method="POST" onsubmit="return confirm('Delete this group forever?');">
                            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                            <button type="submit" style="width: 100%; background-color: #ff4d4d; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                                ❌ End Ikibina
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

            </div> 
            <div class="invite-box">
                <span style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Invite Code</span><br>
                <strong style="font-size:1.5rem; letter-spacing:2px; color:var(--primary-color);">
                    <?php echo $group_info['invite_token']; ?>
                </strong>
            </div>

        </div> 
        
        <div class="chat-area">
            <div class="chat-messages" id="chatBox">
                <?php if ($messages->num_rows > 0): ?>
                    <?php while($msg = $messages->fetch_assoc()): ?>
                        <?php 
                            $is_me = ($msg['user_id'] == $user_id); 
                            $class = $is_me ? "my-message" : "other-message";
                        ?>
                        <div class="message <?php echo $class; ?>">
                            <?php if(!$is_me): ?>
                                <strong style="font-size:0.8em; display:block; margin-bottom:2px; color:var(--primary-color);">
                                    <?php echo htmlspecialchars($msg['full_name']); ?>
                                </strong>
                            <?php endif; ?>
                            
                            <?php echo htmlspecialchars($msg['message_content']); ?>
                            
                            <div class="message-meta">
                                <?php echo date('H:i', strtotime($msg['sent_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align:center; color:var(--text-muted); margin-top:50px;">
                        No messages yet. Say "Muraho"! 👋
                    </p>
                <?php endif; ?>
            </div>

            <form action="" method="POST" class="chat-input-area">
                <input type="text" name="message_content" placeholder="Type a message..." required autocomplete="off" style="flex:1; padding:12px; border-radius:25px; border:1px solid #555; background:var(--bg-color); color:var(--text-color);">
                <button type="submit" class="btn-primary" style="border-radius:50%; width:50px; height:50px; padding:0; display:flex; align-items:center; justify-content:center; margin-left:10px;">➤</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        var chatBox = document.getElementById("chatBox");
        chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>
</html>