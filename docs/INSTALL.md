# Installation & Deployment Guide

Complete production-ready instructions for PHP-Bind-Dashboard.

---

## 1. Prerequisites

| Component | Version / Notes |
|-----------|-----------------|
| PHP | 8.2 or 8.3+ |
| Extensions | `pdo_sqlite`, `mbstring`, `json`, `openssl` |
| Web server | Nginx + PHP-FPM (recommended) or Apache |
| DNS | BIND9 (`named`) |
| Optional | `rndc` for live zone reload |

Verify PHP extensions:

```bash
php -m | grep -E 'pdo_sqlite|mbstring|json|openssl'
```

---

## 2. Obtain the Code

```bash
cd /var/www
git clone https://github.com/alsyundawy/php-bind-dashboard.git
cd php-bind-dashboard
```

---

## 3. Permissions

```bash
sudo chown -R www-data:www-data /var/www/php-bind-dashboard
sudo find /var/www/php-bind-dashboard -type d -exec chmod 755 {} \;
sudo find /var/www/php-bind-dashboard -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/php-bind-dashboard/database
```

The web-server user must also write BIND zone files:

```bash
sudo usermod -aG bind www-data
sudo mkdir -p /etc/bind/zones
sudo chown root:bind /etc/bind/zones
sudo chmod 775 /etc/bind/zones
sudo chown root:bind /etc/bind/named.conf.local
sudo chmod 664 /etc/bind/named.conf.local
```

---

## 4. Initialise the Database

```bash
cd /var/www/php-bind-dashboard
sudo -u www-data php scripts/install.php
```

Default administrator:

- **Username:** `admin`
- **Password:** `ChangeMe123!`

> Change this password immediately after the first login.

---

## 5. Configuration

```bash
cp config/config.php config/config.local.php
```

Key settings:

```php
'app' => [
    'url'   => 'https://dns.example.com',
    'debug' => false,
],
'bind' => [
    'zones_dir'  => '/etc/bind/zones',
    'named_conf' => '/etc/bind/named.conf.local',
    'rndc_path'  => '/usr/sbin/rndc',
    'allow_write'=> true,
],
```

---

## 6. Nginx Configuration

Document root **must** be `public/`.

See `docs/nginx.conf.example` for a full server block.

```bash
sudo ln -s /etc/nginx/sites-available/php-bind-dashboard /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

## 7. HTTPS (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d dns.example.com
```

---

## 8. BIND9 Notes

Ensure `named.conf` includes:

```
include "/etc/bind/named.conf.local";
```

After creating zones:

```bash
sudo rndc reload
# or
sudo systemctl reload bind9
```

---

## 9. Post-Install Checklist

- [ ] Logged in and changed the default admin password  
- [ ] `app.debug = false`  
- [ ] HTTPS enabled  
- [ ] Zones directory writable by the web user  
- [ ] Sensitive paths blocked in the web server  

---

## Troubleshooting

| Symptom | Likely cause |
|---------|--------------|
| Blank page / 500 | PHP-FPM log; ensure `pdo_sqlite` is loaded |
| Cannot create zone | Permissions on `zones_dir` or `allow_write = false` |
| Login lockout | Wait for lockout window or reset attempts in SQLite |
