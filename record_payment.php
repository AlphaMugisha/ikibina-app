<?php
session_start();
include 'db.php';

// Security: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_SESSION['user_id'];
    $group_id = intval($_POST['group_id']);
    $member_id = intval($_POST['member_id']);
    $amount = floatval($_POST['amount']);

    // 1. VERIFY: Is the current user actually the Admin of this group?
    // We don't want random members adding fake money!
    $check_admin = $conn->query("SELECT admin_id FROM savings_groups WHERE group_id = '$group_id'");
    $group = $check_admin->fetch_assoc();

    if ($group && $group['admin_id'] == $admin_id) {
        
        // 2. RECORD THE TRANSACTION
        $sql = "INSERT INTO transactions (group_id, user_id, amount, status) 
                VALUES ('$group_id', '$member_id', '$amount', 'approved')";
        
        if ($conn->query($sql) === TRUE) {
            
            // 3. AUTO-POST TO CHAT (Cool Feature)
            // Let the whole group know money was received!
            $system_msg = "💰 Payment Received: " . number_format($amount) . " RWF";
            $conn->query("INSERT INTO chat_messages (group_id, user_id, message_content) VALUES ('$group_id', '$member_id', '$system_msg')");

            header("Location: group_chat.php?id=$group_id&msg=saved");
        } else {
            echo "Error: " . $conn->error;
        }

    } else {
        die("❌ You are not the Admin. You cannot record payments.");
    }
}
?>