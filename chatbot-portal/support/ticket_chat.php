<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}
$user_id = intval($_SESSION['user_id']);
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($ticket_id <= 0) { header('Location: tickets.php'); exit; }

// handle posting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender = $_POST['sender'] ?? 'user';
    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        $stmt = $conn->prepare('INSERT INTO ticket_messages (ticket_id, sender, message, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iss', $ticket_id, $sender, $message);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: ticket_chat.php?id=' . $ticket_id);
    exit;
}

// fetch ticket
$ts = $conn->prepare('SELECT * FROM tickets WHERE id = ? LIMIT 1');
$ts->bind_param('i', $ticket_id);
$ts->execute();
$ticket = $ts->get_result()->fetch_assoc();
$ts->close();
if (!$ticket) { header('Location: tickets.php'); exit; }

// messages
$ms = $conn->prepare('SELECT sender, message, created_at FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC');
$ms->bind_param('i', $ticket_id);
$ms->execute();
$messages = $ms->get_result();
$ms->close();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Ticket Chat</title><link rel="stylesheet" href="../assets/styles.css"></head>
<body>
<div class="topnav"><div><strong>Ticket #<?= (int)$ticket_id ?></strong></div><div><a href="tickets.php">Back to Tickets</a></div></div>
<div class="chat-container">
  <h3><?= htmlspecialchars($ticket['subject'] ?? 'Ticket') ?></h3>
  <div id="chat-box" style="height:300px;overflow:auto;padding:10px;border:1px solid #eee;background:#fafafa;border-radius:6px">
    <?php while($m = $messages->fetch_assoc()): ?>
      <div class="<?= htmlspecialchars($m['sender']) ?>"><strong><?= ucfirst(htmlspecialchars($m['sender'])) ?>:</strong> <?= nl2br(htmlspecialchars($m['message'])) ?></div>
    <?php endwhile; ?>
  </div>
  <form method="post" style="margin-top:10px">
    <input type="hidden" name="sender" value="user">
    <input name="message" placeholder="Type a message..." autocomplete="off">
    <button type="submit">Send</button>
  </form>
</div>
</body></html>
