<?php

declare(strict_types=1);

/**
 * PHP-Bind-Dashboard Installer
 * Run once: php scripts/install.php
 */

$root = dirname(__DIR__);
$configFile = $root . '/config/config.php';
$schemaFile = $root . '/database/schema.sql';
$dbPath = $root . '/database/phpbind.db';

echo "PHP-Bind-Dashboard Installer\n";
echo "============================\n\n";

if (!file_exists($configFile)) {
    fwrite(STDERR, "ERROR: config/config.php not found.\n");
    exit(1);
}

$config = require $configFile;
if (!is_array($config) || empty($config['database']['path']) || !is_string($config['database']['path'])) {
    fwrite(STDERR, "ERROR: Invalid database.path in config.\n");
    exit(1);
}
$dbPath = $config['database']['path'];

if (file_exists($dbPath)) {
    echo "Database already exists at: $dbPath\n";
    echo "Do you want to re-initialize? This will DELETE all data! (yes/no): ";
    $handle = fopen('php://stdin', 'r');
    $line = trim(fgets($handle) ?: '');
    fclose($handle);
    if (strtolower($line) !== 'yes') {
        echo "Aborted.\n";
        exit(0);
    }
    unlink($dbPath);
}

// Create database directory if needed
$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) {
    if (!mkdir($dbDir, 0775, true) && !is_dir($dbDir)) {
        fwrite(STDERR, "ERROR: Cannot create database directory: $dbDir\n");
        exit(1);
    }
}

try {
    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $schema = file_get_contents($schemaFile);
    if ($schema === false) {
        throw new RuntimeException('Cannot read schema.sql');
    }

    $pdo->exec($schema);
    echo "Schema applied successfully.\n";

    // Create default admin
    $password = 'ChangeMe123!';
    $hash = password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost'   => 4,
        'threads'     => 1,
    ]);

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)'
    );
    $stmt->execute(['admin', 'admin@localhost', $hash, 'admin']);

    echo "Default administrator created:\n";
    echo "  Username : admin\n";
    echo "  Password : ChangeMe123!\n";
    echo "  IMPORTANT: Change this password immediately after first login!\n\n";

    // Seed default settings
    $settings = [
        ['app_installed', '1'],
        ['default_ttl', '3600'],
        ['theme', 'auto'],
    ];
    $stmt = $pdo->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)');
    foreach ($settings as $s) {
        $stmt->execute($s);
    }

    // Ensure log directory
    $logDir = $root . '/database/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }

    echo "Installation completed successfully.\n";
    echo "Database location: $dbPath\n";
    echo "Next steps:\n";
    echo "  1. Configure Nginx to point to public/\n";
    echo "  2. Ensure www-data can write to database/ and zones directory\n";
    echo "  3. Edit config/config.php for BIND paths\n";
    echo "  4. Login and change the admin password\n";

} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . "\n");
    exit(1);
}
