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

// HANDLE REQUEST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']);
    $reason = $conn->real_escape_string($_POST['reason']);

    // Check if they already have a pending/active loan
    $check = $conn->query("SELECT * FROM loan_requests WHERE group_id='$group_id' AND user_id='$user_id' AND status IN ('pending', 'active')");
    
    if ($check->num_rows > 0) {
        $message = "❌ You already have an active or pending loan!";
    } else {
        // Create Request
        $sql = "INSERT INTO loan_requests (group_id, user_id, amount) VALUES ('$group_id', '$user_id', '$amount')";
        if ($conn->query($sql) === TRUE) {
            // Notify in Chat
            $msg = "📄 Loan Request: User requested " . number_format($amount) . " RWF.";
            $conn->query("INSERT INTO chat_messages (group_id, user_id, message_content) VALUES ('$group_id', '$user_id', '$msg')");
            
            header("Location: group_home.php?id=$group_id&msg=loan_requested");
            exit();
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Loan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <div class="logo">Ikibina<span style="color:var(--success-color)">.rw</span></div>
        <div class="nav-links">
            <a href="group_home.php?id=<?php echo $group_id; ?>">Cancel</a>
        </div>
    </nav>

    <div class="form-container">
        <h2 style="text-align:center;">Request a Loan (Inguzanyo)</h2>
        <p style="text-align:center; color:var(--text-muted);">Borrow from the group pot.</p>

        <?php if($message) echo "<p style='color:red; text-align:center;'>$message</p>"; ?>

        <form method="POST">
            <div class="form-group">
                <label>Amount (RWF)</label>
                <input type="number" name="amount" placeholder="e.g. 10000" required>
            </div>

            <div class="form-group">
                <label>Reason (Optional)</label>
                <input type="text" name="reason" placeholder="e.g. School fees, Emergency...">
            </div>

            <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer;">Submit Request</button>
        </form>
    </div>
    
    <script src="script.js"></script>
</body>
</html>