# PHP-Bind-Dashboard

[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Bootstrap 5.3](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![Status](https://img.shields.io/badge/status-stable-success)](docs/CHANGELOG.md)

**Modern, professional web administration console for BIND9.**

Lightweight · Secure · Responsive · Dark Mode · SQLite-powered

Inspired by [PowerDNS-Admin](https://github.com/PowerDNS-Admin/PowerDNS-Admin), built natively for BIND9 zone-file workflows.

---

## Features

| Area | Capability |
|------|------------|
| **Zones** | Create, list, view, delete forward & reverse zones |
| **Records** | Parse & display A, AAAA, CNAME, MX, NS, PTR, SOA, SRV, TXT, CAA |
| **Users** | Role-based access — Administrator · Operator · Viewer |
| **Auth** | Local login, Argon2id hashing, login lockout, session regeneration |
| **Audit** | Full activity log (who, what, when, IP) |
| **UI** | Bootstrap 5.3 + Font Awesome 6.7, dark mode, 480px → 2K+ |
| **Backend** | SQLite3 (zero external DB), BIND zone-file generation, optional `rndc reload` |
| **Security** | CSRF, prepared statements, security headers, output escaping |

---

## Requirements

- **PHP** 8.2 or 8.3+ with extensions: `pdo_sqlite`, `mbstring`, `json`, `openssl`
- **Nginx** (recommended) or Apache
- **BIND9** (`named`)
- Write access for the web-server user to the zones directory and `database/`

---

## Quick Start

```bash
git clone https://github.com/alsyundawy/php-bind-dashboard.git
cd php-bind-dashboard

# Permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 database

# Install database & default admin
php scripts/install.php

# Configure
cp config/config.php config/config.local.php   # optional overrides
# Edit config (zones_dir, named_conf, rndc_path, app.url)

# Point Nginx document root to public/
# See docs/INSTALL.md for the complete guide
```

**Default credentials** (change immediately):

| Field    | Value          |
|----------|----------------|
| Username | `admin`        |
| Password | `ChangeMe123!` |

---

## Documentation

| Document | Description |
|----------|-------------|
| [Installation & Deploy](docs/INSTALL.md) | Full step-by-step install, Nginx, permissions, BIND integration |
| [Configuration](docs/CONFIGURATION.md) | All config keys explained |
| [Security](docs/SECURITY.md) | Hardening checklist & built-in protections |
| [BIND9 Integration](docs/BIND9.md) | How zone files and `named.conf` are managed |
| [Changelog](docs/CHANGELOG.md) | Version history |
| [DOCNOTE](docs/DOCNOTE.md) | Behaviour notes, limitations, static-analysis notes |

---

## Project Structure

```
php-bind-dashboard/
├── config/                 # Application configuration
├── database/               # SQLite DB + schema + logs
├── docs/                   # Documentation
├── public/                 # Web root (document root)
│   ├── assets/css|js       # Minified production assets
│   └── index.php           # Front controller
├── scripts/                # CLI tools (install, …)
├── src/
│   ├── Controllers/
│   ├── Helpers/
│   └── Services/           # Auth, BindManager, ActivityLogger
└── templates/              # Views (layouts + pages)
```

---

## Security Highlights

- Argon2id password hashing  
- CSRF tokens on every state-changing form  
- PDO prepared statements only  
- Session regeneration on login + HttpOnly / SameSite cookies  
- Login attempt lockout  
- Strict output escaping  
- Security response headers (X-Frame-Options, CSP baseline, …)  
- Document root isolated to `public/`

---

## Static Analysis

The codebase targets:

- PHP_CodeSniffer (PSR-12)
- PHPStan (level 6+)
- Psalm
- PHP-CS-Fixer
- PHPLint

```bash
vendor/bin/phpstan analyse src --level=6
vendor/bin/phpcs --standard=PSR12 src public/index.php scripts
```

---

## License

MIT — free for personal and commercial use. See [LICENSE](LICENSE).

---

**PHP-Bind-Dashboard** — professional BIND9 management, simplified.
