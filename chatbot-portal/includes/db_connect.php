<?php
// includes/db_connect.php
// Update DB credentials or use environment variables in production.
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'chatbot_portal';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    // In production log this and show a generic message
    error_log('DB connect error: ' . $conn->connect_error);
    die('Database connection failed.');
}
$conn->set_charset('utf8mb4');
