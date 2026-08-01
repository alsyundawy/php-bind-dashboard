<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Security;
use App\Services\AuthService;
use App\Services\BindManager;
use App\Services\ActivityLogger;

final class ZoneController
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
        $user = $this->auth->user();
        $error = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require $this->root . '/templates/pages/zones.php';
    }

    public function show(int $id): void
    {
        $zone = $this->bind->getZone($id);
        if ($zone === null) {
            http_response_code(404);
            require $this->root . '/templates/pages/404.php';
            return;
        }

        $records = [];
        if (!empty($zone['file_path'])) {
            $records = $this->bind->parseZoneFile($zone['file_path']);
        }

        $user = $this->auth->user();
        require $this->root . '/templates/pages/zone_show.php';
    }

    public function store(): void
    {
        if (!$this->auth->isOperatorOrHigher()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $token = isset($_POST['_csrf_token']) && is_string($_POST['_csrf_token'])
            ? $_POST['_csrf_token']
            : null;
        if (!Security::validateCsrfToken($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            header('Location: /zones');
            exit;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $type = trim((string) ($_POST['type'] ?? 'master'));
        $isReverse = !empty($_POST['is_reverse']);
        $notes = trim((string) ($_POST['notes'] ?? ''));

        if ($name === '') {
            $_SESSION['flash_error'] = 'Zone name is required.';
            header('Location: /zones');
            exit;
        }

        try {
            $user = $this->auth->user();
            $zoneId = $this->bind->createZone($name, $type, $isReverse, $user['id'] ?? null, $notes);
            $this->logger->log('create_zone', $user['id'] ?? null, 'zone', (string) $zoneId, [
                'name' => $name,
                'type' => $type,
            ]);
            $_SESSION['flash_success'] = 'Zone created successfully.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to create zone. Check server logs and configuration.';
        }

        header('Location: /zones');
        exit;
    }

    public function destroy(int $id): void
    {
        if (!$this->auth->isAdmin()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $token = isset($_POST['_csrf_token']) && is_string($_POST['_csrf_token'])
            ? $_POST['_csrf_token']
            : null;
        if (!Security::validateCsrfToken($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            header('Location: /zones');
            exit;
        }

        $zone = $this->bind->getZone($id);
        if ($zone === null) {
            $_SESSION['flash_error'] = 'Zone not found.';
            header('Location: /zones');
            exit;
        }

        $this->bind->deleteZone($id);
        $user = $this->auth->user();
        $this->logger->log('delete_zone', $user['id'] ?? null, 'zone', (string) $id, [
            'name' => $zone['name'],
        ]);
        $_SESSION['flash_success'] = 'Zone deleted.';
        header('Location: /zones');
        exit;
    }
}
