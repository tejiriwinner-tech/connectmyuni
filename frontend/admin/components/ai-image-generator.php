<?php
/**
 * Reusable Professional Education Photography & AI Flyer modal component.
 */
use ConnectMyUni\Services\Ai\AiImageService;

$aiImageCategory    = $aiImageCategory ?? 'events';
$aiImageButtonLabel = $aiImageButtonLabel ?? 'Choose Professional Flyer / Image';
$csrfTokenImage     = \ConnectMyUni\Config\Security::generateCsrfToken();
$adminBaseUrl       = $admin_url ?? (defined('CONNECTMYUNI_BASE_URL') ? CONNECTMYUNI_BASE_URL . 'frontend/admin/' : '/frontend/admin/');
$stockPhotos        = AiImageService::CURATED_STOCK_PHOTOS;
?>

<div class="cmu-ai-image-box" style="margin: 8px 0 16px;">
    <button type="button" class="btn btn-secondary" onclick="ConnectMyUni.AiImage.open('<?php echo htmlspecialchars($aiImageCategory); ?>')" style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg, #4f46e5, #06b6d4);color:#fff;border:none;font-weight:600;padding:8px 16px;border-radius:8px;cursor:pointer;">
        <i class="fas fa-images"></i> <?php echo htmlspecialchars($aiImageButtonLabel); ?>
    </button>
    <input type="hidden" name="ai_image_path" id="ai_image_path" value="">
    <div id="ai_image_preview_box" style="display:none;margin-top:12px;padding:10px;background:var(--surface2, #1e2333);border-radius:8px;border:1px solid var(--border, rgba(255,255,255,0.1));max-width:340px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-size:0.8rem;font-weight:600;color:var(--accent, #06b6d4);"><i class="fas fa-check-circle"></i> Image Attached</span>
            <button type="button" onclick="ConnectMyUni.AiImage.remove()" style="background:none;border:none;color:#ef4444;font-size:0.8rem;cursor:pointer;"><i class="fas fa-times"></i> Remove</button>
        </div>
        <img id="ai_image_preview_img" src="" alt="Selected Flyer" style="width:100%;height:160px;object-fit:cover;border-radius:6px;">
    </div>
</div>

<!-- AI & Stock Photo Modal -->
<div id="cmu-ai-image-modal" hidden style="display:none;position:fixed;inset:0;z-index:10050;align-items:center;justify-content:center;padding:20px;">
    <div class="cmu-ai-modal-backdrop" onclick="ConnectMyUni.AiImage.close()" style="position:absolute;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(5px);"></div>
    <div class="cmu-ai-modal-card" style="position:relative;z-index:2;background:var(--surface, #181c25);border:1px solid var(--border, rgba(255,255,255,0.1));border-radius:12px;max-width:680px;width:100%;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.7);color:#fff;overflow:hidden;">
        <header style="padding:14px 20px;border-bottom:1px solid var(--border, rgba(255,255,255,0.1));display:flex;justify-content:space-between;align-items:center;background:var(--surface2, #1e2333);">
            <div style="display:flex;align-items:center;gap:12px;">
                <h3 style="font-size:1.05rem;margin:0;font-weight:700;display:flex;align-items:center;gap:8px;color:#fff;">
                    <i class="fas fa-camera-retro" style="color:#06b6d4;"></i> Professional Education Photography
                </h3>
            </div>
            <button type="button" onclick="ConnectMyUni.AiImage.close()" style="background:none;border:none;color:#aaa;font-size:1.5rem;line-height:1;cursor:pointer;">&times;</button>
        </header>
        
        <!-- Navigation Tabs -->
        <div style="display:flex;border-bottom:1px solid rgba(255,255,255,0.1);background:rgba(0,0,0,0.2);padding:0 20px;">
            <button type="button" id="tab-btn-stock" onclick="ConnectMyUni.AiImage.switchTab('stock')" style="background:none;border:none;border-bottom:2px solid #06b6d4;color:#06b6d4;font-weight:600;padding:10px 14px;font-size:0.85rem;cursor:pointer;">
                <i class="fas fa-star me-1"></i> Real High-Res Photography (Recommended)
            </button>
            <button type="button" id="tab-btn-custom" onclick="ConnectMyUni.AiImage.switchTab('custom')" style="background:none;border:none;border-bottom:2px solid transparent;color:#aaa;font-weight:600;padding:10px 14px;font-size:0.85rem;cursor:pointer;">
                <i class="fas fa-wand-magic-sparkles me-1"></i> Custom AI Generator
            </button>
        </div>

        <div style="padding:20px;overflow-y:auto;flex:1;">
            <!-- Tab 1: Curated Real Education Photos -->
            <div id="tab-pane-stock">
                <p style="font-size:0.8rem;color:#94a3b8;margin-top:0;margin-bottom:14px;">
                    Select any authentic, high-definition photo. It will be downloaded, saved to your local storage, and attached instantly:
                </p>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(140px, 1fr));gap:10px;">
                    <?php foreach ($stockPhotos as $key => $photo): ?>
                    <div class="cmu-stock-card" onclick="ConnectMyUni.AiImage.selectStock('<?php echo $key; ?>')" style="position:relative;cursor:pointer;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);background:#111;transition:transform 0.15s ease, border-color 0.15s ease;">
                        <img src="<?php echo htmlspecialchars($photo['thumb']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>" style="width:100%;height:95px;object-fit:cover;display:block;">
                        <div style="padding:6px 8px;background:rgba(15,18,25,0.95);font-size:0.72rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#eee;">
                            <?php echo htmlspecialchars($photo['tag']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tab 2: Custom AI Prompt -->
            <div id="tab-pane-custom" style="display:none;">
                <div style="margin-bottom:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label style="font-size:0.85rem;font-weight:600;margin:0;color:#ddd;">Describe Custom Scene</label>
                        <span style="background:linear-gradient(135deg, #a855f7, #6366f1);color:#fff;font-size:0.75rem;font-weight:600;padding:2px 8px;border-radius:4px;">
                            ⚡ fal.ai FLUX.1 Photoreal
                        </span>
                    </div>
                    <input type="text" id="cmu-ai-img-prompt" class="form-control" placeholder="e.g. African & international university students studying together in Oxford courtyard" style="width:100%;padding:10px 12px;background:#0f1219;border:1px solid rgba(255,255,255,0.15);color:#fff;border-radius:8px;">
                    <p style="font-size:0.75rem;color:#94a3b8;margin:6px 0 0;">Generates authentic, high-definition photography with natural human skin, lighting, and real campus scenery.</p>
                </div>
                <div style="text-align:right;">
                    <button type="button" id="cmu-ai-img-gen-btn" onclick="ConnectMyUni.AiImage.generateCustom()" class="btn btn-primary" style="background:linear-gradient(135deg, #06b6d4, #3b82f6);border:none;font-weight:600;padding:8px 18px;border-radius:8px;cursor:pointer;color:#fff;">
                        <i class="fas fa-wand-magic-sparkles"></i> Generate with fal.ai
                    </button>
                </div>
            </div>

            <!-- Status & Preview -->
            <div id="cmu-ai-img-status" style="display:none;margin-top:14px;padding:10px 14px;border-radius:8px;font-size:0.85rem;"></div>

            <div id="cmu-ai-img-result" style="display:none;margin-top:16px;text-align:center;">
                <img id="cmu-ai-img-output" src="" alt="Selected Photo Preview" style="width:100%;max-height:240px;object-fit:cover;border-radius:8px;border:1px solid rgba(255,255,255,0.15);">
                <div style="margin-top:12px;">
                    <button type="button" id="cmu-ai-img-use-btn" onclick="ConnectMyUni.AiImage.useContent()" class="btn btn-success" style="background:#10b981;border:none;font-weight:600;padding:9px 20px;border-radius:8px;cursor:pointer;color:#fff;">
                        <i class="fas fa-check"></i> Use This Photo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.ConnectMyUni = window.ConnectMyUni || {};
window.ConnectMyUni.AiImage = (function () {
    var endpoint = <?php echo json_encode($adminBaseUrl . 'ai/generate-image.php'); ?>;
    var csrf = <?php echo json_encode($csrfTokenImage); ?>;
    var currentCategory = 'events';
    var lastResult = null;

    return {
        open: function (category) {
            currentCategory = category || 'events';
            var modal = document.getElementById('cmu-ai-image-modal');
            if (modal) {
                modal.removeAttribute('hidden');
                modal.style.display = 'flex';
            }
        },
        close: function () {
            var modal = document.getElementById('cmu-ai-image-modal');
            if (modal) {
                modal.setAttribute('hidden', '');
                modal.style.display = 'none';
            }
        },
        switchTab: function (tab) {
            var pStock = document.getElementById('tab-pane-stock');
            var pCustom = document.getElementById('tab-pane-custom');
            var bStock = document.getElementById('tab-btn-stock');
            var bCustom = document.getElementById('tab-btn-custom');

            if (tab === 'stock') {
                if (pStock) pStock.style.display = 'block';
                if (pCustom) pCustom.style.display = 'none';
                if (bStock) { bStock.style.color = '#06b6d4'; bStock.style.borderBottomColor = '#06b6d4'; }
                if (bCustom) { bCustom.style.color = '#aaa'; bCustom.style.borderBottomColor = 'transparent'; }
            } else {
                if (pStock) pStock.style.display = 'none';
                if (pCustom) pCustom.style.display = 'block';
                if (bCustom) { bCustom.style.color = '#06b6d4'; bCustom.style.borderBottomColor = '#06b6d4'; }
                if (bStock) { bStock.style.color = '#aaa'; bStock.style.borderBottomColor = 'transparent'; }
            }
        },
        selectStock: async function (photoKey) {
            var status = document.getElementById('cmu-ai-img-status');
            var resultBox = document.getElementById('cmu-ai-img-result');
            var imgOutput = document.getElementById('cmu-ai-img-output');

            status.style.display = 'block';
            status.style.background = 'rgba(6,182,212,0.15)';
            status.style.color = '#06b6d4';
            status.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Downloading high-resolution commercial photo...';

            try {
                var res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                    body: JSON.stringify({ photo_key: photoKey, category: currentCategory })
                });
                var json = await res.json();

                if (json.success && json.image_url) {
                    lastResult = json;
                    imgOutput.src = json.image_url;
                    resultBox.style.display = 'block';
                    status.style.background = 'rgba(16,185,129,0.15)';
                    status.style.color = '#10b981';
                    status.innerHTML = '<i class="fas fa-check-circle me-1"></i> Real photo ready! Click <strong>Use This Photo</strong> below.';
                } else {
                    status.style.background = 'rgba(239,68,68,0.15)';
                    status.style.color = '#ef4444';
                    status.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> ' + (json.error || 'Failed to download photo.');
                }
            } catch (err) {
                status.style.background = 'rgba(239,68,68,0.15)';
                status.style.color = '#ef4444';
                status.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Error: ' + err.message;
            }
        },
        generateCustom: async function () {
            var prompt = (document.getElementById('cmu-ai-img-prompt') || {}).value || '';
            if (!prompt.trim()) {
                alert('Please describe the photo scene.');
                return;
            }

            var btn = document.getElementById('cmu-ai-img-gen-btn');
            var status = document.getElementById('cmu-ai-img-status');
            var resultBox = document.getElementById('cmu-ai-img-result');
            var imgOutput = document.getElementById('cmu-ai-img-output');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating Photo (~3s)...';
            status.style.display = 'block';
            status.style.background = 'rgba(6,182,212,0.15)';
            status.style.color = '#06b6d4';
            status.innerHTML = '<i class="fas fa-camera fa-spin me-1"></i> Generating photorealistic scene...';

            try {
                var res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                    body: JSON.stringify({ prompt: prompt, category: currentCategory })
                });
                var json = await res.json();

                if (json.success && json.image_url) {
                    lastResult = json;
                    imgOutput.src = json.image_url;
                    resultBox.style.display = 'block';
                    status.style.background = 'rgba(16,185,129,0.15)';
                    status.style.color = '#10b981';
                    status.innerHTML = '<i class="fas fa-check-circle me-1"></i> Photo generated successfully!';
                } else {
                    status.style.background = 'rgba(239,68,68,0.15)';
                    status.style.color = '#ef4444';
                    status.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> ' + (json.error || 'Generation failed.');
                }
            } catch (err) {
                status.style.background = 'rgba(239,68,68,0.15)';
                status.style.color = '#ef4444';
                status.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Error: ' + err.message;
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-bolt"></i> Generate Custom Photo';
            }
        },
        useContent: function () {
            if (!lastResult || !lastResult.image_path) return;
            var hidden = document.getElementById('ai_image_path');
            if (hidden) hidden.value = lastResult.image_path;

            var pBox = document.getElementById('ai_image_preview_box');
            var pImg = document.getElementById('ai_image_preview_img');
            if (pBox && pImg) {
                pImg.src = lastResult.image_url;
                pBox.style.display = 'block';
            }
            ConnectMyUni.AiImage.close();
        },
        remove: function () {
            var hidden = document.getElementById('ai_image_path');
            if (hidden) hidden.value = '';
            var pBox = document.getElementById('ai_image_preview_box');
            if (pBox) pBox.style.display = 'none';
        }
    };
})();
</script>
