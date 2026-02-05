<?php
include 'db.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST['user_id']);
    $new_pass = $_POST['new_pass'];

    // Encrypt the NEW password
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

    $sql = "UPDATE users SET password_hash = '$hashed' WHERE user_id = '$user_id'";
    
    if ($conn->query($sql) === TRUE) {
        $msg = "✅ Password changed successfully! You can now login.";
    } else {
        $msg = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emergency Password Reset</title>
    <style>
        body { font-family: sans-serif; padding: 50px; background: #333; color: white; text-align: center; }
        .box { background: #444; padding: 30px; border-radius: 10px; display: inline-block; }
        input { padding: 10px; margin: 10px 0; width: 90%; border-radius: 5px; border: none; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; }
    </style>
</head>
<body>

    <div class="box">
        <h2>🔑 Force Reset Password</h2>
        <?php if($msg) echo "<p>$msg</p>"; ?>

        <form method="POST">
            <label>User ID (Look at view_data.php):</label><br>
            <input type="number" name="user_id" placeholder="e.g., 1" required><br>

            <label>New Password:</label><br>
            <input type="text" name="new_pass" placeholder="Enter new password" required><br>

            <button type="submit">Change Password</button>
        </form>
    </div>

</body>
</html>