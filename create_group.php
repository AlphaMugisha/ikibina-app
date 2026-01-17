<?php
session_start();
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success_token = ""; // Variable to hold just the code

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = $conn->real_escape_string($_POST['group_name']);
    $amount = $_POST['amount'];
    $frequency = $_POST['frequency'];
    $admin_id = $_SESSION['user_id'];

    // Generate random 6 character code
    $token = bin2hex(random_bytes(3)); 

    $sql = "INSERT INTO savings_groups (group_name, admin_id, contribution_amount, frequency, invite_token) 
            VALUES ('$group_name', '$admin_id', '$amount', '$frequency', '$token')";

    if ($conn->query($sql) === TRUE) {
        $new_group_id = $conn->insert_id;
        // Add Admin as Member
        $conn->query("INSERT INTO group_members (group_id, user_id, role) VALUES ('$new_group_id', '$admin_id', 'admin')");
        
        // Save the token to display it
        $success_token = $token;
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Ikibina</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="loader-wrapper">
    <div class="loader-spinner"></div>
</div>

    <nav>
        <div class="logo">Ikibina<span style="color:var(--success-color)">.rw</span></div>
        <div class="nav-links">
            <a href="dashboard.php">Back to Dashboard</a>
            <button id="theme-toggle">🌗 Mode</button>
        </div>
    </nav>

    <div class="form-container">
        
        <?php if($success_token): ?>
            <div style="text-align:center; animation: fadeIn 0.5s;">
                <h2 style="color:var(--success-color);">Group Created! 🎉</h2>
                <p>Share this code with your friends:</p>
                
                <div style="background:var(--bg-color); padding:20px; border:2px dashed var(--primary-color); margin:20px 0; border-radius:10px;">
                    <span style="display:block; font-size:0.9rem; color:var(--text-muted); margin-bottom:5px;">GROUP CODE</span>
                    <strong style="font-size:2.5rem; letter-spacing:5px; color:var(--text-color);"><?php echo $success_token; ?></strong>
                </div>

                <p style="font-size:0.9rem;">Or copy the full link:</p>
                <div style="background:#f0f0f0; padding:10px; border-radius:5px; font-size:0.8rem; word-break:break-all; color:#333;">
                    http://localhost/ikibina/join.php?token=<?php echo $success_token; ?>
                </div>

                <br>
                <a href="dashboard.php" class="btn-primary">Done</a>
            </div>

        <?php else: ?>
            <h2 style="text-align:center;">Start a New Savings Group</h2>
            <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>

            <form action="create_group.php" method="POST">
                <div class="form-group">
                    <label>Group Name</label>
                    <input type="text" name="group_name" placeholder="e.g. Kigali Family Savings" required>
                </div>

                <div class="form-group">
                    <label>Contribution Amount (RWF)</label>
                    <input type="number" name="amount" placeholder="e.g. 5000" required>
                </div>

                <div class="form-group">
                    <label>Frequency</label>
                    <select name="frequency" style="width:100%; padding:10px; border-radius:5px;">
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="daily">Daily</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer;">Create Group</button>
            </form>
        <?php endif; ?>

    </div>
    <script src="script.js"></script>
</body>
</html>