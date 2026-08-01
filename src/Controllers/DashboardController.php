<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\BindManager;
use App\Services\ActivityLogger;
use App\Helpers\Database;

final class DashboardController
{
    private AuthService $auth;
    private BindManager $bind;
    private ActivityLogger $logger;
    private string $root;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->bind = new BindManager();
        $this->logger = new ActivityLogger();
        $this->root = dirname(__DIR__, 2);
        $this->auth->requireLogin();
    }

    public function index(): void
    {
        $zones = $this->bind->listZones();
        $recentLogs = $this->logger->recent(10);
        $user = $this->auth->user();

        $stats = [
            'zones'   => count($zones),
            'users'   => $this->countUsers(),
            'logs'    => count($recentLogs),
        ];

        require $this->root . '/templates/pages/dashboard.php';
    }

    private function countUsers(): int
    {
        $db = Database::get();
        $stmt = $db->query('SELECT COUNT(*) FROM users WHERE is_active = 1');
        return (int) $stmt->fetchColumn();
    }
}
