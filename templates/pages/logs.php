<?php
use App\Helpers\Security;
$pageTitle = 'Activity Logs';
ob_start();
?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <span class="fw-semibold">Recent Activity (last 100)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No logs</td></tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="small text-nowrap"><?= Security::escape($log['created_at'] ?? '') ?></td>
                    <td><?= Security::escape($log['username'] ?? 'system') ?></td>
                    <td><code><?= Security::escape($log['action']) ?></code></td>
                    <td class="small"><?= Security::escape(($log['target_type'] ?? '') . ' ' . ($log['target_id'] ?? '')) ?></td>
                    <td class="small font-monospace"><?= Security::escape($log['ip_address'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
