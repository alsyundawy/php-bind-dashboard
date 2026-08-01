<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Helpers\Database;

final class UserController
{
    private AuthService $auth;
    private string $root;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->root = dirname(__DIR__, 2);
        $this->auth->requireAdmin();
    }

    public function index(): void
    {
        $db = Database::get();
        $stmt = $db->query(
            'SELECT id, username, email, role, is_active, last_login_at, created_at
             FROM users ORDER BY username ASC'
        );
        $users = $stmt->fetchAll() ?: [];
        $user = $this->auth->user();

        require $this->root . '/templates/pages/users.php';
    }
}
