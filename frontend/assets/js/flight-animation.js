/**
 * Connect MyUni — Flight Animation (GSAP ScrollTrigger)
 * 
 * Creates a smooth 3D-feeling scroll animation with an airplane
 * gliding down the page behind foreground content.
 * 
 * Dependencies: GSAP 3.12.5, ScrollTrigger 3.12.5
 */

(function() {
    'use strict';

    // Register ScrollTrigger plugin
    gsap.registerPlugin(ScrollTrigger);

    console.log('Flight animation: GSAP and ScrollTrigger registered');

    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        console.log('Flight animation: Reduced motion detected, skipping');
        return; // Skip animation if user prefers reduced motion
    }

    console.log('Flight animation: Starting initialization');

    // Use existing flight animation container from header.php
    const flightContainer = document.getElementById('flight-animation-container');
    if (!flightContainer) {
        console.warn('Flight animation: Container element not found');
        return;
    }

    console.log('Flight animation: Container found', flightContainer);
    console.log('Flight animation: Container styles', window.getComputedStyle(flightContainer));

    // Create airplane element with SVG
    const airplane = document.createElement('div');
    airplane.id = 'flight-airplane';
    airplane.innerHTML = `
        <svg viewBox="0 0 120 60" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Airplane Body -->
            <ellipse cx="60" cy="30" rx="50" ry="12" fill="#ffffff" opacity="0.9"/>
            <!-- Cockpit -->
            <ellipse cx="95" cy="30" rx="15" ry="8" fill="#e8f4f8"/>
            <!-- Wings -->
            <path d="M 40 30 L 35 10 L 45 10 L 50 30 Z" fill="#d0e8f0"/>
            <path d="M 40 30 L 35 50 L 45 50 L 50 30 Z" fill="#d0e8f0"/>
            <!-- Tail -->
            <path d="M 15 30 L 5 15 L 20 20 L 25 30 Z" fill="#c8e0e8"/>
            <path d="M 15 30 L 5 45 L 20 40 L 25 30 Z" fill="#c8e0e8"/>
            <!-- Windows -->
            <circle cx="75" cy="30" r="2" fill="#a0c8d8"/>
            <circle cx="82" cy="30" r="2" fill="#a0c8d8"/>
            <circle cx="89" cy="30" r="2" fill="#a0c8d8"/>
            <!-- Engine -->
            <ellipse cx="45" cy="38" rx="8" ry="4" fill="#b8d8e8"/>
        </svg>
    `;
    flightContainer.appendChild(airplane);

    console.log('Flight animation: Airplane created', airplane);
    console.log('Flight animation: Airplane HTML', airplane.innerHTML);

    // Create flight trail effect
    const trail = document.createElement('div');
    trail.className = 'flight-trail';
    trail.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);';
    airplane.appendChild(trail);

    // Section references for scroll triggers
    const heroSection = document.getElementById('home');
    const aboutSection = document.getElementById('about');
    const servicesSection = document.getElementById('services');
    const eventsSection = document.getElementById('events');
    const contactSection = document.getElementById('contact');

    console.log('Flight animation: Sections found', {
        hero: !!heroSection,
        about: !!aboutSection,
        services: !!servicesSection,
        events: !!eventsSection,
        contact: !!contactSection
    });

    if (!heroSection || !contactSection) {
        console.warn('Flight animation: Required sections not found');
        return;
    }

    // Calculate section positions
    const totalScrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    const heroHeight = heroSection.offsetHeight;
    const contactTop = contactSection.offsetTop;

    // Initial state - airplane above viewport
    gsap.set(airplane, {
        y: -150,
        x: '50%',
        rotation: 0,
        scale: 0.8,
        opacity: 0
    });

    console.log('Flight animation: Initial airplane state set');

    // Master timeline for the flight animation
    const flightTimeline = gsap.timeline({
        scrollTrigger: {
            trigger: 'body',
            start: 'top top',
            end: 'bottom bottom',
            scrub: 1, // Smooth scrubbing
            onUpdate: (self) => {
                // Update trail opacity based on scroll speed
                const scrollSpeed = Math.abs(self.getVelocity() / 1000);
                trail.style.opacity = Math.min(scrollSpeed, 0.6);
            }
        }
    });

    // Phase 1: Entry from above hero section
    flightTimeline.to(airplane, {
        y: heroHeight * 0.3,
        opacity: 0.9,
        scale: 1,
        rotation: -5,
        duration: 0.15,
        ease: 'power2.out'
    });

    // Phase 2: Glide through "World-Class Education Universe" (about section)
    if (aboutSection) {
        flightTimeline.to(airplane, {
            y: aboutSection.offsetTop + aboutSection.offsetHeight * 0.5,
            rotation: 3,
            scale: 1.1,
            duration: 0.2,
            ease: 'sine.inOut'
        });
    }

    // Phase 3: Pass through "Our Services" section
    if (servicesSection) {
        flightTimeline.to(airplane, {
            y: servicesSection.offsetTop + servicesSection.offsetHeight * 0.5,
            rotation: -8,
            scale: 1.05,
            duration: 0.15,
            ease: 'sine.inOut'
        });
    }

    // Phase 4: Continue through "Latest News & Events" section
    if (eventsSection) {
        flightTimeline.to(airplane, {
            y: eventsSection.offsetTop + eventsSection.offsetHeight * 0.5,
            rotation: 5,
            scale: 1.1,
            duration: 0.2,
            ease: 'sine.inOut'
        });
    }

    // Phase 5: Landing sequence at Contact section
    flightTimeline.to(airplane, {
        y: contactTop + 100,
        rotation: 15, // Nose down for landing
        scale: 0.9,
        duration: 0.1,
        ease: 'power2.in'
    });

    // Phase 6: Final landing - level out and stop
    flightTimeline.to(airplane, {
        y: contactTop + 200,
        rotation: 0, // Level out
        scale: 0.8,
        opacity: 0.7,
        duration: 0.1,
        ease: 'power2.out'
    });

    // Add subtle horizontal movement for more natural flight path
    gsap.to(airplane, {
        x: (i, target) => {
            // Slight sine wave motion
            return 'calc(50% + ' + Math.sin(i * 0.01) * 30 + 'px)';
        },
        scrollTrigger: {
            trigger: 'body',
            start: 'top top',
            end: 'bottom bottom',
            scrub: 1
        }
    });

    // Add banking effect based on scroll direction
    let lastScrollY = window.scrollY;
    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        const scrollDirection = currentScrollY > lastScrollY ? 1 : -1;
        const scrollSpeed = Math.abs(currentScrollY - lastScrollY);
        
        // Add subtle banking based on scroll speed and direction
        const bankAngle = scrollDirection * Math.min(scrollSpeed * 0.5, 10);
        
        gsap.to(airplane, {
            rotationY: bankAngle,
            duration: 0.3,
            ease: 'power2.out'
        });
        
        lastScrollY = currentScrollY;
    }, { passive: true });

    // Responsive adjustments
    function adjustForMobile() {
        if (window.innerWidth < 768) {
            gsap.set(airplane, { scale: 0.6 });
        } else {
            gsap.set(airplane, { scale: 1 });
        }
    }

    window.addEventListener('resize', adjustForMobile);
    adjustForMobile();

    // Clean up ScrollTrigger on page unload
    window.addEventListener('beforeunload', () => {
        ScrollTrigger.getAll().forEach(trigger => trigger.kill());
    });

})();
