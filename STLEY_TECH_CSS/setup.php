<?php
declare(strict_types=1);
require_once __DIR__ . '/session.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$lockFile = __DIR__ . '/.database-installed';
if (is_file($lockFile)) {
    http_response_code(403);
    exit('Database setup is disabled after installation. Remove setup.php only after confirming the site works.');
}

function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
$token = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $provided = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($token, $provided)) {
        http_response_code(419);
        exit('Invalid form token. Please refresh the setup page and try again.');
    }

    $host = trim((string)($_POST['db_host'] ?? 'localhost')) ?: 'localhost';
    $name = trim((string)($_POST['db_name'] ?? ''));
    $user = trim((string)($_POST['db_user'] ?? ''));
    $pass = (string)($_POST['db_pass'] ?? '');
    if ($name === '' || $user === '') {
        $message = 'Database name and database username are required.';
        $messageType = 'error';
    } else {
        mysqli_report(MYSQLI_REPORT_OFF);
        $db = @new mysqli($host, $user, $pass, $name);
        if ($db->connect_errno) {
            $message = 'Database connection failed. Check the exact Hostinger database values.';
            $messageType = 'error';
        } else {
            $db->set_charset('utf8mb4');
            $sql = @file_get_contents(__DIR__ . '/stley_tech.sql');
            $importError = false;
            if ($sql === false) {
                $importError = true;
            } else {
                $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql);
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if ($statement === '' || preg_match('/^(--|#)/', $statement)) continue;
                    if (!$db->query($statement)) { $importError = true; break; }
                }
            }
            if ($importError) {
                $message = 'The database connection worked, but the educational schema could not be imported. Check the SQL file and database permissions.';
                $messageType = 'error';
            } else {
                $config = "<?php\ndeclare(strict_types=1);\nconst DB_HOST = " . var_export($host, true) . ";\nconst DB_NAME = " . var_export($name, true) . ";\nconst DB_USER = " . var_export($user, true) . ";\nconst DB_PASS = " . var_export($pass, true) . ";\nconst DB_CHARSET = 'utf8mb4';\nconst SHOW_DATABASE_ERRORS = false;\n?>\n";
                if (@file_put_contents(__DIR__ . '/config.php', $config, LOCK_EX) === false) {
                    $message = 'The schema was imported, but config.php could not be written. Edit it manually, then remove setup.php.';
                    $messageType = 'error';
                } else {
                    @file_put_contents($lockFile, 'installed ' . date('c') . PHP_EOL, LOCK_EX);
                    $message = 'Database connected and schema imported. Test registration and login, then delete setup.php.';
                    $messageType = 'success';
                }
            }
            $db->close();
        }
    }
}
?><!doctype html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>STLEY TECH Educational Setup</title><style>body{font-family:Arial,sans-serif;background:#101313;color:#f5f7f5;margin:0;padding:32px}.card{max-width:620px;margin:30px auto;background:#1d2422;padding:28px;border-radius:14px}h1{margin-top:0}label{display:block;margin-top:16px;font-weight:700}input{box-sizing:border-box;width:100%;padding:12px;margin-top:6px;border-radius:7px;border:1px solid #66716d;background:#fff;color:#111;font-size:16px}.button{margin-top:22px;background:#b8f26c;border:0;border-radius:7px;padding:13px 18px;font-weight:700;cursor:pointer}.note{color:#c9d2cd;line-height:1.5}.message{padding:14px;border-radius:8px;margin:16px 0;line-height:1.5}.success{background:#244d32}.error{background:#6b2929}code{color:#d7ff9b}</style></head><body><main class="card"><h1>STLEY TECH educational database setup</h1><p class="note">Use this one-time installer only for your own educational Hostinger project. It never displays database credentials or raw SQL errors.</p><?php if ($message): ?><div class="message <?= h($messageType) ?>"><?= h($message) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= h($token) ?>"><label for="db_host">Database host</label><input id="db_host" name="db_host" value="<?= h($_POST['db_host'] ?? 'localhost') ?>" required><label for="db_name">Database name</label><input id="db_name" name="db_name" value="<?= h($_POST['db_name'] ?? '') ?>" required><label for="db_user">Database username</label><input id="db_user" name="db_user" value="<?= h($_POST['db_user'] ?? '') ?>" required><label for="db_pass">Database password</label><input id="db_pass" type="password" name="db_pass" required><button class="button" type="submit">Test connection and install database</button></form><p class="note">After success, test the site and delete <code>setup.php</code> from public_html.</p></main></body></html>
