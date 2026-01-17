<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['group_id'])) {
    header("Location: dashboard.php");
    exit();
}

$group_id = intval($_POST['group_id']);
$user_id = $_SESSION['user_id'];

// 1. Verify Admin Status (CRITICAL SECURITY)
$check = $conn->query("SELECT admin_id FROM savings_groups WHERE group_id = '$group_id'");
$group = $check->fetch_assoc();

if ($group['admin_id'] == $user_id) {
    
    // 2. DELETE EVERYTHING (Order matters due to Foreign Keys)
    
    // A. Delete Payments/Transactions
    $conn->query("DELETE FROM transactions WHERE group_id = '$group_id'");
    
    // B. Delete Chat Messages
    $conn->query("DELETE FROM chat_messages WHERE group_id = '$group_id'");
    
    // C. Remove Members
    $conn->query("DELETE FROM group_members WHERE group_id = '$group_id'");
    
    // D. Finally, Delete the Group
    $conn->query("DELETE FROM savings_groups WHERE group_id = '$group_id'");

    // 3. Success
    header("Location: dashboard.php?msg=deleted");
    exit();

} else {
    die("Only the Admin can end the Ikibina.");
}
?>