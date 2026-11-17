<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$placeholders = [
    'REPLACE_WITH_HOSTINGER_DATABASE_NAME',
    'REPLACE_WITH_HOSTINGER_DATABASE_USER',
    'REPLACE_WITH_HOSTINGER_DATABASE_PASSWORD',
];
$configured = DB_NAME !== '' && DB_USER !== '' && !in_array(DB_NAME, $placeholders, true) && !in_array(DB_USER, $placeholders, true) && !in_array(DB_PASS, $placeholders, true);

if (!$configured) {
    http_response_code(503);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    exit('<!doctype html><html lang="en"><meta charset="utf-8"><title>STLEY TECH setup required</title><body><h1>STLEY TECH setup required</h1><p>Database connection is not configured yet.</p><p><a href="setup.php">Open the secure database setup page</a></p></body></html>');
}

mysqli_report(MYSQLI_REPORT_OFF);
$db = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_errno) {
    http_response_code(503);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    exit('<!doctype html><html lang="en"><meta charset="utf-8"><title>STLEY TECH database unavailable</title><body><h1>STLEY TECH database unavailable</h1><p>Check the database values in Hostinger hPanel, then run <a href="setup.php">setup.php</a>.</p></body></html>');
}
$db->set_charset(DB_CHARSET);
?>
