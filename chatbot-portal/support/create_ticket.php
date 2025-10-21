<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}
$user_id = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($subject === '' || $description === '') {
        $error = 'Subject and description are required.';
    } else {
        $stmt = $conn->prepare('INSERT INTO tickets (user_id, subject, description, status, created_at) VALUES (?, ?, ?, "open", NOW())');
        $stmt->bind_param('iss', $user_id, $subject, $description);
        $stmt->execute();
        $newId = $conn->insert_id;
        $stmt->close();
        header('Location: tickets.php?msg=created&id=' . $newId);
        exit;
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Create Ticket</title><link rel="stylesheet" href="../assets/styles.css"></head>
<body>
<div class="topnav"><div><strong>Chatbot Portal</strong></div><div><a href="tickets.php">My Tickets</a></div></div>
<div class="chat-container">
  <h2>Create Support Ticket</h2>
  <?php if (!empty($error)): ?><div style="color:red"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input name="subject" placeholder="Subject" required>
    <textarea name="description" rows="6" placeholder="Describe your issue..." required></textarea>
    <button type="submit">Create Ticket</button>
  </form>
</div>
</body></html>
