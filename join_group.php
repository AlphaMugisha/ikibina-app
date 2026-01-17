<?php
session_start();

// Security: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Group</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="loader-wrapper">

</div>

    <nav>
        <div class="logo">Ikibina<span style="color:var(--success-color)">.rw</span></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <button id="theme-toggle">🌗 Mode</button>
        </div>
    </nav>

    <div class="form-container" style="text-align:center;">
        <h2>Join an Ikibina</h2>
        <p style="color:var(--text-muted);">Enter the unique invite code shared by your group admin.</p>
        
        <form action="join.php" method="GET">
            <div class="form-group">
                <label style="text-align:left; display:block;">Invite Code</label>
                <input type="text" name="token" placeholder="e.g. 8f7d2a" required 
                       style="text-align:center; letter-spacing: 3px; font-size: 1.2rem; text-transform:lowercase;">
            </div>

            <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer;">
                Find Group
            </button>
        </form>

        <p style="margin-top:20px; font-size:0.9rem; color:var(--text-muted);">
            Don't have a code? <a href="create_group.php" style="color:var(--primary-color);">Create your own group</a>
        </p>
    </div>

    <script src="script.js"></script>
</body>
</html>