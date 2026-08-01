<?php
use App\Helpers\Security;
$pageTitle = 'Dashboard';
ob_start();
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                    <i class="fa-solid fa-globe fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Zones</div>
                    <div class="fs-4 fw-bold"><?= (int) ($stats['zones'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                    <i class="fa-solid fa-users fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Users</div>
                    <div class="fs-4 fw-bold"><?= (int) ($stats['users'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                    <i class="fa-solid fa-clock-rotate-left fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Recent Events</div>
                    <div class="fs-4 fw-bold"><?= (int) ($stats['logs'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                    <i class="fa-solid fa-shield-halved fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Role</div>
                    <div class="fs-5 fw-bold text-capitalize"><?= Security::escape($user['role'] ?? '-') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="fa-solid fa-globe me-2"></i>Zones</span>
                <a href="/zones" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Serial</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($zones)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No zones yet</td></tr>
                        <?php else: ?>
                            <?php foreach (array_slice($zones, 0, 8) as $z): ?>
                            <tr>
                                <td>
                                    <a href="/zones/<?= (int) $z['id'] ?>" class="text-decoration-none">
                                        <?= Security::escape(rtrim($z['name'], '.')) ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-secondary"><?= Security::escape($z['type']) ?></span></td>
                                <td class="font-monospace small"><?= (int) $z['serial'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <span class="fw-semibold"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Activity</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                <?php if (empty($recentLogs)): ?>
                    <li class="list-group-item text-muted text-center py-4">No activity yet</li>
                <?php else: ?>
                    <?php foreach ($recentLogs as $log): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-medium small"><?= Security::escape($log['action']) ?></div>
                            <div class="text-muted smaller">
                                <?= Security::escape($log['username'] ?? 'system') ?>
                                · <?= Security::escape($log['created_at'] ?? '') ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
