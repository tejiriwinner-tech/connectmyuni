/**
 * Connect MyUni — Cinematic Landing Background (Three.js)
 * =======================================================
 * ONE global dimensional atmosphere for the public homepage.
 *
 *   - Fixed, full-screen canvas positioned BEHIND the HTML content
 *     (pointer-events: none, aria-hidden in the markup).
 *   - A dimensional grid floor that recedes + tilts as the user scrolls
 *     (scroll position → grid rotation / z / camera movement).
 *   - Honors prefers-reduced-motion (renders ONE static frame).
 *   - Graceful CSS fallback when WebGL is unavailable
 *     (body.cmi-no-webgl).
 *
 * It only initialises when #cmi-three-canvas exists in the DOM, so it
 * is a no-op on every page except the homepage. There is exactly ONE
 * renderer per page.
 * ============================================================= */
(function () {
    'use strict';

    var canvas = document.getElementById('cmi-three-canvas');
    if (!canvas || typeof window.THREE === 'undefined') {
        return; /* not the landing page, or the library failed to load */
    }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var renderer = null;
    var scene = null;
    var camera = null;
    var gridMain = null;
    var gridFaint = null;
    var t = 0;

    /* ── WebGL support check — any failure → clean static fallback ── */
    try {
        renderer = new THREE.WebGLRenderer({
            canvas: canvas,
            alpha: true,
            antialias: true,
            powerPreference: 'low-power'
        });
    } catch (err) {
        canvas.parentNode.removeChild(canvas);
        document.body.classList.add('cmi-no-webgl');
        return;
    }

    var width = window.innerWidth;
    var height = window.innerHeight;
    var mobile = width < 768;

    renderer.setSize(width, height);
    /* Cap DPR — desktop 1.5, mobile 1.0 (big GPU savings, visually equal) */
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, mobile ? 1 : 1.5));

    scene = new THREE.Scene();
    scene.fog = new THREE.Fog(0x0b1f3f, 44, 92); /* soft depth fade, hides grid edges */

    camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 220);
    camera.position.set(0, 17, 30);
    camera.lookAt(0, -4, 0);

    /* ── Main dimensional grid floor ── */
    function setGridOpacity(material, opacity) {
        if (Array.isArray(material)) {
            material.forEach(function (m) { m.transparent = true; m.opacity = opacity; });
        } else if (material) {
            material.transparent = true;
            material.opacity = opacity;
        }
    }

    gridMain = new THREE.GridHelper(mobile ? 70 : 96, mobile ? 34 : 62, 0x1a56db, 0x1340a8);
    setGridOpacity(gridMain.material, mobile ? 0.20 : 0.30);
    gridMain.rotation.x = -0.45;
    scene.add(gridMain);

    /* ── Faint secondary grid — adds layered depth (desktop only) ── */
    if (!mobile) {
        gridFaint = new THREE.GridHelper(90, 24, 0xffffff, 0xffffff);
        setGridOpacity(gridFaint.material, 0.09);
        gridFaint.rotation.x = -0.45;
        gridFaint.rotation.y = Math.PI / 4;
        gridFaint.position.y = 0.4;
        scene.add(gridFaint);
    }

    /* ── Scroll state ── */
    var maxScroll = 1;
    var progress = 0;   /* eased */
    var target = 0;     /* raw from scroll */

    function measureScroll() {
        maxScroll = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
    }
    measureScroll();

    function onScroll() {
        measureScroll();
        target = window.scrollY / maxScroll;
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ── Resize (throttled to one rAF) ── */
    var resizeQueued = false;
    function onResize() {
        if (resizeQueued) { return; }
        resizeQueued = true;
        window.requestAnimationFrame(function () {
            width = window.innerWidth;
            height = window.innerHeight;
            mobile = width < 768;
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, mobile ? 1 : 1.5));
            renderer.setSize(width, height);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            resizeQueued = false;
        });
    }
    window.addEventListener('resize', onResize, { passive: true });

    /* ── Static frame under reduced motion (no loop, no scroll sync) ── */
    if (reduceMotion) {
        renderer.render(scene, camera);
        return;
    }

    /* ── Animation loop: scroll choreography + gentle idle life ── */
    function frame(now) {
        window.requestAnimationFrame(frame);

        t = now * 0.001;
        progress += (target - progress) * 0.06;   /* eased scroll follow */

        /* Grid recedes + tilts as the user scrolls down */
        gridMain.rotation.x = -0.45 + progress * -0.55;
        gridMain.position.z = progress * -34;
        if (gridFaint) {
            gridFaint.rotation.x = -0.45 + progress * -0.5;
            gridFaint.position.z = progress * -30;
        }

        /* Slow idle drift — restrained, readable, never game-like */
        gridMain.rotation.z = Math.sin(t * 0.05) * 0.012;
        camera.position.x = Math.sin(t * 0.035) * 0.7;
        camera.position.y = 17 - progress * 5 + Math.sin(t * 0.06) * 0.15;
        camera.lookAt(0, -4 - progress * 2, 0);

        renderer.render(scene, camera);
    }
    window.requestAnimationFrame(frame);
})();
