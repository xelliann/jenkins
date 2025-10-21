<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
        } elseif ($user['role'] === 'agent') {
            header('Location: ../chat/agent.php');
        } else {
            header('Location: ../chat/chat.php');
        }
        exit;
    }

    header('Location: login.php?error=1');
    exit;
}
http_response_code(400);
echo 'Invalid request';
?>
