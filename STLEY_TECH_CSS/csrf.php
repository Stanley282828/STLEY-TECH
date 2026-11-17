<?php
declare(strict_types=1);
require_once __DIR__ . '/session.php';
header('Content-Type: application/javascript; charset=UTF-8');
$token = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
echo 'document.querySelectorAll("input[name=csrf_token]").forEach(function(i){i.value=' . json_encode($token, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';});';
?>
