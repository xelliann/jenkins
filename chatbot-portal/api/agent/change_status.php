<?php
// api/agent/change_status.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'agent') {
    http_response_code(403); echo json_encode(['error'=>'unauthorized']); exit;
}
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$ticket_id = isset($data['ticket_id']) ? intval($data['ticket_id']) : 0;
$status = isset($data['status']) ? $data['status'] : '';
$allowed = ['open','in_progress','resolved','closed'];
if ($ticket_id <= 0 || !in_array($status, $allowed, true)) { http_response_code(400); echo json_encode(['error'=>'invalid']); exit; }

$stmt = $conn->prepare('UPDATE tickets SET status = ? WHERE id = ?');
$stmt->bind_param('si', $status, $ticket_id);
$stmt->execute();
$stmt->close();
echo json_encode(['ok'=>true]);
