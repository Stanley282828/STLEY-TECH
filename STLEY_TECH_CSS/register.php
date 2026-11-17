<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_to('register.html');
require_csrf();
$email = post_string('email', 190);
$phone = post_string('phone', 40);
$password = (string)($_POST['password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');
$type = post_string('account_type', 20);

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || strlen($password) < 8 || $password !== $confirm || !in_array($type, ['customer','member'], true)) {
    flash('error', 'Please enter a valid email and phone number. Passwords must match and contain at least 8 characters.');
    redirect_to('register.html');
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$username = 'user_' . bin2hex(random_bytes(8));
$stmt = $db->prepare('INSERT INTO users (username, email, phone, password_hash, account_type) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $username, $email, $phone, $hash, $type);
if (!$stmt->execute()) {
    flash('error', $db->errno === 1062 ? 'This email address is already registered.' : 'Registration could not be completed.');
    redirect_to('register.html');
}
session_regenerate_id(true);
$_SESSION['user_id'] = $db->insert_id;
$_SESSION['username'] = $username;
$_SESSION['email'] = $email;
flash('success', 'Account created. You are now logged in.');
redirect_to('shop.html');
?>
