<?php

declare(strict_types=1);

/**
 * PHP-Bind-Dashboard Front Controller
 */

$root = dirname(__DIR__);

// Autoloader
require $root . '/src/Helpers/Autoloader.php';
App\Helpers\Autoloader::register($root . '/src');

use App\Helpers\Config;
use App\Helpers\Security;
use App\Services\AuthService;

// Load config
Config::load($root . '/config/config.php');

// Timezone
date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));

// Session
$sessionName = (string) Config::get('app.session_name', 'PBDSESSID');
session_name($sessionName);
session_set_cookie_params([
    'lifetime' => (int) Config::get('app.session_lifetime', 7200),
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Security headers
Security::setSecurityHeaders();

// Error handling
if (Config::get('app.debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// Simple router
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = rtrim((string) $uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$auth = new AuthService();

// Public routes
$publicRoutes = ['/login', '/logout'];

if (!in_array($uri, $publicRoutes, true) && !$auth->check()) {
    header('Location: /login');
    exit;
}

// Route dispatch
try {
    switch (true) {
        case $uri === '/login':
            $ctrl = new App\Controllers\AuthController();
            if ($method === 'POST') {
                $ctrl->login();
            } else {
                $ctrl->showLogin();
            }
            break;

        case $uri === '/logout':
            (new App\Controllers\AuthController())->logout();
            break;

        case $uri === '/' || $uri === '/dashboard':
            (new App\Controllers\DashboardController())->index();
            break;

        case $uri === '/zones':
            $ctrl = new App\Controllers\ZoneController();
            if ($method === 'POST') {
                $ctrl->store();
            } else {
                $ctrl->index();
            }
            break;

        case preg_match('#^/zones/(\d+)$#', $uri, $m) === 1:
            (new App\Controllers\ZoneController())->show((int) $m[1]);
            break;

        case preg_match('#^/zones/(\d+)/delete$#', $uri, $m) === 1 && $method === 'POST':
            (new App\Controllers\ZoneController())->destroy((int) $m[1]);
            break;

        case $uri === '/users':
            (new App\Controllers\UserController())->index();
            break;

        case $uri === '/logs':
            (new App\Controllers\LogController())->index();
            break;

        case $uri === '/settings':
            (new App\Controllers\SettingsController())->index();
            break;

        default:
            http_response_code(404);
            require $root . '/templates/pages/404.php';
            break;
    }
} catch (Throwable $e) {
    if (Config::get('app.debug', false)) {
        echo '<pre>' . Security::escape($e->getMessage()) . "\n" . Security::escape($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        echo 'Internal Server Error';
    }
}
