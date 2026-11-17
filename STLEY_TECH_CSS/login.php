<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_to('index.html');
require_csrf();

$email = post_string('email', 190);
if ($email === '') $email = post_string('user', 190);
$password = (string)($_POST['pass'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    flash('error', 'Please enter your email address and password.');
    redirect_to('index.html');
}

$stmt = $db->prepare('SELECT id, username, email, password_hash FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, (string)$user['password_hash'])) {
    flash('error', 'The email or password is incorrect.');
    redirect_to('index.html');
}

if (password_needs_rehash((string)$user['password_hash'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $updateHash = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $userId = (int)$user['id'];
    $updateHash->bind_param('si', $newHash, $userId);
    $updateHash->execute();
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$userId = (int)$user['id'];
$updateLogin = $db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
$updateLogin->bind_param('i', $userId);
$updateLogin->execute();
flash('success', 'Sign-in successful. Welcome.');
redirect_to('shop.html');
?>
