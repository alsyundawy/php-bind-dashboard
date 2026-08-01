<?php
use App\Helpers\Security;
use App\Helpers\Config;
$appName = Config::get('app.name', 'PHP-Bind-Dashboard');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · <?= Security::escape($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/app.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-8 col-md-5 col-lg-4">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <i class="fa-solid fa-server fa-3x text-primary mb-3"></i>
                            <h1 class="h4 fw-bold"><?= Security::escape($appName) ?></h1>
                            <p class="text-muted small">BIND9 Administration Console</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2 small" role="alert">
                                <i class="fa-solid fa-circle-exclamation me-1"></i>
                                <?= Security::escape($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="/login" autocomplete="off">
                            <?= Security::csrfField() ?>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username"
                                           required autofocus autocomplete="username">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                           required autocomplete="current-password">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In
                            </button>
                        </form>
                    </div>
                </div>
                <p class="text-center text-muted small mt-3 mb-0">
                    &copy; <?= date('Y') ?> PHP-Bind-Dashboard
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.min.js"></script>
</body>
</html>
