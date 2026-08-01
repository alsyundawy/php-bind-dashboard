<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Simple PSR-4 style autoloader for App\ namespace
 */
final class Autoloader
{
    private static string $baseDir = '';

    public static function register(string $baseDir): void
    {
        self::$baseDir = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR;
        spl_autoload_register([self::class, 'load']);
    }

    public static function load(string $class): void
    {
        if (!str_starts_with($class, 'App\\')) {
            return;
        }

        $relative = substr($class, 4); // strip "App\\"
        $relative = str_replace('\\', DIRECTORY_SEPARATOR, $relative);
        $file = self::$baseDir . $relative . '.php';

        if (self::$baseDir !== '' && is_file($file)) {
            require $file;
        }
    }
}
