<?php
/**
 * Reusable AI Content Generator modal component.
 *
 * Include inside a CRUD `<form>` after defining:
 *   $aiContentType  - event | university | hero_slide | blog_post |
 *                     social_caption | website_content
 *   $aiFieldMap     - map of AI output key => form field name
 *                     (e.g. ['title' => 'title', 'description' => 'description'])
 *   $aiButtonLabel  - button text shown on the trigger
 *
 * Notes:
 *   - The trigger is type="button" so it never submits the enclosing form.
 *   - The modal is relocated to <body> so its generated fields are never
 *     submitted with the CRUD form.
 *   - "Use This Content" only fills form fields; it never saves.
 */
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;

if (!AuthMiddleware::check()) {
    http_response_code(401);
    return;
}

$aiContentType  = $aiContentType ?? 'website_content';
$aiFieldMap     = is_array($aiFieldMap ?? null) ? $aiFieldMap : [];
$aiButtonLabel  = $aiButtonLabel ?? 'Generate with AI';
$csrf           = Security::generateCsrfToken();
$baseUrl        = $admin_url ?? (defined('CONNECTMYUNI_BASE_URL') ? CONNECTMYUNI_BASE_URL . 'frontend/admin/' : '/');
$generateUrl    = $baseUrl . 'ai/generate.php';
$improveUrl     = $baseUrl . 'ai/improve.php';
$aiFieldMapJson = json_encode($aiFieldMap);
$aiTypeJson     = json_encode($aiContentType);
?>
<script>
window.CMU_AI = {
    csrf: <?php echo json_encode($csrf); ?>,
    generateUrl: <?php echo json_encode($generateUrl); ?>,
    improveUrl: <?php echo json_encode($improveUrl); ?>
};
window.__cmuAiFieldMap = <?php echo $aiFieldMapJson; ?>;
</script>

<button type="button" class="cmu-ai-trigger" onclick="ConnectMyUni.Ai.openFromTrigger(this)">
    <i class="fas fa-wand-magic-sparkles"></i> <?php echo htmlspecialchars($aiButtonLabel); ?>
</button>

<div id="cmu-ai-modal" class="cmu-ai-modal" hidden style="display:none;position:fixed;inset:0;z-index:10050;align-items:center;justify-content:center;padding:16px;">
    <div class="cmu-ai-modal-backdrop" data-ai-close style="position:absolute;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);"></div>
    <div class="cmu-ai-modal-card" style="position:relative;z-index:2;background:var(--surface,#181c25);border:1px solid var(--border,rgba(255,255,255,0.12));border-radius:12px;max-width:760px;width:100%;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.75);color:var(--txt,#f1f5f9);overflow:hidden;">
        <header style="padding:14px 20px;border-bottom:1px solid var(--border,rgba(255,255,255,0.1));display:flex;justify-content:space-between;align-items:center;background:var(--surface2,#1e2333);flex-shrink:0;">
            <h2 style="font-size:1.05rem;font-weight:700;margin:0;color:var(--txt,#fff);display:flex;align-items:center;gap:8px;">
                <i class="fas fa-wand-magic-sparkles" style="color:var(--accent,#06b6d4);"></i> AI Content Generator
            </h2>
            <button type="button" class="cmu-ai-modal-close" data-ai-close aria-label="Close" style="background:none;border:none;color:var(--txt-muted,#94a3b8);cursor:pointer;font-size:1.6rem;line-height:1;padding:2px 8px;border-radius:6px;">&times;</button>
        </header>
        <div class="cmu-ai-modal-body" style="padding:18px;overflow-y:auto;-webkit-overflow-scrolling:touch;flex:1;">
            <div id="cmu-ai-type-badge" class="form-hint" style="margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--accent,#06b6d4);font-size:0.75rem;"></div>
            <div id="cmu-ai-context-form" style="margin-bottom:12px;"></div>
            <div id="cmu-ai-banner" hidden style="display:none;margin-bottom:12px;"></div>
            <div id="cmu-ai-result-area">
                <label class="form-label" style="margin-bottom:6px;font-weight:600;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-file-lines text-primary"></i> Generated Content
                </label>
                <textarea id="cmu-ai-editor" class="cmu-ai-editor" rows="7" readonly style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border,rgba(255,255,255,0.15));border-radius:8px;padding:12px;color:#fff;font-family:monospace;font-size:0.88rem;resize:vertical;"></textarea>
                <div id="cmu-ai-meta" class="form-hint" style="margin-top:8px;"></div>
            </div>
            <div class="cmu-ai-modal-actions" style="margin-top:16px;">
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
</div>

<?php include_once __DIR__ . '/ai-generator-core.php'; ?>

<script>
window.__cmuAiContentType = <?php echo $aiTypeJson; ?>;
(function () {
    var m = document.getElementById('cmu-ai-modal');
    if (m && m.parentNode && m.parentNode !== document.body) {
        document.body.appendChild(m);
    }
    if (window.ConnectMyUni && window.ConnectMyUni.Ai) {
        window.ConnectMyUni.Ai.fieldMap = window.__cmuAiFieldMap || {};
        window.ConnectMyUni.Ai.openFromTrigger = function (triggerEl) {
            var fm = window.__cmuAiFieldMap || {};
            var form = triggerEl ? triggerEl.closest('form') : document.querySelector('form');
            window.ConnectMyUni.Ai.open({
                contentType: window.__cmuAiContentType || 'website_content',
                targetForm: form || 'form',
                fieldMap: fm
            });
        };
    }
})();
</script>
