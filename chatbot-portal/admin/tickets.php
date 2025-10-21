<?php
require_once __DIR__ . '/../includes/db_connect.php';
// TODO: enforce admin auth here using session role checks
$res = $conn->query('SELECT t.*, u.name as user_name FROM tickets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC');
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Admin - Tickets</title><link rel="stylesheet" href="../assets/styles.css"></head>
<body>
<div class="topnav"><div><strong>Admin</strong></div><div><a href="../chat/chat.php">Back</a></div></div>
<div class="chat-container"><h2>All Tickets</h2>
<table><tr><th>ID</th><th>User</th><th>Subject</th><th>Status</th><th>Assigned</th><th>Created</th></tr>
<?php while($row = $res->fetch_assoc()): ?>
  <tr>
    <td><?= (int)$row['id'] ?></td>
    <td><?= htmlspecialchars($row['user_name'] ?? 'Guest') ?></td>
    <td><?= htmlspecialchars($row['subject']) ?></td>
    <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
    <td><?= (int)$row['assigned_agent'] ?: '-' ?></td>
    <td><?= htmlspecialchars($row['created_at']) ?></td>
  </tr>
<?php endwhile; ?>
</table></div></body></html>
    