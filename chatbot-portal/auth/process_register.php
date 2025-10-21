<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = in_array($_POST['role'] ?? 'user', ['user', 'agent', 'admin']) ? $_POST['role'] : 'user';

if ($name === '' || $email === '' || $password === '') {
    $_SESSION['error'] = 'All fields are required.';
    header('Location: register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email address.';
    header('Location: register.php');
    exit;
}

// Check if user already exists
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['error'] = 'Email already registered.';
    header('Location: register.php');
    exit;
}
$stmt->close();

// Insert new user
$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())');
$stmt->bind_param('ssss', $name, $email, $hashed, $role);
if ($stmt->execute()) {
    $_SESSION['success'] = 'Registration successful. Please login.';
    header('Location: login.php');
} else {
    $_SESSION['error'] = 'Registration failed. Try again.';
    header('Location: register.php');
}
$stmt->close();
$conn->close();
exit;
?>
