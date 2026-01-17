<?php
session_start();
include 'db.php';

// Security: Kick them out if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// FETCH GROUPS THIS USER IS IN
// We join 'savings_groups' with 'group_members'
$sql = "SELECT g.group_id, g.group_name, g.contribution_amount, g.frequency, m.role 
        FROM savings_groups g 
        JOIN group_members m ON g.group_id = m.group_id 
        WHERE m.user_id = '$user_id'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard</title>
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
<a href="profile.php" style="margin-right:15px; font-weight:bold; text-decoration:none; color:var(--text-color); border-bottom:2px solid var(--primary-color);">
    Muraho, <?php echo htmlspecialchars($user_name); ?>
</a>
            <a href="logout.php" style="color:red;">Logout</a>
            <button id="theme-toggle">🌗 Mode</button>
        </div>
    </nav>

    <div style="padding: 40px 10%;">
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h1>My Savings Groups</h1>
            <div>
                <a href="create_group.php" class="btn-primary">+ Create Group</a>
                <a href="join_group.php" class="btn-primary" style="background-color:#666; margin-left:10px;">Join via Code</a>
            </div>
        </div>

        <div class="info-section" style="padding:0; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
            
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card" style="text-align:left;">
                        <h3><?php echo htmlspecialchars($row['group_name']); ?></h3>
                        <p style="color:var(--text-muted); font-size:0.9rem;">
                            Contribution: <strong><?php echo number_format($row['contribution_amount']); ?> RWF</strong><br>
                            Frequency: <?php echo ucfirst($row['frequency']); ?><br>
                            My Role: <span style="color:var(--primary-color);"><?php echo ucfirst($row['role']); ?></span>
                        </p>
                        
                        <a href="group_home.php?id=<?php echo $row['group_id']; ?>" class="btn-primary" style="display:inline-block; margin-top:10px; font-size:0.9rem;">Open Group</a>
                    
                    </div>
                <?php endwhile; ?>
            
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align:center; padding:50px; border:2px dashed #ccc; border-radius:10px;">
                    <h3>You aren't in any groups yet.</h3>
                    <p>Create one or ask a friend for an invite link!</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>