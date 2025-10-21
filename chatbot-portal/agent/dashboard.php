<?php
// agent/dashboard.php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

// Agent-only guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'agent') {
    header('Location: ../auth/login.php');
    exit;
}
$agent_id = intval($_SESSION['user_id']);
$agent_name = $_SESSION['user_name'] ?? 'Agent';

// Fetch tickets assigned to this agent
$stmt = $conn->prepare('SELECT id, user_id, subject, status, created_at FROM tickets WHERE assigned_agent = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $agent_id);
$stmt->execute();
$tickets = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Agent Dashboard</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <style>
    .agent-top{display:flex;justify-content:space-between;align-items:center;padding:12px;background:#fff;border-bottom:1px solid #eee}
    .online-ind {font-weight:600;color:green}
    .offline-ind {color:#888}
    .ticket-row:hover{background:#f9f9ff}
  </style>
</head>
<body>
  <div class="agent-top">
    <div><strong>Agent Dashboard</strong> — <?=htmlspecialchars($agent_name)?></div>
    <div>
      <span id="presence" class="offline-ind">Checking presence...</span>
      <a href="../chat/chat.php" style="margin-left:12px">Back</a>
      <a href="../auth/logout.php" style="margin-left:12px">Logout</a>
    </div>
  </div>

  <div class="chat-container" style="margin-top:16px">
    <h2>Assigned Tickets</h2>
    <table style="width:100%;margin-top:10px">
      <tr><th>ID</th><th>Subject</th><th>Status</th><th>Created</th><th>Action</th></tr>
      <?php while($row = $tickets->fetch_assoc()): ?>
        <tr class="ticket-row">
          <td><?= (int)$row['id'] ?></td>
          <td><?= htmlspecialchars($row['subject']) ?></td>
          <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
          <td><a href="ticket_view.php?id=<?= (int)$row['id'] ?>">Open</a></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>

<script>
// heartbeat: tell server we're online and poll presence status for this agent id
const AGENT_ID = <?=json_encode($agent_id)?>;
async function heartbeat(){
  try {
    await fetch('../api/agent/heartbeat.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({agent_id: AGENT_ID})});
  } catch(e){}
}
async function checkPresence(){
  try {
    const r = await fetch('../api/agent/heartbeat.php?check=1&agent_id=' + AGENT_ID);
    const j = await r.json();
    const el = document.getElementById('presence');
    if (j && j.online) {
      el.textContent = 'Online';
      el.className = 'online-ind';
    } else {
      el.textContent = 'Offline';
      el.className = 'offline-ind';
    }
  } catch(e){}
}
heartbeat(); // initial
setInterval(heartbeat, 10000); // every 10s
setInterval(checkPresence, 5000); // update UI
checkPresence();
</script>
</body>
</html>
