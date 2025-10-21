<?php
// /api/config.php
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        putenv(sprintf('%s=%s', $name, $value));
    }
}

// Load .env from project root
loadEnv(__DIR__ . '/../.env');

// Access env vars
$GPT_API_KEY = getenv('GPT_API_KEY');
$GPT_API_URL = getenv('GPT_API_URL');
$GPT_MODEL   = getenv('GPT_MODEL');

// Safety check
if (!$GPT_API_KEY || !$GPT_API_URL) {
    http_response_code(500);
    echo json_encode(["error" => "API configuration missing"]);
    exit;
}
?>
