<?php
session_start();
include 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];

    // 1. Check if phone already exists
    $check = $conn->query("SELECT user_id FROM users WHERE phone_number = '$phone'");
    if ($check->num_rows > 0) {
        $error = "This phone number is already registered!";
    } else {
        // 2. Hash the password (Security First!)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert User
        $sql = "INSERT INTO users (full_name, phone_number, password_hash) VALUES ('$fullname', '$phone', '$hashed_password')";
        
        if ($conn->query($sql) === TRUE) {
            // Auto-login after register
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['name'] = $fullname;
            header("Location: dashboard.php"); // Send them to the main app
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Ikibina</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="loader-wrapper">
</div>

    <nav>
        <div class="logo">Ikibina<span style="color:var(--success-color)">.rw</span></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php">Log In</a>
            <button id="theme-toggle">🌗 Mode</button>
        </div>
    </nav>

    <div class="form-container">
        <h2 style="text-align:center;">Create Account</h2>
        <p style="text-align:center; color:var(--text-muted);">Start saving with your friends.</p>
        
        <form action="register.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="e.g. Keza Amata" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="078..." required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="******" required>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer;">Register</button>
        </form>
        
        <p style="margin-top:20px; text-align:center;">
            Already have an account? <a href="login.php" style="color:var(--primary-color);">Log In</a>
        </p>
    </div>

    <script src="script.js"></script>
</body>
</html>