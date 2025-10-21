<?php
// api/agent/heartbeat.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db_connect.php';
session_start();

// storage dir - ensure writable by PHP
$storageDir = __DIR__ . '/../../storage/agents';
if (!is_dir($storageDir)) mkdir($storageDir, 0755, true);

// If client asks to check presence
if (isset($_GET['check']) && isset($_GET['agent_id'])) {
    $aid = intval($_GET['agent_id']);
    $file = $storageDir . "/agent_{$aid}.json";
    if (!file_exists($file)) { echo json_encode(['online'=>false]); exit; }
    $data = json_decode(file_get_contents($file), true);
    $last = isset($data['last_ping']) ? intval($data['last_ping']) : 0;
    $online = (time() - $last) < 18; // consider online if ping within 18s
    echo json_encode(['online'=>$online,'last_ping'=>$last]);
    exit;
}

// POST heartbeat to mark online
$raw = file_get_contents('php://input');
$json = json_decode($raw, true);
$agent_id = $json['agent_id'] ?? $_SESSION['user_id'] ?? null;
$agent_id = intval($agent_id);
if ($agent_id <= 0) { http_response_code(400); echo json_encode(['error'=>'invalid_agent']); exit; }

$file = $storageDir . "/agent_{$agent_id}.json";
$data = ['last_ping' => time()];
file_put_contents($file, json_encode($data));
echo json_encode(['ok'=>true]);
