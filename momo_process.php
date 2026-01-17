<?php
session_start();
include 'db.php';

// Check if data was sent
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $group_id = intval($_POST['group_id']);
    $amount = floatval($_POST['amount']);
    $phone = $_POST['phone']; // Just for record, we don't verify it in simulation
    
    // --- SIMULATION DELAY ---
    // Make the user wait 2 seconds so it feels like a real network request
    sleep(2); 

    // 1. Record the Transaction
    // We mark it as 'approved' automatically for this demo.
    // In a real app, this would be 'pending' until the API calls back.
    $sql = "INSERT INTO transactions (group_id, user_id, amount, status, payment_method) 
            VALUES ('$group_id', '$user_id', '$amount', 'approved', 'MoMo')";

    if ($conn->query($sql) === TRUE) {
        
        // 2. Announce it in the Chat!
        // This is the cool part - everyone sees "Alpha just paid!"
        $msg = "📲 Mobile Money Transfer: " . number_format($amount) . " RWF Received from 0" . substr($phone, -9, 3) . "***";
        $conn->query("INSERT INTO chat_messages (group_id, user_id, message_content) VALUES ('$group_id', '$user_id', '$msg')");

        // 3. Redirect back to Chat with success
        header("Location: group_chat.php?id=$group_id&payment=success");
        exit();

    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: dashboard.php");
}
?>