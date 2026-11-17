<?php
declare(strict_types=1);
require_once __DIR__ . '/session.php';
header('Content-Type: application/javascript; charset=UTF-8');
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
if ($flash && isset($flash[1])) {
    echo 'window.addEventListener("DOMContentLoaded",function(){alert(' . json_encode((string)$flash[1], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');});';
}
?>
