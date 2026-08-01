<?php

declare(strict_types=1);

/**
 * PHP-Bind-Dashboard Configuration
 * Copy to config.local.php for overrides (not tracked in git)
 */

return [
    'app' => [
        'name'            => 'PHP-Bind-Dashboard',
        'version'         => '1.0.1',
        'env'             => 'production', // development | production
        'debug'           => false,
        'url'             => 'https://dns.example.com',
        'timezone'        => 'Asia/Jakarta',
        'locale'          => 'en_US',
        'session_name'    => 'PBDSESSID',
        'session_lifetime'=> 7200, // seconds
        'csrf_token_name' => '_csrf_token',
    ],

    'database' => [
        'driver'   => 'sqlite',
        'path'     => __DIR__ . '/../database/phpbind.db',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],

    'bind' => [
        // Directory where zone files are stored
        'zones_dir'       => '/etc/bind/zones',
        // Path to named.conf include file for zones
        'named_conf'      => '/etc/bind/named.conf.local',
        // Path to rndc binary (optional, for live reload)
        'rndc_path'       => '/usr/sbin/rndc',
        // Allow the web app to write zone files (must be writable by www-data)
        'allow_write'     => true,
        // Default SOA values
        'default_soa'     => [
            'primary'     => 'ns1.example.com.',
            'email'       => 'hostmaster.example.com.',
            'refresh'     => 86400,
            'retry'       => 7200,
            'expire'      => 3600000,
            'minimum'     => 86400,
            'ttl'         => 3600,
        ],
        // Default NS records when creating a new zone
        'default_ns'      => [
            'ns1.example.com.',
            'ns2.example.com.',
        ],
    ],

    'security' => [
        'password_algo'   => PASSWORD_ARGON2ID,
        'password_options'=> [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 1,
        ],
        'max_login_attempts' => 5,
        'lockout_time'       => 900, // 15 minutes
        'allowed_hosts'      => [], // empty = all
    ],

    'logging' => [
        'enabled'  => true,
        'path'     => __DIR__ . '/../database/logs',
        'level'    => 'info', // debug | info | warning | error
    ],
];
