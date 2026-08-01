<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Simple PSR-4 style autoloader for App\ namespace
 */
final class Autoloader
{
    private static string $baseDir;

    public static function register(string $baseDir): void
    {
        self::$baseDir = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR;
        spl_autoload_register([self::class, 'load']);
    }

    public static function load(string $class): void
    {
        if (strpos($class, 'App\\') !== 0) {
            return;
        }

        $relative = str_replace('App\\', '', $class);
        $relative = str_replace('\\', DIRECTORY_SEPARATOR, $relative);
        $file = self::$baseDir . $relative . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
}
