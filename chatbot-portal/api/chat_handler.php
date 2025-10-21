<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if ($userMessage === '') {
    echo json_encode(['error' => 'Empty message']);
    exit;
}

$data = [
    'model' => $GPT_MODEL ?: 'gpt-4o-mini',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful customer support assistant.'],
        ['role' => 'user', 'content' => $userMessage],
    ],
    'max_tokens' => 300,
    'temperature' => 0.7,
];

$ch = curl_init($GPT_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: ' . 'Bearer ' . $GPT_API_KEY,
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_SSL_VERIFYPEER => false, // TEMP: ignore SSL issues
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Debug output if error or invalid response
if ($error || !$response) {
    echo json_encode([
        'error' => 'Curl Error',
        'details' => $error,
        'http_code' => $httpCode,
    ]);
    exit;
}

$responseData = json_decode($response, true);
if (isset($responseData['error'])) {
    echo json_encode([
        'error' => 'API Error',
        'message' => $responseData['error']['message'] ?? 'Unknown error',
        'http_code' => $httpCode,
        'response' => $responseData,
    ]);
    exit;
}

$reply = $responseData['choices'][0]['message']['content'] ?? 'No response from AI.';
echo json_encode(['reply' => $reply]);
?>
