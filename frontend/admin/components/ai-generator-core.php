<?php
declare(strict_types=1);
/**
 * Shared AI generator core (CSS + JS).
 *
 * Emitted ONCE per page. Safe to include many times — a static guard prevents
 * duplicate output. Both the standalone AI page (frontend/admin/ai/index.php) and
 * the CRUD modal component (ai-content-generator.php) rely on this.
 *
 * Reads runtime config from window.CMU_AI = { csrf, generateUrl, improveUrl }.
 */
if (defined('CMU_AI_CORE_EMITTED') && CMU_AI_CORE_EMITTED) {
    return;
}
define('CMU_AI_CORE_EMITTED', true);
?>
<style>
.cmu-ai-modal{position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px;}
.cmu-ai-modal[hidden]{display:none;}
.cmu-ai-modal-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(3px);}
.cmu-ai-modal-card{position:relative;z-index:2;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);max-width:780px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.5);color:var(--txt);}
.cmu-ai-modal-card header{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--surface);z-index:1;}
.cmu-ai-modal-card header h2{font-size:1rem;font-weight:600;margin:0;color:var(--txt);}
.cmu-ai-modal-close{background:none;border:none;color:var(--txt-muted);cursor:pointer;font-size:1.5rem;line-height:1;padding:2px 8px;border-radius:6px;}
.cmu-ai-modal-close:hover{color:var(--txt);background:var(--surface2);}
.cmu-ai-modal-body{padding:20px;}
.cmu-ai-modal-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;align-items:center;}
.cmu-ai-modal-actions .btn{flex:0 0 auto;}
.cmu-ai-trigger{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:var(--radius);background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;border:none;cursor:pointer;font-weight:600;font-size:.85rem;font-family:inherit;transition:opacity .15s ease,transform .15s ease;margin:4px 0 12px;}
.cmu-ai-trigger:hover{opacity:.92;transform:translateY(-1px);}
.cmu-ai-trigger i{font-size:.9rem;}
.cmu-ai-editor{font-family:'DM Mono',monospace;font-size:.85rem;min-height:140px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius);}
@media (max-width: 576px){
    .cmu-ai-modal, #cmu-ai-image-modal{padding:8px !important;}
    .cmu-ai-modal-card, .cmu-ai-modal-card{max-height:96vh !important;border-radius:10px !important;}
    .cmu-ai-modal-body{padding:12px !important;}
    .cmu-ai-modal-actions{flex-direction:column;align-items:stretch;}
    .cmu-ai-modal-actions .btn{width:100% !important;justify-content:center;}
}

.cmu-ai-hint-row {
    margin-top: 6px;
    margin-bottom: 8px;
    font-size: 0.78rem;
    color: var(--txt-muted);
    line-height: 1.4;
}
.cmu-ai-chips-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
    align-items: center;
}
.cmu-ai-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--surface2, #1e2333);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: var(--txt, #cbd5e1);
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.cmu-ai-chip:hover {
    background: rgba(13, 148, 136, 0.2);
    border-color: #0d9488;
    color: #14b8a6;
    transform: translateY(-1px);
}
.cmu-ai-chip:active {
    transform: scale(0.97);
}
.cmu-ai-guide-toggle {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: none;
    border: none;
    color: #06b6d4;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    padding: 3px 6px;
    border-radius: 4px;
    text-decoration: underline;
    transition: color 0.15s ease;
}
.cmu-ai-guide-toggle:hover {
    color: #38bdf8;
}
.cmu-ai-guide-box {
    margin-top: 8px;
    padding: 10px 14px;
    background: rgba(15, 23, 42, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    font-size: 0.78rem;
    line-height: 1.55;
    color: #94a3b8;
}
.cmu-ai-guide-box strong {
    color: #e2e8f0;
}
.cmu-ai-guide-box ul {
    margin: 6px 0 0 0;
    padding-left: 18px;
}
.cmu-ai-guide-box li {
    margin-bottom: 5px;
}

</style>
<script>
(function(){
'use strict';
if (window.ConnectMyUni && window.ConnectMyUni.Ai && window.ConnectMyUni.Ai.__coreLoaded) return;

function getCfg(){ return window.CMU_AI || {}; }

var fieldDefs = {
    event: [
        {name:'topic', label:'Topic / Title', type:'text', required:true},
        {name:'category', label:'Category', type:'text'},
        {name:'audience', label:'Target Audience', type:'text'},
        {name:'location', label:'Location', type:'text'},
        {name:'event_date', label:'Event Date', type:'text'},
        {name:'key_points', label:'Key Points (one per line)', type:'textarea'},
        {name:'tone', label:'Preferred Tone', type:'text'},
        {name:'length', label:'Content Length', type:'text'}
    ],
    university: [
        {name:'university_name', label:'University / Provider Name', type:'text', required:true},
        {name:'country', label:'Country / Region', type:'text', required:true},
        {name:'city', label:'City', type:'text'},
        {name:'programs', label:'Main Programs / Strengths', type:'textarea'},
        {name:'selling_points', label:'Selling Points (one per line)', type:'textarea'},
        {name:'target_students', label:'Target Students', type:'text'},
        {name:'tone', label:'Preferred Tone', type:'text'},
        {name:'length', label:'Content Length', type:'text'}
    ],
    hero_slide: [
        {name:'topic', label:'Topic / Offer', type:'text', required:true},
        {name:'destination', label:'Country / Destination', type:'text'},
        {name:'selling_point', label:'Key Selling Point', type:'text'},
        {name:'cta_goal', label:'CTA Goal (e.g. Apply / Explore)', type:'text'},
        {name:'tone', label:'Preferred Tone', type:'text'},
        {name:'length', label:'Content Length', type:'text'}
    ],
    blog_post: [
        {name:'topic', label:'Topic / Headline', type:'text', required:true},
        {name:'audience', label:'Target Audience', type:'text'},
        {name:'seo_keywords', label:'SEO Keywords (comma separated)', type:'text'},
        {name:'key_points', label:'Key Points (one per line)', type:'textarea'},
        {name:'tone', label:'Preferred Tone', type:'text'},
        {name:'length', label:'Content Length', type:'text'}
    ],
    social_caption: [
        {name:'topic', label:'Topic', type:'text', required:true},
        {name:'platform', label:'Platform (Instagram / LinkedIn / etc.)', type:'text'},
        {name:'audience', label:'Audience', type:'text'},
        {name:'cta', label:'Call-To-Action', type:'text'},
        {name:'hashtags', label:'Fixed Hashtags', type:'text'}
    ],
    website_content: [
        {name:'topic', label:'Page / Section Topic', type:'text', required:true},
        {name:'purpose', label:'Purpose (e.g. admissions, about)', type:'text'},
        {name:'audience', label:'Audience', type:'text'},
        {name:'key_points', label:'Key Points (one per line)', type:'textarea'},
        {name:'tone', label:'Preferred Tone', type:'text'},
        {name:'length', label:'Content Length', type:'text'}
    ]
};

var state = {contentType:null, fieldMap:null, targetForm:null, lastRequestId:null, busy:false};

function escHtml(s){
    return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
}

async function fetchJson(url, body){
    var c = getCfg();
    body = body || {};
    if (typeof body === 'object' && !body.csrf_token && c.csrf) {
        body.csrf_token = c.csrf;
    }
    var res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': c.csrf || '',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(body)
    });
    var text = await res.text();
    var json;
    try { json = JSON.parse(text); }
    catch (e) {
        var cleanMsg = text.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
        throw new Error('Server returned an invalid response (HTTP ' + res.status + '): ' + (cleanMsg.substring(0, 160) || 'Unknown error'));
    }
    return json;
}

function buildForm(type, container, seed){
    seed = seed || {};
    var defs = fieldDefs[type] || [];
    if (!container) return;
    if (!defs.length) { container.innerHTML = ''; return; }
    container.innerHTML = defs.map(function(f){
        var id = 'cmu-ai-ctx-' + f.name;
        var req = f.required ? ' <span class="req">*</span>' : '';
        var v = (seed[f.name] != null) ? escHtml(String(seed[f.name])) : '';
        var inp;
        if (f.type === 'textarea') {
            inp = '<textarea class="form-control" id="' + id + '" name="' + f.name + '" rows="4" placeholder="Enter ' + escHtml(f.label) + '...">' + v + '</textarea>';
        } else {
            var ph = 'Enter ' + escHtml(f.label) + '...';
            if (f.name === 'tone') ph = 'e.g. Professional & academic, or click a preset below';
            if (f.name === 'length') ph = 'e.g. Medium (~150 words), or click a preset below';
            inp = '<input class="form-control" type="text" id="' + id + '" name="' + f.name + '"' + (f.required ? ' required' : '') + ' placeholder="' + ph + '" value="' + v + '">';
        }

        var hintHtml = '';
        if (f.name === 'topic' || f.name === 'university_name') {
            if (type === 'hero_slide') {
                hintHtml = '<div class="cmu-ai-hint-row">' +
                    '<div><i class="fas fa-bullhorn text-warning"></i> Main headline or promotional offer. Tap a preset or type your offer:</div>' +
                    '<div class="cmu-ai-chips-container">' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'50% Tuition Scholarship & Early Bird Discount for Fall 2026\')"><i class="fas fa-tags"></i> 50% Tuition Scholarship</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'Study in the UK, Canada & USA &ndash; Fast-Track Direct Admission\')"><i class="fas fa-plane-departure"></i> UK, Canada &amp; USA Admission</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'Zero Application Fee Week for Top Partner Universities\')"><i class="fas fa-gift"></i> Zero Fee Week</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'Graduate with 2-3 Year Post-Study Work Visa Opportunities\')"><i class="fas fa-briefcase"></i> Work Visa Opportunities</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'Fast-Track Visa Counseling & Full Application Support\')"><i class="fas fa-passport"></i> Fast-Track Support</span>' +
                        '<button type="button" class="cmu-ai-guide-toggle" onclick="ConnectMyUni.Ai.toggleGuide(\'topic-offer\')"><i class="fas fa-question-circle"></i> Offer Guide</button>' +
                    '</div>' +
                    '<div id="cmu-ai-guide-topic-offer" class="cmu-ai-guide-box" style="display:none;">' +
                        '<div><strong><i class="fas fa-lightbulb me-1"></i> Writing High-Converting Banner Offers:</strong></div>' +
                        '<ul>' +
                            '<li><strong>Scholarships &amp; Discounts:</strong> Specify incentives (e.g. "Partial Tuition Grants up to $10,000" or "Early-Bird Bursaries").</li>' +
                            '<li><strong>Target Intakes:</strong> Call out intake dates (e.g. "September 2026 / January 2027 Intakes Open").</li>' +
                            '<li><strong>Key Student Benefit:</strong> Highlight key differentiators (e.g. "No IELTS Required / WAEC Accepted" or "Post-Study Work Permits").</li>' +
                        '</ul>' +
                        '<div style="margin-top:6px;font-size:0.73rem;color:#64748b;"><em>Pro-Tip: Keep it focused on 1 core offer so the headline stays crisp and readable on mobile devices.</em></div>' +
                    '</div>' +
                '</div>';
            } else if (type === 'event') {
                hintHtml = '<div class="cmu-ai-hint-row">' +
                    '<div><i class="fas fa-calendar-star text-warning"></i> Event title or webinar focus. Tap a preset or type your title:</div>' +
                    '<div class="cmu-ai-chips-container">' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'Global Study Abroad & Education Fair 2026\')"><i class="fas fa-globe"></i> Global Education Fair</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'UK Student Visa & Application Processing Seminar\')"><i class="fas fa-passport"></i> UK Visa Seminar</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'International Scholarships & Funding Masterclass\')"><i class="fas fa-award"></i> Scholarships Masterclass</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'Undergraduate & Postgraduate Direct Application Day\')"><i class="fas fa-user-graduate"></i> Direct Application Day</span>' +
                    '</div>' +
                '</div>';
            } else if (type === 'university') {
                hintHtml = '<div class="cmu-ai-hint-row">' +
                    '<div><i class="fas fa-university text-warning"></i> University or institution name. Tap an example or type yours:</div>' +
                    '<div class="cmu-ai-chips-container">' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'university_name\', \'University of Greenwich\'); ConnectMyUni.Ai.setField(\'country\', \'United Kingdom\'); ConnectMyUni.Ai.setField(\'city\', \'London\');"><i class="fas fa-landmark"></i> Univ. of Greenwich (UK)</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'university_name\', \'University of Windsor\'); ConnectMyUni.Ai.setField(\'country\', \'Canada\'); ConnectMyUni.Ai.setField(\'city\', \'Windsor\');"><i class="fas fa-landmark"></i> Univ. of Windsor (Canada)</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'university_name\', \'Western Sydney University\'); ConnectMyUni.Ai.setField(\'country\', \'Australia\'); ConnectMyUni.Ai.setField(\'city\', \'Sydney\');"><i class="fas fa-landmark"></i> Western Sydney Univ. (Aus)</span>' +
                    '</div>' +
                '</div>';
            } else if (type === 'blog_post' || type === 'social_caption' || type === 'website_content') {
                hintHtml = '<div class="cmu-ai-hint-row">' +
                    '<div><i class="fas fa-pen-nib text-warning"></i> Primary topic or campaign focus. Tap an example or type yours:</div>' +
                    '<div class="cmu-ai-chips-container">' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'Step-by-Step Guide to Securing a UK Student Visa in 2026\')"><i class="fas fa-book"></i> UK Visa Guide</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'Top 10 Most Affordable Universities in Canada for International Students\')"><i class="fas fa-school"></i> Affordable Canada Unis</span>' +
                        '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'topic\', \'How to Write a Winning Statement of Purpose (SOP)\')"><i class="fas fa-file-signature"></i> Winning SOP</span>' +
                    '</div>' +
                '</div>';
            }
        }
        if (f.name === 'tone') {
            hintHtml = '<div class="cmu-ai-hint-row">' +
                '<div><i class="fas fa-info-circle text-info"></i> Sets the voice and style of the text. Tap a preset or type your own:</div>' +
                '<div class="cmu-ai-chips-container">' +
                    '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'tone\', \'Professional, academic, and reassuring\')"><i class="fas fa-graduation-cap"></i> Professional &amp; Academic</span>' +
                    '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'tone\', \'Inspiring, aspirational, and empowering\')"><i class="fas fa-lightbulb"></i> Inspiring &amp; Motivating</span>' +
                    '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'tone\', \'Urgent, exciting, and call-to-action focused\')"><i class="fas fa-fire"></i> Urgent &amp; Action-Oriented</span>' +
                    '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'tone\', \'Warm, conversational, and youth-friendly\')"><i class="fas fa-comments"></i> Friendly &amp; Relatable</span>' +
                    '<button type="button" class="cmu-ai-guide-toggle" onclick="ConnectMyUni.Ai.toggleGuide(\'tone\')"><i class="fas fa-question-circle"></i> Tone Guide</button>' +
                '</div>' +
                '<div id="cmu-ai-guide-tone" class="cmu-ai-guide-box" style="display:none;">' +
                    '<div><strong><i class="fas fa-book-reader me-1"></i> Tone Guidelines &amp; Best Practices:</strong></div>' +
                    '<ul>' +
                        '<li><strong>Professional &amp; Informative:</strong> Best for university admission requirements, visa advisory guidelines, and formal education announcements.</li>' +
                        '<li><strong>Inspiring &amp; Motivating:</strong> Best for study abroad fairs, scholarship seminars, and student success stories.</li>' +
                        '<li><strong>Urgent &amp; Action-Oriented:</strong> Best for application deadlines, last remaining seats, and early-bird scholarship discounts.</li>' +
                        '<li><strong>Friendly &amp; Relatable:</strong> Best for social media captions, student spotlights, and FAQ responses.</li>' +
                    '</ul>' +
                    '<div style="margin-top:6px;font-size:0.73rem;color:#64748b;"><em>Pro-Tip: You can combine adjectives (e.g. \'Warm, authoritative, and concise\') or leave blank for the default professional consultancy voice.</em></div>' +
                '</div>' +
            '</div>';
        } else if (f.name === 'length') {
            hintHtml = '<div class="cmu-ai-hint-row">' +
                '<div><i class="fas fa-info-circle text-info"></i> Controls how detailed or concise the text will be. Tap a preset or type word count:</div>' +
                '<div class="cmu-ai-chips-container">' +
                    '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'length\', \'Short (~50 words)\')"><i class="fas fa-bolt"></i> Short (~50 words)</span>' +
                    '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'length\', \'Medium (~150 words)\')"><i class="fas fa-file-alt"></i> Medium (~150 words &bull; Recommended)</span>' +
                    '<span class="cmu-ai-chip" onclick="ConnectMyUni.Ai.setField(\'length\', \'Long / Detailed (~350 words)\')"><i class="fas fa-book-open"></i> Long (~350 words)</span>' +
                    '<button type="button" class="cmu-ai-guide-toggle" onclick="ConnectMyUni.Ai.toggleGuide(\'length\')"><i class="fas fa-question-circle"></i> Length Guide</button>' +
                '</div>' +
                '<div id="cmu-ai-guide-length" class="cmu-ai-guide-box" style="display:none;">' +
                    '<div><strong><i class="fas fa-ruler-combined me-1"></i> Content Length Guidelines:</strong></div>' +
                    '<ul>' +
                        '<li><strong>Short (~50 words):</strong> Best for hero banner slides, quick notification blurbs, and social media captions.</li>' +
                        '<li><strong>Medium (~150 words):</strong> Recommended default for event descriptions, service summaries, and standard university highlights.</li>' +
                        '<li><strong>Long / Detailed (~350 words):</strong> Best for in-depth university profile pages, full event agendas, and comprehensive admission/visa guides.</li>' +
                    '</ul>' +
                    '<div style="margin-top:6px;font-size:0.73rem;color:#64748b;"><em>Pro-Tip: Leave blank to automatically generate balanced, publication-ready Medium copy.</em></div>' +
                '</div>' +
            '</div>';
        }

        return '<div class="form-group"><label class="form-label" for="' + id + '">' + escHtml(f.label) + req + '</label>' + inp + hintHtml + '</div>';
    }).join('');
}

function collectForm(type){
    var defs = fieldDefs[type] || [];
    var out = {content_type: type};
    defs.forEach(function(f){
        var el = document.getElementById('cmu-ai-ctx-' + f.name);
        if (el) out[f.name] = el.value || '';
    });
    return out;
}

function setEditor(data){
    var editor = document.getElementById('cmu-ai-editor');
    var area = document.getElementById('cmu-ai-result-area');
    if (!editor || !area) return;
    editor.value = (typeof data === 'string') ? data : JSON.stringify(data, null, 2);
    area.hidden = false;
}

function clearEditor(){
    var editor = document.getElementById('cmu-ai-editor');
    var area = document.getElementById('cmu-ai-result-area');
    var meta = document.getElementById('cmu-ai-meta');
    if (editor) editor.value = '';
    if (area) area.hidden = true;
    if (meta) meta.innerHTML = '';
    state.lastRequestId = null;
}

function showError(msg){
    var meta = document.getElementById('cmu-ai-meta');
    var area = document.getElementById('cmu-ai-result-area');
    var banner = document.getElementById('cmu-ai-banner');
    if (banner) {
        banner.hidden = false;
        banner.style.display = 'block';
        banner.className = 'cmu-ai-banner';
        banner.innerHTML = '<div style="background:rgba(239,68,68,0.15);border:1px solid #ef4444;color:#ef4444;padding:12px 16px;border-radius:8px;font-size:0.85rem;margin-bottom:14px;"><i class="fas fa-exclamation-triangle me-2"></i> ' + escHtml(msg) + '</div>';
    }
    if (area) {
        area.hidden = false;
        area.style.display = 'block';
    }
    if (meta) {
        meta.innerHTML = '<span style="color:var(--danger);"><i class="fas fa-exclamation-triangle me-1"></i> ' + escHtml(msg) + '</span>';
    }
    if (!banner && !meta) {
        alert(msg);
    }
} else { alert(msg); }
}

function flashButton(id, html){
    var el = document.getElementById(id);
    if (!el) return;
    if (!el.dataset.origHtml) el.dataset.origHtml = el.innerHTML;
    el.innerHTML = html;
    setTimeout(function(){ if (el.dataset.origHtml) el.innerHTML = el.dataset.origHtml; }, 1600);
}

function setBusy(busy){
    state.busy = busy;
    ['cmu-ai-generate','cmu-ai-regen','cmu-ai-improve','cmu-ai-approve','cmu-ai-use'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.disabled = busy;
    });
}

function setLoadingLabel(elId, label){
    var el = document.getElementById(elId);
    if (!el) return null;
    var orig = el.innerHTML;
    // Capture the true pre-loading HTML before the spinner replaces it, so that
    // flashButton()'s delayed restore reverts to the real button (not the spinner).
    if (!el.dataset.origHtml) el.dataset.origHtml = orig;
    el.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + escHtml(label);
    return orig;
}

async function generate(){
    var c = getCfg();
    if (state.busy) return;

    var banner = document.getElementById('cmu-ai-banner');
    if (banner) {
        banner.hidden = true;
        banner.style.display = 'none';
        banner.innerHTML = '';
    }

    if (!state.contentType) { showError('Please select a content type.'); return; }
    if (!c.generateUrl) { showError('AI endpoint not configured.'); return; }
    // required-field validation
    var defs = fieldDefs[state.contentType] || [];
    for (var i = 0; i < defs.length; i++) {
        if (defs[i].required) {
            var el = document.getElementById('cmu-ai-ctx-' + defs[i].name);
            if (el && !el.value.trim()) {
                el.focus();
                showError('Please fill in: ' + defs[i].label);
                return;
            }
        }
    }
    var data = collectForm(state.contentType);
    setBusy(true);
    var o1 = setLoadingLabel('cmu-ai-generate', 'Generating…');
    var o2 = setLoadingLabel('cmu-ai-regen', 'Regenerating…');
    var meta = document.getElementById('cmu-ai-meta');
    var area = document.getElementById('cmu-ai-result-area');
    if (area) {
        area.hidden = false;
        area.style.display = 'block';
    }
    if (meta) {
        meta.innerHTML = '<span style="color:var(--accent);"><i class="fas fa-spinner fa-spin me-1"></i> Generating high-accuracy AI copy (<span id="cmu-ai-timer">0</span>s elapsed)... Please wait.</span>';
    }
    var seconds = 0;
    var timerInterval = setInterval(function(){
        seconds++;
        var el = document.getElementById('cmu-ai-timer');
        if (el) el.textContent = seconds;
    }, 1000);
    try {
        var json = await fetchJson(c.generateUrl, data);
        if (!json.success) { showError(json.error || 'Generation failed.'); return; }
        // Provider-not-configured state surfaced by backend.
        setEditor(json.data);
        state.lastRequestId = json.requestId || null;
        if (meta) meta.innerHTML = '<span style="color:var(--success);"><i class="fas fa-check-circle me-1"></i> Generated in ' + seconds + 's. Review and edit below, then click <strong>Use This Content</strong> to fill the form.</span>';
    } catch (e) {
        showError((e && e.message) ? e.message : String(e));
    } finally {
        clearInterval(timerInterval);
        setBusy(false);
        if (o1) document.getElementById('cmu-ai-generate').innerHTML = o1;
        if (o2) document.getElementById('cmu-ai-regen').innerHTML = o2;
    }
}

async function improve(){
    var c = getCfg();
    if (state.busy) return;
    if (!state.contentType) { showError('Generate content first.'); return; }
    if (!c.improveUrl) { showError('AI endpoint not configured.'); return; }
    var editor = document.getElementById('cmu-ai-editor');
    var existing = null;
    if (editor && editor.value.trim()) {
        try { existing = JSON.parse(editor.value); }
        catch (_) { existing = editor.value; }
    }
    if (existing == null) { showError('Please generate content before improving.'); return; }
    var def = prompt('How should the AI improve this content?\n\nSuggestions:\n- Make it more professional\n- Make it shorter\n- Make it more persuasive\n- Improve grammar\n- Simplify the language\n- Make the CTA stronger');
    if (!def || !def.trim()) return;
    setBusy(true);
    var o = setLoadingLabel('cmu-ai-improve', 'Improving…');
    try {
        var json = await fetchJson(c.improveUrl, {
            existing_content: existing,
            instruction: def,
            context: {content_type: state.contentType}
        });
        if (!json.success) { showError(json.error || 'Improve failed.'); return; }
        setEditor(json.data);
        state.lastRequestId = json.requestId || state.lastRequestId;
        var meta = document.getElementById('cmu-ai-meta');
        if (meta) meta.innerHTML = '<span style="color:var(--accent);"><i class="fas fa-magic"></i> Improved. Review the result before using it.</span>';
    } catch (e) {
        showError((e && e.message) ? e.message : String(e));
    } finally {
        setBusy(false);
        if (o) document.getElementById('cmu-ai-improve').innerHTML = o;
    }
}

async function copyEditor(){
    var editor = document.getElementById('cmu-ai-editor');
    if (!editor || !editor.value) return;
    try {
        await navigator.clipboard.writeText(editor.value);
        flashButton('cmu-ai-copy', '<i class="fas fa-check"></i> Copied');
    } catch (_) {
        editor.select();
        try { document.execCommand('copy'); } catch (e) {}
    }
}

async function approve(){
    if (!state.lastRequestId) { showError('Generate content before approving.'); return; }
    var c = getCfg();
    if (!c.generateUrl) { showError('AI endpoint not configured.'); return; }
    var editor = document.getElementById('cmu-ai-editor');
    var approved = null;
    if (editor && editor.value.trim()) {
        try { approved = JSON.parse(editor.value); }
        catch (_) { approved = editor.value; }
    }
    setBusy(true);
    var o = setLoadingLabel('cmu-ai-approve', 'Saving approval…');
    try {
        var json = await fetchJson(c.generateUrl, {
            requestId: state.lastRequestId,
            approvedContent: approved
        });
        if (json && (json.success || json.requestId)) {
            flashButton('cmu-ai-approve', '<i class="fas fa-check"></i> Approved');
            var meta = document.getElementById('cmu-ai-meta');
            if (meta) meta.innerHTML = '<span style="color:var(--success);"><i class="fas fa-check-circle"></i> Approved into AI history. This does not write to the CRUD table. Manually copy the content into the form and click Save.</span>';
            if (window.ConnectMyUni.Ai.afterApprove) { try { window.ConnectMyUni.Ai.afterApprove(state.lastRequestId); } catch (_e) {} }
        } else {
            showError(json ? (json.error || 'Approve failed.') : 'Approve failed.');
        }
    } catch (e) {
        showError((e && e.message) ? e.message : String(e));
    } finally {
        setBusy(false);
        var approveBtn = document.getElementById('cmu-ai-approve');
        if (o && approveBtn) approveBtn.innerHTML = o;
    }
}

function useContent(){
    var editor = document.getElementById('cmu-ai-editor');
    if (!editor) { showError('Editor missing.'); return; }
    if (!state.fieldMap || !state.targetForm) { showError('Use This Content is only available when the AI generator is opened from a CRUD create/edit form. On this standalone page, copy the generated content manually.'); return; }
    var data = {};
    try { data = JSON.parse(editor.value) || {}; }
    catch (_) { showError('Generated content is not valid JSON. Edit it into valid JSON first.'); return; }
    var form = (typeof state.targetForm === 'string')
        ? (document.querySelector(state.targetForm) || document.getElementById(state.targetForm))
        : state.targetForm;
    if (!form) { showError('Target form not found in the page.'); return; }

    var composed = {};
    // primary mapping (AI key -> form field name)
    Object.keys(state.fieldMap).forEach(function(aiKey){
        var targetName = state.fieldMap[aiKey];
        if (!targetName || !(aiKey in data)) return;
        var val = data[aiKey];
        if (typeof val === 'string' && val.trim() !== '') {
            composed[targetName] = (composed[targetName] !== undefined)
                ? composed[targetName] + '\n\n' + val : val;
        } else if (Array.isArray(val) && val.length) {
            composed[targetName] = (composed[targetName] || '') + (composed[targetName] ? '\n\n' : '') + val.join('\n- ');
        } else if (typeof val !== 'string') {
            val = JSON.stringify(val);
            composed[targetName] = (composed[targetName] || '') + (composed[targetName] ? '\n\n' : '') + val;
        }
    });

    // content-type-specific composition
    if (state.contentType === 'university') {
        var extra = '';
        if (Array.isArray(data.programs) && data.programs.length) extra += 'Programs: ' + data.programs.join(', ');
        else if (typeof data.programs === 'string' && data.programs) extra += 'Programs: ' + data.programs;
        if (Array.isArray(data.selling_points) && data.selling_points.length) {
            if (extra) extra += '\n\n';
            extra += 'Selling points:\n- ' + data.selling_points.join('\n- ');
        } else if (typeof data.selling_points === 'string' && data.selling_points) {
            if (extra) extra += '\n\n';
            extra += 'Selling points: ' + data.selling_points;
        }
        if (extra) {
            composed['description'] = (composed['description'] || '') + (composed['description'] ? '\n\n' : '') + extra;
        }
    }
    if (state.contentType === 'hero_slide') {
        if (data.destination && !composed['subtitle']) {
            composed['subtitle'] = data.destination;
        }
    }

    var filled = [];
    Object.keys(composed).forEach(function(name){
        var el = form.querySelector('[name="' + name + '"]');
        if (!el) return;
        var val = composed[name];
        if (el.tagName === 'SELECT') {
            var opts = Array.prototype.slice.call(el.options);
            var match = opts.find(function(o){
                return (o.value || '').toLowerCase() === String(val).toLowerCase()
                    || (o.text || '').toLowerCase() === String(val).toLowerCase();
            });
            if (match) el.value = match.value;
            else el.value = val;
        } else {
            el.value = val;
        }
        try {
            el.dispatchEvent(new Event('input', {bubbles:true}));
            el.dispatchEvent(new Event('change', {bubbles:true}));
        } catch (e) {}
        filled.push(name);
    });

    if (filled.length === 0) { showError('No mapped form fields matched the generated content.'); return; }
    flashButton('cmu-ai-use', '<i class="fas fa-check"></i> Filled (' + filled.length + ')');
    setTimeout(function(){ close(); }, 700);
}

function openModal(opts){
    opts = opts || {};
    state = {
        contentType: opts.contentType || null,
        fieldMap: opts.fieldMap || null,
        targetForm: opts.targetForm || null,
        lastRequestId: null,
        busy: false
    };
    var modal = document.getElementById('cmu-ai-modal');
    var formArea = document.getElementById('cmu-ai-context-form');
    var badge = document.getElementById('cmu-ai-type-badge');
    var banner = document.getElementById('cmu-ai-banner');
    if (badge) badge.textContent = opts.contentType || '';
    if (banner) {
        banner.className = 'cmu-ai-banner info';
        if (opts.targetForm) {
            banner.innerHTML = '<i class="fas fa-info-circle"></i> Generated fields will be inserted into the parent form. Nothing is saved automatically until you click the form\'s Save button.';
        } else {
            banner.innerHTML = '<i class="fas fa-info-circle"></i> This is standalone generation. Review and edit before approving. Approval records this in history only — it does not publish.';
        }
        banner.hidden = false;
    }
    buildForm(opts.contentType, formArea, opts.contextSeed);
    clearEditor();
    if (modal) modal.hidden = false;
    document.body.style.overflow = 'hidden';
}

function close(){
    var modal = document.getElementById('cmu-ai-modal');
    if (modal) modal.hidden = true;
    document.body.style.overflow = '';
}

// Standalone-page helpers (used by ai/index.php, not by the CRUD modal).
function setContentType(type){
    state.contentType = type || null;
    var badge = document.getElementById('cmu-ai-type-badge');
    if (badge) badge.textContent = type || '';
    var formArea = document.getElementById('cmu-ai-context-form');
    if (formArea) {
        buildForm(type, formArea, state.contextSeed || {});
    }
    clearEditor();
}

async function copyGenerated(requestId){
    if (!requestId) return;
    var c = getCfg();
    if (!c.generateUrl) { alert('AI endpoint not configured.'); return; }
    try {
        var json = await fetchJson(c.generateUrl, {requestId: Number(requestId)});
        if (!json.success) { alert(json.error || 'Could not load this generation.'); return; }
        var data = json.data;
        var text = (typeof data === 'string') ? data : JSON.stringify(data, null, 2);
        await navigator.clipboard.writeText(text);
        alert('Copied generation #' + requestId + ' to clipboard. Paste it into any CRUD form field as needed.');
    } catch (e) {
        alert((e && e.message) ? e.message : String(e));
    }
}

function init(){
    document.addEventListener('click', function(e){
        var t = e.target;
        if (!t) return;
        if (t.closest && t.closest('[data-ai-close]')) { close(); return; }
        if (t.id === 'cmu-ai-generate') generate();
        else if (t.id === 'cmu-ai-regen') generate();
        else if (t.id === 'cmu-ai-improve') improve();
        else if (t.id === 'cmu-ai-copy') copyEditor();
        else if (t.id === 'cmu-ai-approve') approve();
        else if (t.id === 'cmu-ai-use') useContent();
        else if (t.id === 'cmu-ai-clear') clearEditor();
    });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') close();
    });
    // Standalone page: wire the content-type selector (if present) and build the initial form.
    var sel = document.getElementById('cmu-ai-content-type');
    if (sel) {
        sel.addEventListener('change', function(){ setContentType(this.value); });
        setContentType(sel.value);
    }
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
else init();

window.ConnectMyUni = window.ConnectMyUni || {};
window.ConnectMyUni.Ai = window.ConnectMyUni.Ai || {};
window.ConnectMyUni.Ai.__coreLoaded = true;
window.ConnectMyUni.Ai.fieldDefs = fieldDefs;
window.ConnectMyUni.Ai.open = openModal;
window.ConnectMyUni.Ai.close = close;
window.ConnectMyUni.Ai.generate = generate;
window.ConnectMyUni.Ai.improve = improve;
window.ConnectMyUni.Ai.copyEditor = copyEditor;
window.ConnectMyUni.Ai.approve = approve;
window.ConnectMyUni.Ai.useContent = useContent;
window.ConnectMyUni.Ai.clearEditor = clearEditor;
window.ConnectMyUni.Ai.buildContextForm = buildForm;
window.ConnectMyUni.Ai.setContentType = setContentType;
window.ConnectMyUni.Ai.copyGenerated = copyGenerated;
window.ConnectMyUni.Ai.cfg = getCfg;
window.ConnectMyUni.Ai.setField = function(name, val){
    var el = document.getElementById('cmu-ai-ctx-' + name);
    if (el) {
        el.value = val;
        el.focus();
        el.style.borderColor = '#0d9488';
        setTimeout(function(){ el.style.borderColor = ''; }, 700);
    }
};
window.ConnectMyUni.Ai.toggleGuide = function(guideName){
    var box = document.getElementById('cmu-ai-guide-' + guideName);
    if (box) {
        box.style.display = (box.style.display === 'none' || !box.style.display) ? 'block' : 'none';
    }
};
})();
</script>
<?php