<?php
// chatbot_api.php - working backend using config.php for API key
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/config.php'; // <-- uses $api_key from config.php

$input = json_decode(file_get_contents('php://input'), true);
$message = isset($input['message']) ? trim($input['message']) : '';

if ($message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'empty_message']);
    exit;
}

// Storage directory
$storage_dir = __DIR__ . '/../storage';
if (!is_dir($storage_dir)) mkdir($storage_dir, 0755, true);
$messages_file = $storage_dir . '/messages.json';
if (!file_exists($messages_file)) file_put_contents($messages_file, json_encode([]));

$messages = json_decode(file_get_contents($messages_file), true);
if (!is_array($messages)) $messages = [];

// Append user message
$messages[] = [
    'sender' => 'user',
    'message' => $message,
    'created_at' => date('c')
];

$reply = null;

if (!empty($api_key)) {
    // Call OpenAI Chat Completions API
    $payload = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 400,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp !== false) {
        $data = json_decode($resp, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $reply = trim($data['choices'][0]['message']['content']);
        }
    }

    if ($err && !$reply) {
        error_log('OpenAI API Error: ' . $err);
    }
}

// Fallback if API key is missing or API failed
if (!$reply) {
    $reply = "I'm sorry, I couldn't reach the AI right now. (API issue)";
}

// Save bot reply
$messages[] = [
    'sender' => 'bot',
    'message' => $reply,
    'created_at' => date('c')
];

file_put_contents($messages_file, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Response
echo json_encode(['reply' => $reply]);
