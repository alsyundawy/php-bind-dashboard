# PHP-Bind-Dashboard

**Modern, Professional DNS Administration GUI for BIND9**

PHP-Bind-Dashboard is a lightweight, secure, and responsive web-based administration interface for BIND9 DNS servers. Built with PHP 8.2+, SQLite3, Nginx, Bootstrap 5.3, Tailwind CSS utilities, jQuery, and Font Awesome 6.7. Designed to closely resemble the look and feel of PowerDNS-Admin while providing native BIND9 zone file management.

## Features

- **Zone Management**: Create, edit, delete forward and reverse zones
- **Record Management**: Full support for A, AAAA, CNAME, MX, NS, PTR, SOA, SRV, TXT, CAA, and more
- **User Management**: Role-based access control (Administrator, Operator, Viewer)
- **Activity Logging**: Complete audit trail of all changes
- **Authentication**: Local authentication with secure password hashing (Argon2id)
- **Dark Mode**: Native support with Bootstrap 5.3 `data-bs-theme` + Tailwind dark variants
- **Responsive Design**: Fully responsive from 480px mobile to 2K/4K displays
- **Modern UI**: Clean, professional dashboard inspired by PowerDNS-Admin (2026 design trends)
- **Security**: CSRF protection, prepared statements, input validation, secure headers
- **BIND9 Integration**: Generates standard BIND zone files + `named.conf` includes, supports `rndc reload`
- **SQLite3 Backend**: Zero external database dependency
- **Minified Assets**: Production-ready CSS/JS

## Requirements

- PHP 8.2 or higher (with PDO SQLite, mbstring, json, openssl)
- Nginx (or Apache)
- BIND9 (named)
- SQLite3
- Optional: `rndc` for live reload

## Quick Start

```bash
# Clone or copy the project
cd /var/www
# Ensure permissions
chown -R www-data:www-data php-bind-dashboard
chmod -R 755 php-bind-dashboard
chmod -R 775 php-bind-dashboard/database php-bind-dashboard/public/uploads

# Initialize database
php scripts/install.php

# Configure Nginx (see docs/nginx.conf.example)
# Point document root to public/
```

Default admin credentials after install:
- Username: `admin`
- Password: `ChangeMe123!` (change immediately)

## Project Structure

```
php-bind-dashboard/
├── config/                 # Configuration files
├── database/               # SQLite database + migrations
├── docs/                   # Documentation
├── public/                 # Web root (Nginx document root)
│   ├── assets/
│   │   ├── css/            # Minified CSS (Bootstrap + custom + Tailwind utilities)
│   │   ├── js/             # Minified JS (jQuery + custom)
│   │   └── img/
│   ├── index.php           # Front controller
│   └── .htaccess
├── scripts/                # CLI scripts (install, backup, etc.)
├── src/
│   ├── Controllers/        # MVC Controllers
│   ├── Models/             # Data models
│   ├── Services/           # Business logic (BindManager, Auth, etc.)
│   ├── Helpers/            # Utility helpers
│   └── Middleware/         # Auth, CSRF, etc.
├── templates/              # PHP templates (views)
│   ├── layouts/
│   ├── pages/
│   └── partials/
├── tests/
└── vendor/                 # (if using Composer later)
```

## Documentation

- [Installation Guide](docs/INSTALL.md)
- [Configuration](docs/CONFIGURATION.md)
- [Security Best Practices](docs/SECURITY.md)
- [BIND9 Integration](docs/BIND9.md)
- [Changelog](docs/CHANGELOG.md)

## License

MIT License – free for commercial and personal use.

## Credits

Inspired by PowerDNS-Admin. Built for BIND9 administrators who want a modern, lightweight alternative.

---

**PHP-Bind-Dashboard** – Professional BIND9 management made simple.
