<?php
// api/chat.php
// Placeholder to call Azure OpenAI / Azure Cognitive Services API.
// Replace AZURE_ENDPOINT, AZURE_KEY, DEPLOYMENT_NAME with your values.
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';
if(!$prompt) {
    echo json_encode(['error'=>'empty prompt']);
    exit;
}

// 1) Check local FAQ DB for a best-match (simple LIKE search)
require_once __DIR__ . '/../includes/db.php';
$stmt = $conn->prepare('SELECT answer FROM faqs WHERE question LIKE ? AND is_active=1 LIMIT 1');
$stmt->execute(['%'.substr($prompt,0,50).'%']);
$faq = $stmt->fetchColumn();
if($faq) {
    echo json_encode(['answer'=> $faq, 'source'=>'faq']);
    exit;
}

// 2) Fallback: call Azure API (example using cURL)
// IMPORTANT: Put your Azure credentials and endpoint here.
$azure_endpoint = 'https://YOUR_AZURE_OPENAI_ENDPOINT';
$azure_key = 'YOUR_AZURE_KEY';
$deployment = 'YOUR_DEPLOYMENT_NAME';

// Example: send prompt to Azure OpenAI (Chat Completions) - adjust per your Azure deployment
$payload = [
    'messages' => [
        ['role'=>'system','content'=>'You are a helpful customer support assistant.'],
        ['role'=>'user','content'=>$prompt]
    ],
    'max_tokens' => 500
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $azure_endpoint . '/openai/deployments/'. $deployment .'/chat/completions?api-version=2023-10-01-preview');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'api-key: ' . $azure_key
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$result = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
if($err) {
    echo json_encode(['error'=>'API error: '.$err]);
    exit;
}
$decoded = json_decode($result, true);
$answer = $decoded['choices'][0]['message']['content'] ?? 'No answer from API';
echo json_encode(['answer'=>$answer, 'source'=>'azure']);
