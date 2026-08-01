# DOCNOTE — Behaviour, Limitations & Analysis Notes

This document records intentional design decisions, known limitations, and guidance for static-analysis tools.

---

## Behaviour Notes

### Zone management
- Metadata (name, type, serial, notes) lives in SQLite.
- Authoritative data lives in standard BIND zone files under `bind.zones_dir`.
- Creating a zone writes a new zone file and may append a `zone` statement to `named.conf.local` when that file is writable.
- Deleting a zone removes the zone file and the database row. Removal of the corresponding `named.conf` entry is **not** automatic in v1.0 (manual cleanup or future enhancement).

### Record editing
- The UI currently **displays** parsed records (read-only).
- Full in-browser record editing is intentionally deferred; zone files can be edited on disk or the parser/writer API can be extended.

### Zone-file parser
- Supports common single-line records and `$ORIGIN` / `$TTL`.
- `$GENERATE`, complex multi-line RDATA, and some exotic types are skipped or only partially handled.
- Parser is deliberately conservative for safety.

### Authentication
- First installed user is always role `admin`.
- Lockout resets attempts after the configured window.
- Session is regenerated on successful login.

### Error messages
- User-facing messages for unexpected failures deliberately avoid leaking filesystem paths or internal details.
- Validation errors (`InvalidArgumentException`) are shown as-is because they are safe.

---

## Security Notes

- Document root **must** be `public/`. Never expose `config/`, `database/`, `src/`, or `scripts/`.
- `config.local.php` should contain environment-specific values and remain untracked.
- Default admin password must be changed on first use.
- `app.debug` must be `false` in production.

---

## Static Analysis / Linter Notes

### PHPStan / Psalm
- Level 6+ is the target.
- Config values retrieved via `Config::get()` are `mixed`; call sites cast or guard as needed.
- Singleton `Database` implements `__wakeup` to block unserialization.

### SonarQube / similar
False-positive candidates that may be suppressed with `// NOSONAR` when justified:

| Location | Reason |
|----------|--------|
| `@chmod` / `@unlink` on zone files | Best-effort; permission failure is non-fatal |
| `exec()` for `rndc` | Arguments are escaped with `escapeshellcmd` + `escapeshellarg`; path comes from config |
| CSP containing `'unsafe-inline'` | Required for Bootstrap 5.3 CDN usage in current layout |

Do **not** suppress real issues (SQL concatenation, missing CSRF, plaintext passwords, etc.).

### PHPCS / PHP-CS-Fixer
- Coding standard: **PSR-12**
- All application PHP files use `declare(strict_types=1);`

---

## Compatibility

- Minimum PHP: **8.2**
- Tested design target: PHP 8.2 / 8.3
- SQLite 3.35+ (for modern pragmas; schema uses widely supported features)

---

## Versioning

See [CHANGELOG.md](CHANGELOG.md). Semantic versioning is followed for the public surface.
