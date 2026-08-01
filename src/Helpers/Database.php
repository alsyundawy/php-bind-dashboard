<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;

/**
 * SQLite PDO singleton wrapper
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $path = Config::get('database.path');
        $optionsRaw = Config::get('database.options', []);
        $options = is_array($optionsRaw) ? $optionsRaw : [];

        if (!is_string($path) || $path === '') {
            throw new \RuntimeException('Database path not configured');
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException('Cannot create database directory: ' . $dir);
            }
        }

        try {
            self::$pdo = new PDO('sqlite:' . $path, null, null, $options);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
            self::$pdo->exec('PRAGMA journal_mode = WAL');
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        return self::$pdo;
    }

    public static function get(): PDO
    {
        return self::connect();
    }

    /** Prevent cloning / unserialization */
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new \RuntimeException('Cannot unserialize Database singleton');
    }
}
