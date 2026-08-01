# Installation Guide – PHP-Bind-Dashboard

## Prerequisites

- PHP 8.2+ with extensions: `pdo_sqlite`, `mbstring`, `json`, `openssl`
- Nginx + PHP-FPM
- BIND9 (`named`)
- Write access for the web server user to:
  - `database/` directory
  - BIND zones directory (e.g. `/etc/bind/zones`)

## Steps

1. Copy the project to `/var/www/php-bind-dashboard` (or preferred location).

2. Set ownership:
   ```bash
   chown -R www-data:www-data /var/www/php-bind-dashboard
   chmod -R 755 /var/www/php-bind-dashboard
   chmod -R 775 /var/www/php-bind-dashboard/database
   ```

3. Run the installer:
   ```bash
   cd /var/www/php-bind-dashboard
   php scripts/install.php
   ```

4. Edit `config/config.php` (or create `config/config.local.php`) and set:
   - `bind.zones_dir`
   - `bind.named_conf`
   - `bind.rndc_path`
   - `app.url`

5. Configure Nginx (see `docs/nginx.conf.example`).

6. Ensure the web server can write zone files:
   ```bash
   usermod -aG bind www-data
   chown -R root:bind /etc/bind/zones
   chmod 775 /etc/bind/zones
   ```

7. Open the site and log in with `admin` / `ChangeMe123!`. Change the password immediately.

## Security Notes

- Never expose the `database/` folder via the web server.
- Document root **must** be `public/`.
- Use HTTPS in production.
- Change default password and consider restricting access by IP if possible.
