<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\ActivityLogger;

final class LogController
{
    private AuthService $auth;
    private ActivityLogger $logger;
    private string $root;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->logger = new ActivityLogger();
        $this->root = dirname(__DIR__, 2);
        $this->auth->requireLogin();
    }

    public function index(): void
    {
        $logs = $this->logger->recent(100);
        $user = $this->auth->user();
        require $this->root . '/templates/pages/logs.php';
    }
}
