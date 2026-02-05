<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['group_id'])) {
    header("Location: dashboard.php");
    exit();
}

$group_id = intval($_GET['group_id']);
$user_id = $_SESSION['user_id'];
$message = "";

// 1. VERIFY ADMIN (Security)
$group = $conn->query("SELECT * FROM savings_groups WHERE group_id = '$group_id'")->fetch_assoc();
if ($group['admin_id'] != $user_id) {
    die("Access Denied. Only Admin can manage loans.");
}

// 2. HANDLE APPROVE / REJECT
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_id = intval($_POST['loan_id']);
    $action = $_POST['action']; // 'approve' or 'reject'
    
    // Fetch loan details first
    $loan = $conn->query("SELECT * FROM loan_requests WHERE loan_id='$loan_id'")->fetch_assoc();
    $amount = $loan['amount'];
    $borrower_id = $loan['user_id'];

    if ($action == 'approve') {
        // A. Check if pot has enough money
        $pot = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE group_id='$group_id'")->fetch_assoc();
        $current_pot = $pot['total'] ? $pot['total'] : 0;

        if ($current_pot < $amount) {
            $message = "❌ Not enough money in the pot!";
        } else {
            // B. Approve: Update Status
            $conn->query("UPDATE loan_requests SET status='active' WHERE loan_id='$loan_id'");

            // C. Subtract Money from Pot (Record as Negative Transaction)
            // We tag it as 'Loan Payout' so we know why money left
            $neg_amount = 0 - $amount;
            $conn->query("INSERT INTO transactions (group_id, user_id, amount, status, payment_method) VALUES ('$group_id', '$borrower_id', '$neg_amount', 'approved', 'Loan Payout')");

            $message = "✅ Loan Approved! Money sent to user.";
        }

    } elseif ($action == 'reject') {
        $conn->query("UPDATE loan_requests SET status='rejected' WHERE loan_id='$loan_id'");
        $message = "Loan rejected.";
    }
}

// 3. FETCH PENDING REQUESTS
$sql = "SELECT l.*, u.full_name, u.profile_pic FROM loan_requests l 
        JOIN users u ON l.user_id = u.user_id 
        WHERE l.group_id = '$group_id' AND l.status = 'pending' 
        ORDER BY l.request_date ASC";
$requests = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Loans</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <div class="logo">Ikibina<span style="color:var(--success-color)">.rw</span></div>
        <div class="nav-links">
            <a href="group_home.php?id=<?php echo $group_id; ?>">Back</a>
        </div>
    </nav>

    <div class="form-container" style="max-width: 700px;">
        <h2>🏦 Loan Requests</h2>
        <p style="color:var(--text-muted);">Approve or Reject member requests.</p>
        
        <?php if($message) echo "<p style='text-align:center; font-weight:bold; color:var(--primary-color);'>$message</p>"; ?>

        <div style="margin-top:20px;">
            <?php if ($requests->num_rows > 0): ?>
                <?php while($r = $requests->fetch_assoc()): ?>
                    <div class="card" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-left: 4px solid orange;">
                        
                        <div style="display:flex; gap:15px; align-items:center;">
                            <?php 
                                $pic = $r['profile_pic'] ?: 'default.png';
                                $img = (file_exists("uploads/$pic") && $pic != 'default.png') ? "uploads/$pic" : "https://ui-avatars.com/api/?name=".$r['full_name'];
                            ?>
                            <img src="<?php echo $img; ?>" style="width:50px; height:50px; border-radius:50%;">
                            <div>
                                <h3 style="margin:0; font-size:1.1rem;"><?php echo htmlspecialchars($r['full_name']); ?></h3>
                                <p style="margin:5px 0 0 0; color:var(--text-muted);">
                                    Wants: <strong style="color:var(--text-color);"><?php echo number_format($r['amount']); ?> RWF</strong>
                                </p>
                                <small>Date: <?php echo date('M d', strtotime($r['request_date'])); ?></small>
                            </div>
                        </div>

                        <div style="display:flex; gap:10px; flex-direction:column;">
                            <form method="POST">
                                <input type="hidden" name="loan_id" value="<?php echo $r['loan_id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn-primary" style="background:var(--success-color); border:none; width:100%; cursor:pointer;">Approve</button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="loan_id" value="<?php echo $r['loan_id']; ?>">
                                <button type="submit" name="action" value="reject" class="btn-primary" style="background:#ff4d4d; border:none; width:100%; cursor:pointer;">Reject</button>
                            </form>
                        </div>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center; padding:40px; border:2px dashed #ccc; border-radius:10px;">
                    <h3>No pending requests</h3>
                    <p>When members ask for money, they will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="loader-wrapper"><div class="loader-spinner"></div></div>
    <script src="script.js"></script>
</body>
</html>