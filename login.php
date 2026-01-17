<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE phone_number = '$phone'");
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify Password
        if (password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['name'] = $user['full_name'];
    
    // --- NEW REDIRECT LOGIC ---
    if (isset($_SESSION['redirect_after_login'])) {
        $link = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']); // Clear it so it doesn't happen again
        header("Location: " . $link);
    } else {
        header("Location: dashboard.php");
    }
    // --------------------------
    exit();
} else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Account not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ikibina</title>
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
            <a href="index.php">Home</a>
            <a href="register.php">Register</a>
            <button id="theme-toggle">🌗 Mode</button>
        </div>
    </nav>

    <div class="form-container">
        <h2 style="text-align:center;">Welcome Back</h2>
        <?php if($error) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="078..." required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer;">Log In</button>
        </form>
    </div>

    <script src="script.js"></script>
</body>
</html>