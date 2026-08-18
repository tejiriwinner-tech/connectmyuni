/**
 * Connect MyUni — Public UI bootstrap (concise)
 * ==============================================
 * This file previously ran a broad, competing `.animate-on-scroll`
 * observer across every `.container / .row / .col-*`, which conflicted
 * with the single `cmi-*` motion system in animations.js.
 *
 * It is now intentionally minimal:
 *   - It only flags the page as "loaded" so entry animations fire.
 *   - All scroll reveals & parallax live in the ONE system:
 *     animations.js (IntersectionObserver reveal + data-depth engine).
 *
 * Keep it small. Do not add competing scroll logic here.
 * ============================================================= */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.body.classList.add('page-loaded');
    });
})();
