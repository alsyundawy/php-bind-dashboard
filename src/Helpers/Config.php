<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Configuration loader with optional local override
 */
final class Config
{
    private static ?array $data = null;

    public static function load(string $configPath): void
    {
        if (!is_file($configPath)) {
            throw new \RuntimeException('Config file not found: ' . $configPath);
        }

        $loaded = require $configPath;
        if (!is_array($loaded)) {
            throw new \RuntimeException('Config file must return an array');
        }
        self::$data = $loaded;

        $local = dirname($configPath) . '/config.local.php';
        if (is_file($local)) {
            $override = require $local;
            if (is_array($override)) {
                self::$data = array_replace_recursive(self::$data, $override);
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$data === null) {
            throw new \RuntimeException('Config not loaded');
        }

        $keys = explode('.', $key);
        $value = self::$data;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function all(): array
    {
        return self::$data ?? [];
    }
}
