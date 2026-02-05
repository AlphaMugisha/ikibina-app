<?php
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Inspector</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        h2 { border-bottom: 3px solid #007bff; display: inline-block; padding-bottom: 5px; color: #333; margin-top: 40px;}
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .hash { font-family: monospace; font-size: 0.8em; color: #d63384; }
    </style>
</head>
<body>

    <h1>🗄️ Database Debugger</h1>
    <p>Here is all the raw data currently stored in your system.</p>

    <h2>1. Users Table</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Password Hash (Encrypted)</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM users";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['user_id']}</td>
                        <td>{$row['full_name']}</td>
                        <td>{$row['phone_number']}</td>
                        <td class='hash'>{$row['password_hash']}</td>
                        <td>{$row['created_at']}</td>
                    </tr>";
                }
            } else { echo "<tr><td colspan='5'>No users found</td></tr>"; }
            ?>
        </tbody>
    </table>

    <h2>2. Savings Groups</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Group Name</th>
                <th>Admin ID</th>
                <th>Contribution</th>
                <th>Invite Code</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM savings_groups";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['group_id']}</td>
                        <td>{$row['group_name']}</td>
                        <td>{$row['admin_id']}</td>
                        <td>" . number_format($row['contribution_amount']) . " RWF</td>
                        <td style='font-weight:bold; color:green;'>{$row['invite_token']}</td>
                    </tr>";
                }
            } else { echo "<tr><td colspan='5'>No groups found</td></tr>"; }
            ?>
        </tbody>
    </table>

    <h2>3. Group Members</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Group ID</th>
                <th>User ID</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM group_members";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['group_id']}</td>
                        <td>{$row['user_id']}</td>
                        <td>{$row['role']}</td>
                    </tr>";
                }
            } else { echo "<tr><td colspan='4'>No members found</td></tr>"; }
            ?>
        </tbody>
    </table>

    <h2>4. Transactions (Money)</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Group</th>
                <th>User</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM transactions";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $color = $row['amount'] < 0 ? 'red' : 'green';
                    echo "<tr>
                        <td>{$row['transaction_id']}</td>
                        <td>{$row['group_id']}</td>
                        <td>{$row['user_id']}</td>
                        <td style='color:$color; font-weight:bold;'>" . number_format($row['amount']) . "</td>
                        <td>{$row['status']}</td>
                    </tr>";
                }
            } else { echo "<tr><td colspan='5'>No transactions found</td></tr>"; }
            ?>
        </tbody>
    </table>

</body>
</html>