<?php
declare(strict_types=1);
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
function redirect_to(string $url): never { header('Location: ' . $url, true, 303); exit; }
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function require_csrf(): void {
    $expected = (string)($_SESSION['csrf_token'] ?? '');
    $provided = (string)($_POST['csrf_token'] ?? '');
    if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
        http_response_code(419);
        exit('Invalid form token. Please refresh the page and try again.');
    }
}
function post_string(string $key, int $max = 255): string {
    $value = trim((string)($_POST[$key] ?? ''));
    return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
}
function flash(string $type, string $message): void { $_SESSION['flash'] = [$type, $message]; }
function take_flash(): ?array { $value = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $value; }
function logged_user_id(): ?int { return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null; }
?>
