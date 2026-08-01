<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Helpers\Config;

final class SettingsController
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
        $user = $this->auth->user();
        $config = Config::all();
        require $this->root . '/templates/pages/settings.php';
    }
}
