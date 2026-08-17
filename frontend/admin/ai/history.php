<?php declare(strict_types=1);

require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\Ai\AiContentService;


AuthMiddleware::requireAuth();

$page_title = 'AI Generation History';
include __DIR__ . '/../components/admin-header.php';

// Defensive: if auth was bypassed, stop cleanly.
if (!AuthMiddleware::check()) {
    http_response_code(401);
    include __DIR__ . '/../components/footer.php';
    exit;
}

$csrf       = Security::generateCsrfToken();
$provider   = AiContentService::resolveProvider();
$configured = $provider->isConfigured();
$history    = [];
if ($configured) {
    try {
        $service = new AiContentService($provider);
        $history = $service->getRecentHistory(50, AuthMiddleware::userId());
    } catch (\Throwable $e) {
        error_log('[AI history] History unavailable: ' . $e->getMessage());
        $history = [];
    }
}

$baseUrl     = $admin_url ?? (defined('CONNECTMYUNI_BASE_URL') ? CONNECTMYUNI_BASE_URL . 'frontend/admin/' : '/');
$generateUrl = $baseUrl . 'ai/generate.php';
$improveUrl  = $baseUrl . 'ai/improve.php';
?>
<script>
window.CMU_AI = {
    csrf: <?php echo json_encode($csrf); ?>,
    generateUrl: <?php echo json_encode($generateUrl); ?>,
    improveUrl: <?php echo json_encode($improveUrl); ?>
};
</script>

<?php include_once __DIR__ . '/../components/ai-generator-core.php'; ?>

<div class="page-heading" style="margin-bottom:20px;">
    <h1><i class="fas fa-clock-rotate-left" style="color:var(--accent);margin-right:8px;"></i> AI Generation History</h1>
    <p class="form-hint">Stored generations and their approval status. Approval records history only &mdash; it never writes to CRUD tables.</p>
</div>

<?php if (!$configured): ?>
<div class="card">
    <div class="card-body" style="padding:18px 22px;">
        <p class="form-hint" style="margin-bottom:0;">AI is installed but disabled until <code>AI_API_KEY</code> is set in the server <code>.env</code>.</p>
    </div>
</div>
<?php elseif (empty($history)): ?>
<div class="card">
    <div class="card-body" style="padding:18px 22px;">
        <p class="form-hint" style="margin-bottom:0;">No generation history yet.</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body" style="padding:18px 22px;overflow-x:auto;">
        <table class="table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th align="left" style="padding:8px;font-size:.78rem;">ID</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Type</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Provider</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Model</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Status</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Created</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Admin</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:8px;"><?php echo (int) ($h['id'] ?? 0); ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars((string) ($h['content_type'] ?? '')); ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars((string) ($h['provider'] ?? '')) ?: '—'; ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars((string) ($h['model'] ?? '')) ?: '—'; ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars((string) ($h['status'] ?? 'unknown')); ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars((string) ($h['created_at'] ?? '')); ?></td>
                        <td style="padding:8px;"><?php echo htmlspecialchars((string) ($h['username'] ?? '')); ?></td>
                        <td style="padding:8px;">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="ConnectMyUni.Ai.copyGenerated(<?php echo (int) ($h['id'] ?? 0); ?>);"><i class="fas fa-copy"></i> Copy</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>
