<?php
session_start();
include 'db.php';

// If user is not logged in, send them to login
// But we save the token in the session so they are redirected back here after login!
if (!isset($_SESSION['user_id'])) {
    if(isset($_GET['token'])) {
        $_SESSION['redirect_after_login'] = "join.php?token=" . $_GET['token'];
    }
    header("Location: login.php");
    exit();
}

$message = "";
$group_name = "";
$group_id = "";

// 1. Check if token exists in URL
if (isset($_GET['token'])) {
    $raw_token = $_GET['token'];

// If user pasted the full URL, clean it to get just the code
if (strpos($raw_token, 'token=') !== false) {
    $parts = explode('token=', $raw_token);
    $raw_token = end($parts);
}

$token = $conn->real_escape_string($raw_token);
    
    // Find the group
    $sql = "SELECT group_id, group_name, contribution_amount FROM savings_groups WHERE invite_token = '$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $group = $result->fetch_assoc();
        $group_name = $group['group_name'];
        $group_id = $group['group_id'];
        $amount = $group['contribution_amount'];

        // 2. Handle the "Yes, Join" click
        if (isset($_POST['join_confirm'])) {
            $user_id = $_SESSION['user_id'];
            
            // Check if already a member
            $check = $conn->query("SELECT * FROM group_members WHERE group_id='$group_id' AND user_id='$user_id'");
            if($check->num_rows == 0) {
                // Add them!
                $conn->query("INSERT INTO group_members (group_id, user_id, role) VALUES ('$group_id', '$user_id', 'member')");
                header("Location: dashboard.php"); // Success, go to dashboard
                exit();
            } else {
                $message = "You are already in this group!";
            }
        }

    } else {
        $message = "Invalid or expired invite link.";
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Group</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
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

    <div class="form-container" style="text-align:center;">
        <?php if ($group_name): ?>
            <h2>You've been invited!</h2>
            <p>Do you want to join the group:</p>
            <h1 style="color:var(--primary-color);"><?php echo htmlspecialchars($group_name); ?></h1>
            <p>Contribution: <strong><?php echo number_format($amount); ?> RWF</strong></p>
            
            <?php if($message) echo "<p style='color:red;'>$message</p>"; ?>

            <form method="POST">
                <button type="submit" name="join_confirm" class="btn-primary" style="width:100%; border:none; cursor:pointer; font-size:1.1rem;">
                    Yes, Join Group
                </button>
            </form>
            <br>
            <a href="dashboard.php" style="color:var(--text-muted);">No, cancel</a>

        <?php else: ?>
            <h2 style="color:red;">Error</h2>
            <p><?php echo $message; ?></p>
            <a href="dashboard.php" class="btn-primary">Go Home</a>
        <?php endif; ?>
    </div>
    
    <script src="script.js"></script>
</body>
</html>