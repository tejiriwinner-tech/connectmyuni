// Scroll Animation Handler
document.addEventListener('DOMContentLoaded', function() {
    // Trigger page load animation
    document.body.classList.add('page-loaded');

    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-on-scroll');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all service cards
    const cards = document.querySelectorAll('.service-card-item');
    cards.forEach(card => {
        observer.observe(card);
    });

    // Observe all sections and divs for animation
    const allSections = document.querySelectorAll('section, .container, .row, .col-lg-4, .col-md-6, .col-12');
    allSections.forEach(element => {
        observer.observe(element);
    });

    // Observe other sections for animation
    const animateElements = document.querySelectorAll('.about-section, .section-title, .feature-box');
    animateElements.forEach(element => {
        observer.observe(element);
    });
});
