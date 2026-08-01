<?php
use App\Helpers\Security;
use App\Helpers\Config;
$pageTitle = '404 Not Found';
$appName = Config::get('app.name', 'PHP-Bind-Dashboard');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 · <?= Security::escape($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary d-flex align-items-center min-vh-100">
    <div class="container text-center">
        <h1 class="display-1 fw-bold text-primary">404</h1>
        <p class="fs-4">Page not found</p>
        <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>
