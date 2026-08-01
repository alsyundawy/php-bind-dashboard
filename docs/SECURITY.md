# Security Best Practices

## Built-in Protections

- **CSRF tokens** on all state-changing forms
- **Argon2id** password hashing
- **Prepared statements** (PDO) – no raw SQL concatenation
- **Session regeneration** on login
- **HttpOnly + SameSite cookies**
- **Security headers**: X-Frame-Options, X-Content-Type-Options, CSP (basic), Referrer-Policy
- **Login lockout** after failed attempts
- **Role-based access** (admin / operator / viewer)
- **Output escaping** via `htmlspecialchars` everywhere

## Recommended Production Hardening

1. Run behind HTTPS only.
2. Restrict Nginx access by IP or VPN if the panel is internal-only.
3. Keep PHP and BIND9 updated.
4. Ensure `database/phpbind.db` is not web-accessible (Nginx config already denies).
5. Use a dedicated system user for the application if possible.
6. Regularly review `activity_logs`.
7. Change the default admin password immediately.
8. Consider setting `bind.allow_write = false` if you only want read-only view and manage zones manually.
9. Disable debug mode (`app.debug = false`).
10. Use `config.local.php` for secrets and keep it out of version control.

## PHP Static Analysis

The codebase is written with strict types and is intended to pass:

- PHP_CodeSniffer (PSR-12)
- PHPStan (level 6+)
- Psalm
- PHP-CS-Fixer
- PHPLint

Run your preferred tools after any modification.
