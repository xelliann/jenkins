<?php
// api/agent/poll_messages.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'agent') {
    http_response_code(403);
    echo json_encode(['error'=>'unauthorized']);
    exit;
}
$ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;
$since_id = isset($_GET['since_id']) ? intval($_GET['since_id']) : 0;
if ($ticket_id <= 0) { echo json_encode(['messages'=>[]]); exit; }

$stmt = $conn->prepare('SELECT id, sender, message, created_at FROM ticket_messages WHERE ticket_id = ? AND id > ? ORDER BY id ASC');
$stmt->bind_param('ii', $ticket_id, $since_id);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) {
    $r['message'] = $r['message']; // will be escaped on client
    $rows[] = $r;
}
$stmt->close();
echo json_encode(['messages'=>$rows]);
