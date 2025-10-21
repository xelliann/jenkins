<?php
// agent/ticket_view.php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'agent') {
    header('Location: ../auth/login.php');
    exit;
}
$agent_id = intval($_SESSION['user_id']);
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($ticket_id <= 0) { header('Location: dashboard.php'); exit; }

// verify ticket assigned to this agent (or allow open if admin)
$stmt = $conn->prepare('SELECT t.*, u.name AS user_name, u.email AS user_email FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ? LIMIT 1');
$stmt->bind_param('i', $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ticket) { header('Location: dashboard.php'); exit; }
// optional: enforce assigned_agent === agent_id
// if ($ticket['assigned_agent'] != $agent_id) { header('Location: dashboard.php'); exit; }
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Ticket #<?= (int)$ticket_id ?></title>
<link rel="stylesheet" href="../assets/styles.css">
<style>.msg{margin:8px 0;padding:8px;border-radius:6px}.msg.user{background:#f6f6ff;text-align:left}.msg.agent{background:#eef9f2;text-align:right}</style>
</head>
<body>
  <div class="topnav"><div><strong>Ticket #<?= (int)$ticket_id ?></strong> - <?=htmlspecialchars($ticket['subject'])?></div><div><a href="dashboard.php">Back</a></div></div>
  <div class="chat-container">
    <h3>Conversation (User: <?=htmlspecialchars($ticket['user_name'] ?? 'Guest')?>)</h3>
    <div id="chat-box" style="height:320px;overflow:auto;padding:10px;border:1px solid #eee;background:#fff;border-radius:8px"></div>

    <form id="sendForm" style="margin-top:10px">
      <input id="messageInput" autocomplete="off" placeholder="Write message to user..." style="width:78%;padding:8px">
      <button type="submit" style="padding:8px">Send</button>
      <select id="statusSelect" style="margin-left:8px;padding:6px">
        <option value="open" <?= $ticket['status']==='open' ? 'selected' : '' ?>>Open</option>
        <option value="in_progress" <?= $ticket['status']==='in_progress' ? 'selected' : '' ?>>In Progress</option>
        <option value="resolved" <?= $ticket['status']==='resolved' ? 'selected' : '' ?>>Resolved</option>
        <option value="closed" <?= $ticket['status']==='closed' ? 'selected' : '' ?>>Closed</option>
      </select>
    </form>
    <div style="margin-top:8px"><small class="small">Agent presence: <span id="agentPresence">Checking...</span></small></div>
  </div>

<script>
const TICKET_ID = <?=json_encode($ticket_id)?>;
const POLL_INTERVAL = 2500; // ms

async function fetchMessages(since_id = 0) {
  try {
    const r = await fetch('../api/agent/poll_messages.php?ticket_id=' + TICKET_ID + '&since_id=' + since_id);
    return await r.json();
  } catch(e) { return {messages:[]}; }
}

function renderMessages(messages) {
  const box = document.getElementById('chat-box');
  messages.forEach(m => {
    const d = document.createElement('div');
    d.className = 'msg ' + (m.sender === 'agent' ? 'agent' : 'user');
    d.innerHTML = '<strong>' + (m.sender === 'agent' ? 'Agent' : 'User') + ':</strong> ' + escapeHtml(m.message) + '<div class="small">' + m.created_at + '</div>';
    box.appendChild(d);
  });
  box.scrollTop = box.scrollHeight;
}

function escapeHtml(str){ return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[s])); }

let lastId = 0;
async function poll() {
  const res = await fetchMessages(lastId);
  if (res.messages && res.messages.length) {
    renderMessages(res.messages);
    lastId = res.messages[res.messages.length -1].id;
  }
  // update presence
  try {
    const p = await fetch('../api/agent/heartbeat.php?check=1&agent_id=' + <?=json_encode($agent_id)?>);
    const j = await p.json();
    document.getElementById('agentPresence').textContent = j.online ? 'Online' : 'Offline';
  } catch(e){}
}

document.getElementById('sendForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const msg = document.getElementById('messageInput').value.trim();
  const status = document.getElementById('statusSelect').value;
  if (!msg) return;
  // send agent message
  await fetch('../api/agent/send_message.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ ticket_id: TICKET_ID, message: msg })
  });
  // update ticket status if changed
  await fetch('../api/agent/change_status.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ ticket_id: TICKET_ID, status: status })
  });
  document.getElementById('messageInput').value = '';
  // immediately poll
  poll();
});

setInterval(poll, POLL_INTERVAL);
poll(); // initial
</script>
</body>
</html>
