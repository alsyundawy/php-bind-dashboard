<?php
use App\Helpers\Security;
$pageTitle = 'Zone: ' . rtrim($zone['name'] ?? '', '.');
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h2 class="h5 mb-0"><?= Security::escape(rtrim($zone['name'], '.')) ?></h2>
        <small class="text-muted">Type: <?= Security::escape($zone['type']) ?> · Serial: <?= (int) $zone['serial'] ?></small>
    </div>
    <a href="/zones" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between">
        <span class="fw-semibold">Resource Records</span>
        <span class="badge text-bg-light"><?= count($records) ?> records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle font-monospace small">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>TTL</th>
                        <th>Type</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No records parsed or zone file empty</td></tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= Security::escape($r['name'] ?? '') ?></td>
                        <td><?= (int) ($r['ttl'] ?? 0) ?></td>
                        <td><span class="badge text-bg-primary"><?= Security::escape($r['type'] ?? '') ?></span></td>
                        <td class="text-break"><?= Security::escape($r['rdata'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<p class="text-muted small mt-3">
    <i class="fa-solid fa-info-circle me-1"></i>
    Record editing is available in the full version. Zone file location:
    <code><?= Security::escape($zone['file_path'] ?? 'N/A') ?></code>
</p>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
