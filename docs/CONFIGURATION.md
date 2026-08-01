# Configuration Reference

Primary configuration file: `config/config.php`

Optional local overrides (recommended for production secrets):

```php
// config/config.local.php
<?php
return [
    'app' => [
        'url'   => 'https://dns.yourdomain.com',
        'debug' => false,
    ],
    'bind' => [
        'zones_dir'  => '/etc/bind/zones',
        'named_conf' => '/etc/bind/named.conf.local',
        'rndc_path'  => '/usr/sbin/rndc',
    ],
];
```

## Key Options

| Key | Description | Default |
|-----|-------------|---------|
| `app.name` | Application display name | PHP-Bind-Dashboard |
| `app.debug` | Show detailed errors | false |
| `app.timezone` | PHP timezone | Asia/Jakarta |
| `database.path` | Absolute path to SQLite file | `database/phpbind.db` |
| `bind.zones_dir` | Directory for zone files | `/etc/bind/zones` |
| `bind.named_conf` | File to append zone statements | `/etc/bind/named.conf.local` |
| `bind.allow_write` | Allow creating/writing zone files | true |
| `security.max_login_attempts` | Lockout threshold | 5 |
| `security.lockout_time` | Lockout duration (seconds) | 900 |

After changing configuration, restart PHP-FPM if using opcache.
