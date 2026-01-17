<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Security check
    if(!isset($_SESSION['user_id']) || !isset($_POST['group_id'])) die("Access Denied");

    $admin_id = $_SESSION['user_id'];
    $group_id = intval($_POST['group_id']);
    $winner_id = intval($_POST['winner_id']);
    $amount = floatval($_POST['amount']); // This is the positive pot amount

    // Double check admin
    $check = $conn->query("SELECT admin_id FROM savings_groups WHERE group_id='$group_id'")->fetch_assoc();
    if($check['admin_id'] != $admin_id) die("Not authorized");

    // 1. Record the Payout (AS A NEGATIVE NUMBER)
    // We use negative to subtract from the group's global pot
    $negative_amount = 0 - $amount; 
    
    // Note: We assign this transaction to the WINNER so we know who took it
    $sql = "INSERT INTO transactions (group_id, user_id, amount, status, payment_method) 
            VALUES ('$group_id', '$winner_id', '$negative_amount', 'approved', 'Payout')";

    if ($conn->query($sql) === TRUE) {
        
        // 2. Fetch Winner Name for Chat
        $winner_query = $conn->query("SELECT full_name FROM users WHERE user_id='$winner_id'")->fetch_assoc();
        $winner_name = $winner_query['full_name'];

        // 3. Announce in Chat
        $msg = "🎉 TOMBOLA! The pot of " . number_format($amount) . " RWF has been paid out to: " . $winner_name . "!";
        $msg = $conn->real_escape_string($msg);
        
        $conn->query("INSERT INTO chat_messages (group_id, user_id, message_content) VALUES ('$group_id', '$admin_id', '$msg')");

        // 4. Back to Chat
        header("Location: group_chat.php?id=$group_id&payout=success");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>