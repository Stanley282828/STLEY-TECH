# STLEY TECH — Safe educational Hostinger deployment

This package contains the STLEY TECH gaming-phone shop with its PHP/MySQL learning backend. It is intended for educational practice only, not for real customer data or production commerce.

## Security fixes included

- Login now verifies the stored password hash; it no longer accepts any password or auto-creates accounts.
- Password reset is disabled rather than allowing an unauthenticated email-only reset. A real production reset needs expiring, single-use email tokens.
- CSRF token generation no longer depends on the database, fixing the `Invalid form token` problem during setup.
- Sessions use HttpOnly/SameSite cookies and rotate after login/registration.
- Setup requires a CSRF token, hides raw database errors, and disables itself after a successful install.
- `config.php`, database files, lock files, and backup-style files are blocked from web access.
- Security response headers and directory listing protection are enabled where Hostinger supports them.
- No payment processing is included. The order form is only an educational demo.

## Hostinger setup

1. Create a MySQL database and user under **Databases → MySQL Databases**.
2. Upload all package files into the domain's `public_html`.
3. Open `https://your-domain.com/setup.php` once.
4. Enter the exact database host, name, username, and password from hPanel.
5. Confirm the success message, test registration/login and a sample order.
6. Delete `setup.php` immediately after testing. The installer also creates `.database-installed` and refuses to run again.
7. Keep `SHOW_DATABASE_ERRORS = false` in `config.php`.
8. Run Hostinger Malware Scanner and request a Google Safe Browsing review if the domain was previously flagged.

## Important educational limitations

- Do not use this package for real customer passwords, personal data, or payments without a full security review.
- Do not turn password reset back on without expiring, single-use, email-delivered tokens.
- Do not expose `config.php`, `db.php`, `auth.php`, `session.php`, `stley_tech.sql`, or backups publicly.
- Use a separate test database and test credentials.
