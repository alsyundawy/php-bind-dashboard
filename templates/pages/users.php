<?php
use App\Helpers\Security;
$pageTitle = 'Users';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">User Management</h2>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td class="fw-medium"><?= Security::escape($u['username']) ?></td>
                    <td><?= Security::escape($u['email']) ?></td>
                    <td><span class="badge text-bg-secondary text-capitalize"><?= Security::escape($u['role']) ?></span></td>
                    <td>
                        <?php if ((int) $u['is_active']): ?>
                            <span class="badge text-bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge text-bg-danger">Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted"><?= Security::escape($u['last_login_at'] ?? '—') ?></td>
                    <td class="small text-muted"><?= Security::escape(substr($u['created_at'] ?? '', 0, 10)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
