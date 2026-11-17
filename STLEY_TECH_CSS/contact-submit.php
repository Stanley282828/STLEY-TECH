<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_to('contact.html');
require_csrf();
$name = post_string('cname', 160); $email = post_string('cemail', 190); $phone = post_string('cphone', 40); $message = post_string('cmsg', 5000);
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') { flash('error', 'Please complete your name, email, and message.'); redirect_to('contact.html'); }
$stmt = $db->prepare('INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $name, $email, $phone, $message); $stmt->execute();
flash('success', 'Your message was sent. We will reply within 24 hours.'); redirect_to('contact.html');
?>
