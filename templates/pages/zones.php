<?php
use App\Helpers\Security;
$pageTitle = 'Zones';
ob_start();
?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= Security::escape($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= Security::escape($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h2 class="h5 mb-0">DNS Zones</h2>
    <?php if (in_array($user['role'] ?? '', ['admin', 'operator'], true)): ?>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createZoneModal">
        <i class="fa-solid fa-plus me-1"></i> New Zone
    </button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Zone Name</th>
                        <th>Type</th>
                        <th>Reverse</th>
                        <th>Serial</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($zones)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">No zones configured. Create your first zone.</td></tr>
                <?php else: ?>
                    <?php foreach ($zones as $z): ?>
                    <tr>
                        <td>
                            <a href="/zones/<?= (int) $z['id'] ?>" class="fw-medium text-decoration-none">
                                <?= Security::escape(rtrim($z['name'], '.')) ?>
                            </a>
                        </td>
                        <td><span class="badge text-bg-secondary"><?= Security::escape($z['type']) ?></span></td>
                        <td><?= !empty($z['is_reverse']) ? '<i class="fa-solid fa-check text-success"></i>' : '—' ?></td>
                        <td class="font-monospace small"><?= (int) $z['serial'] ?></td>
                        <td class="small text-muted"><?= Security::escape(substr($z['created_at'] ?? '', 0, 10)) ?></td>
                        <td class="text-end">
                            <a href="/zones/<?= (int) $z['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <?php if (($user['role'] ?? '') === 'admin'): ?>
                            <form method="post" action="/zones/<?= (int) $z['id'] ?>/delete" class="d-inline"
                                  onsubmit="return confirm('Delete zone <?= Security::escape(rtrim($z['name'], '.')) ?>? This cannot be undone.');">
                                <?= Security::csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Zone Modal -->
<div class="modal fade" id="createZoneModal" tabindex="-1" aria-labelledby="createZoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="/zones" class="modal-content">
            <?= Security::csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title" id="createZoneModalLabel">Create New Zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="zoneName" class="form-label">Zone Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="zoneName" name="name"
                           placeholder="example.com" required pattern="[a-zA-Z0-9.\-]+">
                    <div class="form-text">FQDN without trailing dot (e.g. example.com)</div>
                </div>
                <div class="mb-3">
                    <label for="zoneType" class="form-label">Type</label>
                    <select class="form-select" id="zoneType" name="type">
                        <option value="master" selected>Master</option>
                        <option value="slave">Slave</option>
                        <option value="forward">Forward</option>
                    </select>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="isReverse" name="is_reverse" value="1">
                    <label class="form-check-label" for="isReverse">Reverse zone (in-addr.arpa / ip6.arpa)</label>
                </div>
                <div class="mb-0">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Zone</button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
