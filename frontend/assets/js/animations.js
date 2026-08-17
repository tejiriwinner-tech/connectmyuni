/**
 * Connect MyUni — Animation System (JS)
 * v1.0  |  Lightweight IntersectionObserver-based scroll reveal
 *
 * Respects:
 *   - prefers-reduced-motion  → disables all scroll-triggered animations
 *   - Single observer instance for performance
 *   - Elements animate once on first intersection (unobserved afterward)
 *
 * Usage:
 *   HTML  <div class="cmi-fade-up">...</div>
 *   HTML  <div class="cmi-stagger-children">...</div>
 *   HTML  <span class="cmi-counter" data-counter="500">0</span>
 *
 * Dependencies: none (pure JavaScript, ES6)
 * ====================================================================
 */
(function () {
    'use strict';

    /* ── 0. Navbar scrolled state (static elevation — not an animation) ── */
    var navbar = document.querySelector('.navbar');
    if (navbar) {
        var onNavScroll = function () {
            navbar.classList.toggle('navbar--scrolled', window.scrollY > 10);
        };
        window.addEventListener('scroll', onNavScroll, { passive: true });
        onNavScroll();
    }

    /* ── 1. Reduced-motion guard ──────────────────────────────── */
    var prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    /* ── 0b. Back-to-top (functional — stays active with reduced motion) ── */
    var toTop = document.getElementById('cmiToTop');
    if (toTop) {
        var onToTopScroll = function () {
            toTop.classList.toggle('cmi-to-top--show', window.scrollY > 400);
        };
        window.addEventListener('scroll', onToTopScroll, { passive: true });
        onToTopScroll();
        toTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
        });
    }

    if (prefersReducedMotion) {
        return; /* Bail out entirely — no scroll animations */
    }

    /* ── 2. Single IntersectionObserver ───────────────────────── */
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-animated');
                observer.unobserve(entry.target); /* fire once */
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    /* ── 3. Observe scroll-reveal elements ────────────────────── */
    var revealSelectors = [
        '.cmi-fade-up',
        '.cmi-fade-in',
        '.cmi-slide-in-left',
        '.cmi-slide-in-right',
        '.cmi-scale',
        '.cmi-image',
        '.cmi-section-enter'
    ];

    revealSelectors.forEach(function (selector) {
        document.querySelectorAll(selector).forEach(function (el) {
            observer.observe(el);
        });
    });

    /* ── 4. Stagger containers (parent observed, children CSS-transition with delay) */
    document.querySelectorAll('.cmi-stagger-children, .cmi-stagger-row').forEach(function (parent) {
        observer.observe(parent);
    });

    /* ── 5. Counter / stat animation ──────────────────────────── */
    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-counter'), 10) || 0;
        var duration = parseInt(el.getAttribute('data-counter-duration'), 10) || 2000;
        var start = null;
        var step = function (timestamp) {
            if (!start) start = timestamp;
            var progress = Math.min((timestamp - start) / duration, 1);
            var ease = 1 - Math.pow(1 - progress, 3); /* easeOutCubic */
            el.textContent = Math.round(target * ease);
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };
        requestAnimationFrame(step);
    }

    var counterObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-animated');
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.cmi-counter[data-counter]').forEach(function (el) {
        counterObserver.observe(el);
    });

    /* ── 6. Smooth anchor scrolling ──────────────────────────── */
    var smoothScrollSupported = false;
    try {
        smoothScrollSupported = 'scrollBehavior' in document.documentElement.style;
    } catch (e) {
        smoothScrollSupported = false;
    }

    if (smoothScrollSupported) {
        document.documentElement.style.scrollBehavior = 'smooth';
    }

    /* ── 7. Subtle 3D card tilt (mouse-position) ───────────────
       Applied to .cmi-tilt elements only. Guarded by:
       - prefers-reduced-motion (returned earlier)
       - hover:fine device check (no tilt on touch)
       - Small manual adjustment +/- 4deg max; GPU-friendly. */
    var canTilt = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (canTilt) {
        var tiltCards = document.querySelectorAll('.cmi-tilt');

        tiltCards.forEach(function (card) {
            card.addEventListener('mouseenter', function () {
                card.classList.add('cmi-tilt--active');
            });

            card.addEventListener('mousemove', function (e) {
                var rect = card.getBoundingClientRect();
                var px = (e.clientX - rect.left) / rect.width;   /* 0..1 */
                var py = (e.clientY - rect.top) / rect.height;   /* 0..1 */

                var rotateY = (px - 0.5) * 8;   /* -4..4 deg */
                var rotateX = (0.5 - py) * 8;   /* -4..4 deg */

                card.style.setProperty('--cmi-tilt-x', rotateX.toFixed(2) + 'deg');
                card.style.setProperty('--cmi-tilt-y', rotateY.toFixed(2) + 'deg');
            });

            card.addEventListener('mouseleave', function () {
                card.classList.remove('cmi-tilt--active');
                card.style.setProperty('--cmi-tilt-x', '0deg');
                card.style.setProperty('--cmi-tilt-y', '0deg');
            });
        });
    }

})();
