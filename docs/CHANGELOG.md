# Changelog

All notable changes to PHP-Bind-Dashboard are documented in this file.

## 1.0.1 (2026-08-02)

### Fixed
- **Critical**: Zone serial generation operator-precedence bug that produced a string under `strict_types` and caused `TypeError` when creating zones (`(int) date('Ymd') . '01'` → `(int) (date('Ymd') . '01')`).
- Zone type validation: only `master`, `slave`, `forward` are accepted.
- Config loader now verifies that config files return arrays.
- Database helper safely casts PDO options and fails clearly if the database directory cannot be created.
- Auth logout cookie deletion now preserves `SameSite` attribute (PHP 7.3+ array form of `setcookie`).
- Front controller no longer forces 404 for non-admin access to `/users` and `/settings`; controllers correctly return 403.
- Removed redundant manual `require` of controllers (autoloader is used).
- Removed unused variable in zone file parser.
- Safer typing for `default_soa` / `default_ns` configuration values.

### Security / Hardening
- CSRF field name is explicitly cast to string.
- Database singleton rejects unserialization.

### Code Quality
- PSR-12 style, `declare(strict_types=1)`, typed properties and return types throughout.
- Prepared for PHPStan / Psalm / PHPCS / PHP-CS-Fixer.

## 1.0.0 (2026-08-02)

### Initial Release
- Modern responsive UI (Bootstrap 5.3 + custom utilities, Font Awesome 6.7, jQuery)
- Dark mode support (system preference + manual toggle)
- Zone management (create, list, view, delete)
- User authentication with Argon2id + lockout
- Role-based access (admin / operator / viewer)
- Activity logging
- SQLite3 backend
- BIND9 zone file generation and basic parser
- Security headers, CSRF protection
- Full documentation
- Designed for screens from 480px to 2K+
