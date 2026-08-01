<?php
/** @var array $user */
/** @var string $pageTitle */
/** @var string $content */
use App\Helpers\Security;
use App\Helpers\Config;

$appName = Config::get('app.name', 'PHP-Bind-Dashboard');
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="PHP-Bind-Dashboard - Modern BIND9 DNS Administration">
    <meta name="theme-color" content="#0d6efd">
    <title><?= Security::escape($pageTitle) ?> · <?= Security::escape($appName) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/app.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
    <div class="d-flex" id="wrapper">
        <nav id="sidebar" class="bg-dark text-white sidebar collapse collapse-horizontal show" style="min-width:260px;max-width:260px;">
            <div class="p-3 border-bottom border-secondary">
                <a href="/dashboard" class="text-white text-decoration-none d-flex align-items-center gap-2">
                    <i class="fa-solid fa-server fa-lg text-primary"></i>
                    <span class="fw-bold fs-5">PHP-Bind</span>
                </a>
                <small class="text-secondary d-block mt-1">Dashboard</small>
            </div>
            <ul class="nav flex-column p-2">
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= ($pageTitle ?? '') === 'Dashboard' ? 'active text-white bg-primary rounded' : '' ?>" href="/dashboard">
                        <i class="fa-solid fa-gauge-high me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= str_contains($pageTitle ?? '', 'Zone') ? 'active text-white bg-primary rounded' : '' ?>" href="/zones">
                        <i class="fa-solid fa-globe me-2"></i> Zones
                    </a>
                </li>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= ($pageTitle ?? '') === 'Users' ? 'active text-white bg-primary rounded' : '' ?>" href="/users">
                        <i class="fa-solid fa-users me-2"></i> Users
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= ($pageTitle ?? '') === 'Activity Logs' ? 'active text-white bg-primary rounded' : '' ?>" href="/logs">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i> Activity Logs
                    </a>
                </li>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= ($pageTitle ?? '') === 'Settings' ? 'active text-white bg-primary rounded' : '' ?>" href="/settings">
                        <i class="fa-solid fa-gear me-2"></i> Settings
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="mt-auto p-3 border-top border-secondary small text-secondary">
                v<?= Security::escape((string) Config::get('app.version', '1.0.0')) ?>
            </div>
        </nav>

        <div id="page-content" class="flex-grow-1">
            <nav class="navbar navbar-expand-lg bg-body border-bottom sticky-top px-3">
                <button class="btn btn-outline-secondary d-lg-none me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <span class="navbar-brand mb-0 h1 fs-5"><?= Security::escape($pageTitle) ?></span>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary" id="themeToggle" type="button" title="Toggle dark mode">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user me-1"></i>
                            <?= Security::escape($user['username'] ?? 'User') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted"><?= Security::escape($user['role'] ?? '') ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="container-fluid p-3 p-md-4">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="/assets/js/app.min.js"></script>
</body>
</html>
