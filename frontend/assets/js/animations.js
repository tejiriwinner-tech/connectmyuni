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
    document.querySelectorAll('.cmi-stagger-children, .cmi-stagger-row, .cmi-stagger-group').forEach(function (parent) {
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

    /* ── 8. DEPTH / PARALLAX ENGINE ─────────────────────────
       ONE scroll-driven motion layer, part of this single system.

       Architecture — data attributes only:
         data-depth="background"    → tiny travel, gentle scale
         data-depth="decorative"    → slow travel, subtle opacity lift
         data-depth="content"       → minimal travel (text stays stable)
         data-depth="foreground"    → slightly faster travel (cards/visuals)
         data-depth="float"         → very subtle accent travel
         data-parallax-speed="0.15" → explicit speed override (-1..1)

       Design rules:
       - GPU-friendly: only transform + opacity, driven by rAF.
       - Restrained: travel is a fraction of the element's distance
         from the viewport centre → a FEW tens of px, no flying.
       - Intensity is scaled down on touch/tablets/mobile and fully
         disabled when prefers-reduced-motion is set (earlier return).
       - Elements carrying CSS keyframe motion (.cmi-float) or the
         pointer-driven tilt system are excluded to avoid conflicts.
       - Elements only update while near the viewport (cheap loop).
    ----------------------------------------------------------- */
    var depthEls = document.querySelectorAll(
        '[data-depth], [data-parallax-speed]'
    );
    var depthItems = [];
    var depthLayers = {
        'background': { travel: 8,   fade: 0,     scale: true  },
        'decorative': { travel: 16,  fade: 0.20,  scale: false },
        'content':    { travel: 4,   fade: 0,     scale: false },
        'foreground': { travel: 26,  fade: 0,     scale: false },
        'float':      { travel: 12,  fade: 0.30,  scale: false }
    };

    var coarsePointer = !window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var smallViewport = window.matchMedia('(max-width: 768px)').matches;
    /* Coarse/touch → roughly one third of desktop travel.
       Small screens + coarse → gentle enough to avoid nausea. */
    var motionIntensity = (!coarsePointer && !smallViewport) ? 1 : (coarsePointer ? 0.33 : 0.55);

    depthEls.forEach(function (el) {
        /* Skip elements whose transform is owned by another system. */
        if (el.classList.contains('cmi-float') || el.classList.contains('cmi-tilt')) {
            return;
        }

        var depth = el.getAttribute('data-depth');
        var explicit = parseFloat(el.getAttribute('data-parallax-speed'), 10);
        var cfg = depthLayers[depth] || { travel: 12, fade: 0.20, scale: false };
        if (!isNaN(explicit)) {
            /* data-parallax-speed="-0.15" → ~30px max travel */
            cfg = { travel: Math.abs(explicit) * 200, fade: 0, scale: false, explicit: true };
        }

        depthItems.push({
            el: el,
            travel: cfg.travel * motionIntensity,
            fade: cfg.fade,
            scale: cfg.scale
        });
    });

    var depthTicking = false;

    function applyDepth() {
        depthTicking = false;
        var vh = window.innerHeight || document.documentElement.clientHeight;

        depthItems.forEach(function (item) {
            var rect = item.el.getBoundingClientRect();
            var inView = rect.bottom > -vh * 0.6 && rect.top < vh * 1.6;
            if (!inView) {
                return; /* leave last transform in place; avoid reflow work */
            }

            var center = rect.top + rect.height / 2;
            /* -1 when fully above the viewport centre, +1 when below it */
            var norm = Math.max(-1.4, Math.min(1.4, (center - vh / 2) / (vh / 2)));

            var travel = -(norm * item.travel);
            var scale = 1;
            var opacity = 1;

            if (item.scale) {
                /* very gentle zoom as element leaves the viewport centre */
                scale = 1 + Math.abs(norm) * 0.006;
            }
            if (item.fade) {
                var fadeAt = (1 - Math.abs(norm)) ;
                opacity = 0.5 + 0.5 * Math.min(1, Math.max(0, fadeAt / (1 - item.fade * 0.4)));
                opacity = Math.round(opacity * 100) / 100;
            }

            var t = 'translate3d(0,' + travel.toFixed(1) + 'px,0)';
            if (scale !== 1) { t += ' scale(' + scale.toFixed(4) + ')'; }
            item.el.style.transform = t;
            if (opacity !== 1) { item.el.style.opacity = opacity; }
        });
    }

    function requestDepth() {
        if (!depthTicking) {
            depthTicking = true;
            window.requestAnimationFrame(applyDepth);
        }
    }

    if (depthItems.length) {
        window.addEventListener('scroll', requestDepth, { passive: true });
        window.addEventListener('resize', requestDepth, { passive: true });
        requestDepth();
    }

})();
