<?php
// /api/recent_messages.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$storage_dir = __DIR__ . '/../storage';
$messages_file = $storage_dir . '/messages.json';
if (!file_exists($storage_dir)) mkdir($storage_dir, 0755, true);

if (!file_exists($messages_file)) {
    echo json_encode([]);
    exit;
}

$messages = json_decode(file_get_contents($messages_file), true);
if (!is_array($messages)) $messages = [];

$limit = 100;
$start = max(0, count($messages) - $limit);
$recent = array_slice($messages, $start);

echo json_encode($recent);
