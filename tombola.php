<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['group_id'])) {
    header("Location: dashboard.php");
    exit();
}

$group_id = intval($_GET['group_id']);
$user_id = $_SESSION['user_id'];

// 1. Verify Admin Status
$group = $conn->query("SELECT * FROM savings_groups WHERE group_id = '$group_id'")->fetch_assoc();
if ($group['admin_id'] != $user_id) {
    die("Only the Admin can manage payouts.");
}

// 2. Calculate Total Pot (Sum of all deposits)
// We only sum positive amounts (deposits), ignoring previous payouts (negatives)
$pot_query = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE group_id = '$group_id' AND amount > 0");
$pot_data = $pot_query->fetch_assoc();
$current_pot = $pot_data['total'] ? $pot_data['total'] : 0;

// 3. Get All Members (for the random picker)
$members = [];
$mem_sql = $conn->query("SELECT u.user_id, u.full_name FROM group_members gm JOIN users u ON gm.user_id = u.user_id WHERE gm.group_id = '$group_id'");
while($row = $mem_sql->fetch_assoc()) {
    $members[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tombola - Payout</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Animation for the winner selection */
        @keyframes highlight {
            0% { transform: scale(1); background: var(--bg-color); }
            50% { transform: scale(1.1); background: var(--accent-color); color: black; }
            100% { transform: scale(1); background: var(--bg-color); }
        }
        .highlighted {
            animation: highlight 0.2s ease-in-out;
            background: var(--accent-color) !important;
            color: black !important;
            font-weight: bold;
        }
        .winner-card {
            border: 2px solid var(--success-color);
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div id="loader-wrapper">

</div>

    <nav>
        <div class="logo">Ikibina<span style="color:var(--success-color)">.rw</span></div>
        <div class="nav-links">
            <a href="group_chat.php?id=<?php echo $group_id; ?>">Back to Chat</a>
        </div>
    </nav>

    <div class="form-container" style="max-width: 600px; text-align:center;">
        
        <h2>💰 The Money Pot</h2>
        <div style="font-size: 3rem; font-weight: bold; color: var(--success-color); margin: 20px 0;">
            <?php echo number_format($current_pot); ?> RWF
        </div>
        <p style="color:var(--text-muted);">Ready to payout? Pick a winner below.</p>

        <hr style="margin: 30px 0; opacity: 0.3;">

        <h3>🎲 Who takes the pot?</h3>
        
        <div id="memberGrid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:10px; margin: 20px 0;">
            <?php foreach($members as $m): ?>
                <div class="member-card" id="mem_<?php echo $m['user_id']; ?>" 
                     style="padding:15px; border:1px solid #ccc; border-radius:8px; cursor:pointer;"
                     onclick="selectManual(<?php echo $m['user_id']; ?>, '<?php echo addslashes($m['full_name']); ?>')">
                    <?php echo htmlspecialchars($m['full_name']); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <button onclick="startTombola()" class="btn-primary" style="background-color:var(--primary-color); width:100%; font-size:1.2rem; margin-bottom:10px;">
            🔄 Spin the Wheel (Random)
        </button>

        <div id="payoutSection" style="display:none; margin-top:30px; border-top: 2px dashed #ccc; padding-top:20px;">
            <h3>Confirm Payout</h3>
            <p>Send <strong><?php echo number_format($current_pot); ?> RWF</strong> to:</p>
            <h2 id="winnerText" style="color:var(--primary-color);">...</h2>
            
            <form action="process_payout.php" method="POST">
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                <input type="hidden" name="amount" value="<?php echo $current_pot; ?>">
                <input type="hidden" name="winner_id" id="winnerIdInput">
                
                <button type="submit" class="btn-primary" style="background-color:var(--success-color); width:100%;">
                    ✅ Confirm Transfer
                </button>
            </form>
        </div>

    </div>

    <script src="script.js"></script>
    <script>
        // Pass PHP array to JS
        const members = <?php echo json_encode($members); ?>;
        
        function startTombola() {
            let rounds = 0;
            const maxRounds = 20; // How many times it flashes
            const speed = 100; // Speed of flash

            document.getElementById('payoutSection').style.display = 'none';

            let interval = setInterval(() => {
                // Remove highlight from all
                document.querySelectorAll('.member-card').forEach(d => d.classList.remove('highlighted'));
                
                // Pick random
                const random = members[Math.floor(Math.random() * members.length)];
                const el = document.getElementById('mem_' + random.user_id);
                el.classList.add('highlighted');

                rounds++;
                if(rounds >= maxRounds) {
                    clearInterval(interval);
                    selectManual(random.user_id, random.full_name); // Finish on this person
                }
            }, speed);
        }

        function selectManual(id, name) {
            // Remove previous highlights
            document.querySelectorAll('.member-card').forEach(d => d.classList.remove('winner-card'));
            document.querySelectorAll('.member-card').forEach(d => d.classList.remove('highlighted'));

            // Highlight selected
            document.getElementById('mem_' + id).classList.add('winner-card');

            // Show form
            document.getElementById('payoutSection').style.display = 'block';
            document.getElementById('winnerText').innerText = name;
            document.getElementById('winnerIdInput').value = id;
        }
    </script>
</body>
</html>