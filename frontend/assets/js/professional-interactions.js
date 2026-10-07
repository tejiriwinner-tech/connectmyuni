/**
 * ConnectMyUni — Professional Interactions & Scroll Reveal Handler
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Respect reduced motion settings
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ------------------------------------------------------------------
    // 1. Navbar Scroll State Handler
    // ------------------------------------------------------------------
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        const handleScroll = function () {
            if (window.scrollY > 40) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll(); // Initial check
    }

    // ------------------------------------------------------------------
    // 2. Target Destination Hub Filter Handler (Homepage Stats HUD)
    // ------------------------------------------------------------------
    const hubButtons = document.querySelectorAll('.cmi-3d-hub-btn');
    if (hubButtons.length > 0) {
        hubButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                hubButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // ------------------------------------------------------------------
    // 3. Scroll Reveal Animations via IntersectionObserver
    // ------------------------------------------------------------------
    if (!prefersReducedMotion && 'IntersectionObserver' in window) {
        const revealElements = document.querySelectorAll(
            '.service-card-item, .event-card, .cmi-metric-card, .accordion-item, .cmi-contact-tile, .contact-map-card'
        );

        revealElements.forEach(function (el) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(24px)';
            el.style.transition = 'opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        let lastY = window.scrollY;
        let isScrollingUp = false;
        window.addEventListener('scroll', function () {
            const currentY = window.scrollY;
            isScrollingUp = currentY < lastY;
            lastY = currentY <= 0 ? 0 : currentY;
        }, { passive: true });

        const revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                } else {
                    const rect = entry.target.getBoundingClientRect();
                    if (rect.bottom < -40 || rect.top > window.innerHeight + 40) {
                        entry.target.style.opacity = '0';
                        entry.target.style.transform = isScrollingUp ? 'translateY(-16px)' : 'translateY(24px)';
                    }
                }
            });
        }, {
            root: null,
            rootMargin: '20px 0px -30px 0px',
            threshold: 0.08
        });

        revealElements.forEach(function (el) {
            revealObserver.observe(el);
        });
    }
});
