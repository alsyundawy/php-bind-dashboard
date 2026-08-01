<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Security;
use App\Services\AuthService;
use App\Services\ActivityLogger;

final class AuthController
{
    private AuthService $auth;
    private ActivityLogger $logger;
    private string $root;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->logger = new ActivityLogger();
        $this->root = dirname(__DIR__, 2);
    }

    public function showLogin(): void
    {
        if ($this->auth->check()) {
            header('Location: /dashboard');
            exit;
        }
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        require $this->root . '/templates/pages/login.php';
    }

    public function login(): void
    {
        $token = isset($_POST['_csrf_token']) && is_string($_POST['_csrf_token'])
            ? $_POST['_csrf_token']
            : null;
        if (!Security::validateCsrfToken($token)) {
            $_SESSION['login_error'] = 'Invalid security token. Please try again.';
            header('Location: /login');
            exit;
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $_SESSION['login_error'] = 'Username and password are required.';
            header('Location: /login');
            exit;
        }

        if ($this->auth->attempt($username, $password)) {
            $user = $this->auth->user();
            $this->logger->log('login', $user['id'] ?? null, 'user', (string) ($user['id'] ?? ''), [
                'username' => $username,
            ]);
            header('Location: /dashboard');
            exit;
        }

        $_SESSION['login_error'] = 'Invalid credentials or account locked.';
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        $user = $this->auth->user();
        if ($user) {
            $this->logger->log('logout', $user['id'], 'user', (string) $user['id']);
        }
        $this->auth->logout();
        header('Location: /login');
        exit;
    }
}
