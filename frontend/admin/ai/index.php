<?php declare(strict_types=1);

require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\Ai\AiContentService;


AuthMiddleware::requireAuth();

$page_title = 'AI Content Generator';
include __DIR__ . '/../components/admin-header.php';

// Defensive: if auth was bypassed, stop cleanly (no fatal, no data leak).
if (!AuthMiddleware::check()) {
    http_response_code(401);
    include __DIR__ . '/../components/footer.php';
    exit;
}

$csrf         = Security::generateCsrfToken();
$provider     = AiContentService::resolveProvider();
$configured   = $provider->isConfigured();
$providerName = $configured ? $provider->getName() : 'Not configured';
$types        = AiContentService::allowedContentTypes();

$history = [];
if ($configured) {
    try {
        $service = new AiContentService($provider);
        $history = $service->getRecentHistory(20, AuthMiddleware::userId());
    } catch (\Throwable $e) {
        error_log('[AI index] History unavailable: ' . $e->getMessage());
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

<script>
// Standalone AI page: there is no parent CRUD form, so "Use This Content" cannot
// populate one here. Disable the button with an explanatory tooltip.
(function () {
    function disableUse() {
        var btn = document.getElementById('cmu-ai-use');
        if (btn) {
            btn.disabled = true;
            btn.title = 'Available only when opened from a CRUD create/edit form.';
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', disableUse);
    } else {
        disableUse();
    }
})();
</script>

<div class="page-heading" style="margin-bottom:20px;">
    <h1><i class="fas fa-wand-magic-sparkles" style="color:var(--accent);margin-right:8px;"></i> AI Content Generator</h1>
    <p class="form-hint">Generate, improve, and prepare content with AI. All output must be reviewed before publishing. AI never auto-publishes.</p>
</div>

<?php if (!$configured): ?>
<div class="card">
    <div class="card-body" style="padding:18px 22px;border-color:rgba(234,104,64,.35);">
        <h3 style="margin-top:0;color:#ea6830;">AI provider not configured</h3>
        <p class="form-hint" style="margin-bottom:0;">AI generation is installed but disabled until <code>AI_API_KEY</code> is set in the server <code>.env</code> file.</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body" style="padding:18px 22px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <strong>Provider:</strong>
                <span style="color:var(--txt-muted);"><?php echo htmlspecialchars($providerName); ?></span>
            </div>
            <div>
                <label class="form-label" style="margin-bottom:4px;">Content Type</label>
                <select id="cmu-ai-content-type" style="padding:8px 10px;border-radius:var(--radius);border:1px solid var(--border);background:var(--surface2);color:var(--txt);min-width:220px;">
                    <option value="">— Select a content type —</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?php echo $t; ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $t))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <p class="form-hint" style="margin-top:10px;margin-bottom:0;">Select a content type to build its context form, then Generate. Approval records history only &mdash; it does not publish.</p>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:18px 22px;">
        <div id="cmu-ai-type-badge" style="font-size:.8rem;color:var(--txt-muted);margin-bottom:8px;"></div>
        <div id="cmu-ai-context-form" style="margin-bottom:12px;"></div>
        <div id="cmu-ai-banner" hidden style="display:none;"></div>
        <div id="cmu-ai-result-area" style="margin-top:12px;">
            <label class="form-label" style="margin-bottom:6px;">Generated Content</label>
            <textarea id="cmu-ai-editor" class="cmu-ai-editor" rows="8" readonly></textarea>
            <div id="cmu-ai-meta" class="form-hint" style="margin-top:8px;"></div>
        </div>
        <div class="cmu-ai-modal-actions" style="margin-top:14px;">
            <button id="cmu-ai-generate" type="button" class="btn btn-primary"><i class="fas fa-bolt"></i> Generate</button>
            <button id="cmu-ai-regen" type="button" class="btn btn-ghost"><i class="fas fa-sync"></i> Regenerate</button>
            <button id="cmu-ai-improve" type="button" class="btn btn-ghost"><i class="fas fa-wand-magic-sparkles"></i> Improve</button>
            <button id="cmu-ai-use" type="button" class="btn btn-ghost"><i class="fas fa-file-import"></i> Use This Content</button>
            <button id="cmu-ai-approve" type="button" class="btn btn-ghost"><i class="fas fa-check"></i> Approve</button>
            <button id="cmu-ai-copy" type="button" class="btn btn-ghost"><i class="fas fa-copy"></i> Copy</button>
            <button id="cmu-ai-clear" type="button" class="btn btn-ghost"><i class="fas fa-trash"></i> Clear</button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($configured): ?>
<div class="card" style="margin-top:16px;">
    <div class="card-body" style="padding:18px 22px;overflow-x:auto;">
        <h3 style="margin-top:0;font-size:1rem;">Recent generations</h3>
        <p class="form-hint" style="margin-bottom:12px;">Click Copy to load a stored generation back into the editor above.</p>
        <?php if (empty($history)): ?>
            <p class="form-hint" style="margin-bottom:0;">No generation history yet.</p>
        <?php else: ?>
        <table class="table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th align="left" style="padding:8px;font-size:.78rem;">ID</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Type</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Provider</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Model</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Status</th>
                    <th align="left" style="padding:8px;font-size:.78rem;">Created</th>
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
                        <td style="padding:8px;">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="ConnectMyUni.Ai.copyGenerated(<?php echo (int) ($h['id'] ?? 0); ?>);"><i class="fas fa-copy"></i> Copy</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>

