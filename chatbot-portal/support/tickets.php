<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}
$user_id = intval($_SESSION['user_id']);
$stmt = $conn->prepare('SELECT id, subject, status, created_at FROM tickets WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>My Tickets</title><link rel="stylesheet" href="../assets/styles.css"></head>
<body>
<div class="topnav"><div><strong>Chatbot Portal</strong></div><div><a href="../chat/chat.php">Back to Chat</a></div></div>
<div class="chat-container">
  <h2>My Tickets</h2>
  <a href="create_ticket.php">+ New Ticket</a>
  <table style="margin-top:12px">
    <tr><th>ID</th><th>Subject</th><th>Status</th><th>Created</th><th>Action</th></tr>
    <?php while($row = $res->fetch_assoc()): ?>
      <tr>
        <td><?= (int)$row['id'] ?></td>
        <td><?= htmlspecialchars($row['subject']) ?></td>
        <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td><a href="ticket_chat.php?id=<?= (int)$row['id'] ?>">Open</a></td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
</body></html>
