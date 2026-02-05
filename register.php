<?php
session_start();
include 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];

    // --- 1. STRONG PASSWORD ENFORCEMENT (The Security Logic) ---
    
    // Check length (Min 8)
    if (strlen($password) < 8) {
        $error = "Password is too short! It must be at least 8 characters.";
    }
    // Check for at least one number
    elseif (!preg_match("#[0-9]+#", $password)) {
        $error = "Password must include at least one number!";
    }
    // Check for at least one uppercase letter
    elseif (!preg_match("#[A-Z]+#", $password)) {
        $error = "Password must include at least one UPPERCASE letter!";
    }
    else {
        // --- SECURITY CHECK PASSED ---

        // Check if phone already exists
        $check = $conn->query("SELECT user_id FROM users WHERE phone_number = '$phone'");
        if ($check->num_rows > 0) {
            $error = "This phone number is already registered!";
        } else {
            // Re-enable Hashing for Security (Best Practice)
            // If you really want plain text, remove 'password_hash' function, but I don't recommend it!
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (full_name, phone_number, password_hash) VALUES ('$fullname', '$phone', '$hashed_password')";
            
            if ($conn->query($sql) === TRUE) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['name'] = $fullname;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
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
    <style>
        /* Password Strength Bar Styles */
        .strength-bar {
            height: 5px;
            width: 0%;
            background: red;
            transition: width 0.3s, background 0.3s;
            margin-top: 5px;
            border-radius: 3px;
        }
        .strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>

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
        
        <?php if($error) echo "<div style='background:#ffeaea; color:#d63384; padding:10px; border-radius:5px; text-align:center; margin-bottom:15px; border:1px solid #f5c6cb;'>⚠️ $error</div>"; ?>
        
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
                <input type="password" name="password" id="passInput" placeholder="******" required onkeyup="checkStrength()">
                
                <div class="strength-bar" id="strengthBar"></div>
                <div class="strength-text" id="strengthText"></div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer;">Register</button>
        </form>
        
        <p style="margin-top:20px; text-align:center;">
            Already have an account? <a href="login.php" style="color:var(--primary-color);">Log In</a>
        </p>
    </div>

    <script src="script.js"></script>
    
    <script>
        // CLIENT-SIDE PASSWORD CHECKER
        function checkStrength() {
            var input = document.getElementById("passInput");
            var bar = document.getElementById("strengthBar");
            var text = document.getElementById("strengthText");
            var val = input.value;
            var strength = 0;

            // 1. Length Check
            if (val.length >= 8) strength += 1;
            // 2. Number Check
            if (val.match(/[0-9]/)) strength += 1;
            // 3. Uppercase Check
            if (val.match(/[A-Z]/)) strength += 1;
            // 4. Special Char Check (Bonus)
            if (val.match(/[^a-zA-Z0-9]/)) strength += 1;

            // Update UI based on score
            if (val.length === 0) {
                bar.style.width = "0%";
                text.innerHTML = "";
            } else if (val.length < 8) {
                bar.style.width = "20%";
                bar.style.background = "#dc3545"; // Red
                text.innerHTML = "<span style='color:#dc3545'>Too Short</span>";
            } else if (strength < 3) {
                bar.style.width = "60%";
                bar.style.background = "#ffc107"; // Yellow
                text.innerHTML = "<span style='color:#ffc107'>Weak</span>";
            } else {
                bar.style.width = "100%";
                bar.style.background = "#28a745"; // Green
                text.innerHTML = "<span style='color:#28a745'>Strong!</span>";
            }
        }
    </script>
</body>
</html>