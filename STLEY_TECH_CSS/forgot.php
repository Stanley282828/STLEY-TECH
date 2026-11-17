<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_to('forgot.html');
require_csrf();

// Educational build: do not change passwords from an unauthenticated public form.
// A real production reset flow must use an expiring, single-use email token.
flash('error', 'Password reset is disabled in this educational build. Contact the site administrator for an account reset.');
redirect_to('forgot.html');
?>
