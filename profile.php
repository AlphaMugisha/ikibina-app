<?php
session_start();
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

// HANDLE FILE UPLOAD & UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // 1. Handle Image Upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir); // Create folder if not exists
        
        // Generate unique name (e.g., "5_timestamp.jpg")
        $filename = $user_id . "_" . time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $filename;
        
        // Save file
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            // Update DB
            $conn->query("UPDATE users SET profile_pic = '$filename' WHERE user_id = '$user_id'");
        } else {
            $error = "Failed to upload image.";
        }
    }

    // 2. Update Text Info
    $sql = "UPDATE users SET full_name = '$full_name', phone_number = '$phone' WHERE user_id = '$user_id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['name'] = $full_name;
        $message = "Profile updated successfully!";
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
}

// FETCH CURRENT USER DATA
$user = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="loader-wrapper">
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

    <div class="form-container" style="max-width:500px;">
        <h2 style="text-align:center;">My Profile</h2>

        <?php if($message) echo "<p style='color:green; text-align:center; background:#d4edda; padding:10px; border-radius:5px;'>$message</p>"; ?>
        <?php if($error) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>

        <div style="text-align:center; margin-bottom:20px;">
            <?php 
                $img_src = "uploads/" . $user['profile_pic'];
                if ($user['profile_pic'] == 'default.png' || !file_exists($img_src)) {
                    $img_src = "https://ui-avatars.com/api/?name=" . urlencode($user['full_name']) . "&size=128&background=random";
                }
            ?>
            <img src="<?php echo $img_src; ?>" alt="Profile" 
                 style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid var(--primary-color); box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        </div>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Change Profile Picture</label>
                <input type="file" name="profile_pic" accept="image/*" style="padding:10px; background:var(--bg-color);">
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer;">Save Changes</button>
        </form>
    </div>

    <script src="script.js"></script>
</body>
</html>