<?php
use App\Helpers\Security;
$pageTitle = 'Settings';
ob_start();
?>
<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Application</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8"><?= Security::escape($config['app']['name'] ?? '') ?></dd>
                    <dt class="col-sm-4">Version</dt>
                    <dd class="col-sm-8"><?= Security::escape($config['app']['version'] ?? '') ?></dd>
                    <dt class="col-sm-4">Environment</dt>
                    <dd class="col-sm-8"><?= Security::escape($config['app']['env'] ?? '') ?></dd>
                    <dt class="col-sm-4">Timezone</dt>
                    <dd class="col-sm-8"><?= Security::escape($config['app']['timezone'] ?? '') ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">BIND9</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Zones Dir</dt>
                    <dd class="col-sm-8"><code class="small"><?= Security::escape($config['bind']['zones_dir'] ?? '') ?></code></dd>
                    <dt class="col-sm-4">named.conf</dt>
                    <dd class="col-sm-8"><code class="small"><?= Security::escape($config['bind']['named_conf'] ?? '') ?></code></dd>
                    <dt class="col-sm-4">rndc</dt>
                    <dd class="col-sm-8"><code class="small"><?= Security::escape($config['bind']['rndc_path'] ?? '') ?></code></dd>
                    <dt class="col-sm-4">Write enabled</dt>
                    <dd class="col-sm-8"><?= !empty($config['bind']['allow_write']) ? 'Yes' : 'No' ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
<p class="text-muted small mt-3">Edit <code>config/config.php</code> or <code>config/config.local.php</code> to change settings. Restart PHP-FPM after changes.</p>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
