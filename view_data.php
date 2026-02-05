<?php
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Database View</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 20px; }
        h1 { text-align: center; color: #333; }
        .table-container { overflow-x: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 10px; background: white; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; position: sticky; top: 0; }
        tr:hover { background-color: #f1f1f1; }
        
        /* Type Badges */
        .badge { padding: 5px 10px; border-radius: 15px; color: white; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
        .type-user { background-color: #6c757d; } /* Grey */
        .type-group { background-color: #17a2b8; } /* Teal */
        .type-chat { background-color: #28a745; } /* Green */
        .type-money { background-color: #ffc107; color: black; } /* Yellow */
        .type-member { background-color: #007bff; } /* Blue */

        /* Password Field */
        .hidden-pass { font-family: monospace; color: #dc3545; background: #ffeaea; padding: 2px 5px; border-radius: 4px; font-size: 0.8em; }
    </style>
</head>
<body>

    <h1>🗄️ One Table To Rule Them All</h1>
    <p style="text-align:center; margin-bottom: 30px;">Every action in your database, sorted by time.</p>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date / Time</th>
                    <th>Related Name/Group</th>
                    <th>Details / Content</th>
                    <th>Raw Data (ID/Hash/Amount)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // WE USE 'UNION ALL' TO COMBINE DIFFERENT TABLES
                
                // 1. Get Users
                $sql = "SELECT 'User Joined' as type, created_at as date, full_name as main_info, phone_number as sub_info, CONCAT('ID: ', user_id, ' | Pass: ', password_hash) as raw_data FROM users
                
                UNION ALL
                
                -- 2. Get Groups Created
                SELECT 'Group Created' as type, created_at as date, group_name as main_info, CONCAT('Freq: ', frequency) as sub_info, CONCAT('ID: ', group_id, ' | Admin: ', admin_id) as raw_data FROM savings_groups
                
                UNION ALL
                
                -- 3. Get Transactions (Money)
                SELECT 'Money Transaction' as type, payment_date as date, CONCAT('User ID: ', user_id) as main_info, status as sub_info, CONCAT(amount, ' RWF | Method: ', payment_method) as raw_data FROM transactions
                
                UNION ALL

                -- 4. Get Chat Messages
                SELECT 'Chat Message' as type, sent_at as date, CONCAT('User ID: ', user_id) as main_info, message_content as sub_info, CONCAT('Group ID: ', group_id) as raw_data FROM chat_messages
                
                UNION ALL

                -- 5. Get Members Joining
                SELECT 'Member Joined' as type, joined_at as date, CONCAT('User ID: ', user_id) as main_info, role as sub_info, CONCAT('Group ID: ', group_id) as raw_data FROM group_members
                
                -- ORDER EVERYTHING BY DATE (Newest First)
                ORDER BY date DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        
                        // Set badge color based on type
                        $badgeClass = 'type-user';
                        if(strpos($row['type'], 'Group') !== false) $badgeClass = 'type-group';
                        if(strpos($row['type'], 'Money') !== false) $badgeClass = 'type-money';
                        if(strpos($row['type'], 'Chat') !== false) $badgeClass = 'type-chat';
                        if(strpos($row['type'], 'Member') !== false) $badgeClass = 'type-member';

                        echo "<tr>
                            <td><span class='badge $badgeClass'>{$row['type']}</span></td>
                            <td>{$row['date']}</td>
                            <td><strong>{$row['main_info']}</strong></td>
                            <td>{$row['sub_info']}</td>
                            <td style='font-family:monospace; font-size:0.9em; color:#555;'>{$row['raw_data']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Database is empty!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>