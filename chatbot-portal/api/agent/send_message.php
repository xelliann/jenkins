<?php
// api/agent/send_message.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'agent') {
    http_response_code(403); echo json_encode(['error'=>'unauthorized']); exit;
}
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$ticket_id = isset($data['ticket_id']) ? intval($data['ticket_id']) : 0;
$message = trim($data['message'] ?? '');
$agent_id = intval($_SESSION['user_id']);
if ($ticket_id <= 0 || $message === '') { http_response_code(400); echo json_encode(['error'=>'invalid']); exit; }

// insert agent message into ticket_messages
$stmt = $conn->prepare('INSERT INTO ticket_messages (ticket_id, sender, message, created_at) VALUES (?, "agent", ?, NOW())');
$stmt->bind_param('is', $ticket_id, $message);
$stmt->execute();
$inserted_id = $conn->insert_id;
$stmt->close();

// optionally also add to chat_history for global logs
$ch = $conn->prepare('INSERT INTO chat_history (user_id, sender, message, created_at) VALUES (?, "agent", ?, NOW())');
$uid = null; // unknown mapping: we can fetch ticket's user_id if we want
// fetch ticket user_id
$t = $conn->prepare('SELECT user_id FROM tickets WHERE id = ? LIMIT 1');
$t->bind_param('i', $ticket_id);
$t->execute();
$tk = $t->get_result()->fetch_assoc();
$t->close();
$uid = $tk['user_id'] ? intval($tk['user_id']) : null;
if ($uid) {
    $ch->bind_param('is', $uid, $message);
    $ch->execute();
    $ch->close();
}

echo json_encode(['ok'=>true,'id'=>$inserted_id]);
