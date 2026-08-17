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

<button type="button" class="cmu-ai-trigger" onclick="ConnectMyUni.Ai.openFromTrigger()">
    <i class="fas fa-wand-magic-sparkles"></i> <?php echo htmlspecialchars($aiButtonLabel); ?>
</button>

<div id="cmu-ai-modal" hidden>
    <div class="cmu-ai-modal-backdrop" data-ai-close></div>
    <div class="cmu-ai-modal-card">
        <header>
            <h2><i class="fas fa-wand-magic-sparkles" style="color:var(--accent);margin-right:6px;"></i> AI Content Generator</h2>
            <button type="button" class="cmu-ai-modal-close" data-ai-close aria-label="Close">&times;</button>
        </header>
        <div class="cmu-ai-modal-body">
            <div id="cmu-ai-type-badge" class="form-hint" style="margin-bottom:6px;"></div>
            <div id="cmu-ai-context-form" style="margin-bottom:12px;"></div>
            <div id="cmu-ai-banner" hidden style="display:none;"></div>
            <div id="cmu-ai-result-area">
                <label class="form-label" style="margin-bottom:6px;">Generated Content</label>
                <textarea id="cmu-ai-editor" class="cmu-ai-editor" rows="8" readonly></textarea>
                <div id="cmu-ai-meta" class="form-hint" style="margin-top:8px;"></div>
            </div>
            <div class="cmu-ai-modal-actions">
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
        window.ConnectMyUni.Ai.openFromTrigger = function () {
            var fm = window.__cmuAiFieldMap || {};
            // Resolve the actual parent CRUD form (this component is always
            // included inside the CRUD <form>). closest('form') is more robust
            // than 'form' (document.querySelector('form') -> first form on page).
            var trigger = document.querySelector('.cmu-ai-trigger');
            var form = trigger ? trigger.closest('form') : document.querySelector('form');
            window.ConnectMyUni.Ai.open({
                contentType: window.__cmuAiContentType || 'website_content',
                targetForm: form || 'form',
                fieldMap: fm
            });
        };
    }
})();
</script>
