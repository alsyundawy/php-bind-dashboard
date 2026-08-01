# Changelog

All notable changes to **PHP-Bind-Dashboard** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

---

## [1.0.1] — 2026-08-02

### Fixed
- **Critical** — Zone serial generation operator-precedence bug under `strict_types` that caused `TypeError` when creating zones  
  (`(int) date('Ymd') . '01'` → `(int) (date('Ymd') . '01')`)
- Zone type restricted to `master` | `slave` | `forward`
- Config loader validates that config files return arrays
- Database helper safely handles PDO options and directory creation failures
- Auth logout cookie deletion now preserves the `SameSite` attribute
- Front controller no longer maps non-admin access to `/users` and `/settings` as 404; controllers correctly respond with 403
- CSRF token values from `$_POST` are type-checked before validation
- Unexpected exceptions no longer leak internal paths to the UI
- Installer validates `database.path` and fails clearly on directory creation errors
- Safer typing for `default_soa` / `default_ns` and `rndc` path

### Security
- Database singleton rejects unserialization
- CSRF field name explicitly cast to string
- Controlled `exec` for `rndc` uses escaped arguments only

### Code quality
- Redundant controller `require` statements removed (PSR-4 autoloader)
- Unused parser variable removed
- `// NOSONAR` annotations added only where justified for SonarQube false positives
- PSR-12, strict types, and static-analysis readiness throughout

---

## [1.0.0] — 2026-08-02

### Added
- Modern responsive UI (Bootstrap 5.3, Font Awesome 6.7, jQuery)
- Dark mode (system preference + manual toggle)
- Zone management (create, list, view, delete)
- BIND9 zone-file generation and basic parser
- User authentication with Argon2id and login lockout
- Role-based access control (admin / operator / viewer)
- Activity / audit logging
- SQLite3 backend
- Security headers and CSRF protection
- Full documentation set (Install, Configuration, Security, BIND9, DOCNOTE)
- Responsive design from 480px through 2K+ displays
