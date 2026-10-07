<?php
// ─────────────────────────────────────────────────────────────
// CONNECTMYUNI HOMEPAGE  —  Phase 10 Final Clean Rebuild
// Design hierarchy: Content → Photography → Typography → Motion
// ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/../../backend/bootstrap.php';

use ConnectMyUni\Services\HeroSlideService;
use ConnectMyUni\Services\ServiceService;
use ConnectMyUni\Services\EventService;
use ConnectMyUni\Helpers\MediaResolver;

// Fetch Hero Slides from CMS
$heroItems = [];
try {
    foreach ((new HeroSlideService())->getActiveSlides() as $slide) {
        $desktop = MediaResolver::url((string) ($slide['image_path'] ?? ''));
        if ($desktop !== '') {
            $heroItems[] = [
                'title'      => htmlspecialchars((string) ($slide['title']    ?? '')),
                'subtitle'   => htmlspecialchars((string) ($slide['subtitle'] ?? '')),
                'cta_text'   => htmlspecialchars((string) ($slide['cta_text'] ?? '')),
                'cta_url'    => htmlspecialchars((string) ($slide['cta_url']  ?? '')),
                'image_url'  => $desktop,
                'mobile_url' => MediaResolver::url((string) ($slide['mobile_image_path'] ?? '')),
            ];
        }
    }
} catch (\Throwable $e) { error_log('Hero slides load failed: ' . $e->getMessage()); }

// Fetch Services from CMS (accordion on homepage)
$services = [];
try { $services = (new ServiceService())->getAllActive(); } catch (\Throwable $e) {}

// Fetch 3 latest published events from CMS
$events = [];
try { $events = array_slice((new EventService())->getAllForPublic(), 0, 3); } catch (\Throwable $e) {}

// SEO Meta Configuration
$page_title = 'Connect MyUni | Premier Overseas Education & University Admission Consultancy';
$meta_description = 'Connect MyUni connects ambitious students with world-class universities in the UK, USA, Canada, Australia, and Europe. Free admission counseling, student visas, and scholarships.';
$meta_keywords = 'study abroad nigeria, education consultancy lagos, study in uk from nigeria, study in canada, study in usa, overseas university admission, student visa counseling, scholarship assistance, connect myuni';
$canonical_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'connectmyuni.com') . CONNECTMYUNI_BASE_URL;

// WebSite Schema.org JSON-LD
$schema_json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    'name'     => 'Connect MyUni',
    'url'      => $canonical_url,
    'potentialAction' => [
        '@type'       => 'SearchAction',
        'target'      => $canonical_url . 'universities.php?q={search_term_string}',
        'query-input' => 'required name=search_term_string'
    ]
];

include __DIR__ . '/../components/header.php';
?>

<!-- =============================================
     HERO SECTION — full-bleed photography hero
     Falls back to static image if no CMS slides
     ============================================= -->
<section id="home" class="hero-section cinematic-hero">

    <?php if (!empty($heroItems)): $heroIsCarousel = count($heroItems) > 1; ?>
    <!-- CMS-driven Bootstrap carousel -->
    <div id="homeHeroCarousel" class="carousel slide carousel-fade h-100"
         data-bs-ride="carousel" data-bs-interval="6000">
        <div class="carousel-inner h-100">
            <?php foreach ($heroItems as $i => $slide): ?>
            <div class="carousel-item h-100<?php echo $i === 0 ? ' active' : ''; ?>">
                <div class="hero-bg-wrapper">
                    <picture>
                        <?php if ($slide['mobile_url'] !== ''): ?>
                            <source media="(max-width: 767px)" srcset="<?php echo $slide['mobile_url']; ?>">
                        <?php endif; ?>
                        <img src="<?php echo $slide['image_url']; ?>"
                             alt="<?php echo $slide['title']; ?>"
                             class="hero-bg-img"
                             <?php echo $i === 0 ? '' : 'loading="lazy"'; ?>>
                    </picture>
                </div>
                <div class="hero-overlay-gradient"></div>
                <div class="hero-content">
                    <div class="container h-100">
                        <div class="row justify-content-center align-items-center h-100">
                            <div class="col-lg-9 col-md-11 text-center text-white">
                                <h1 class="hero-title fw-bold mb-3"><?php echo $slide['title']; ?></h1>
                                <?php if ($slide['subtitle'] !== ''): ?>
                                    <p class="hero-subtitle mb-4"><?php echo $slide['subtitle']; ?></p>
                                <?php endif; ?>
                                <?php if ($slide['cta_text'] !== ''): ?>
                                    <a href="<?php echo !empty($slide['cta_url']) ? $slide['cta_url'] : '#contact'; ?>"
                                       class="btn btn-hero-cta"><?php echo $slide['cta_text']; ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($heroIsCarousel): ?>
            <button class="carousel-control-prev" type="button"
                    data-bs-target="#homeHeroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button"
                    data-bs-target="#homeHeroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- Static fallback hero (no CMS slides) -->
    <div class="hero-bg-wrapper">
        <img src="<?php echo $base_url; ?>frontend/assets/images/hero_image.png"
             alt="Students exploring global education opportunities"
             class="hero-bg-img">
    </div>
    <div class="hero-overlay-gradient"></div>
    <div class="hero-content">
        <div class="container h-100">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-lg-9 col-md-11 text-center text-white">
                    <h1 class="hero-title mb-3">
                        Unite Your Passion with Purpose at<br>
                        <span class="text-highlight">CONNECT MYUNI</span>
                    </h1>
                    <p class="hero-subtitle mb-4">
                        Explore world-class education with a team dedicated to your global success.
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center align-items-center">
                        <a href="<?php echo $base_url; ?>universities.php" class="btn btn-hero-cta">EXPLORE UNIVERSITIES</a>
                        <a href="#contact" class="btn btn-hero-ghost">TALK TO A CONSULTANT</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scroll indicator -->
    <div class="cmi-scroll-indicator" aria-hidden="true"></div>

    <!-- Disable auto-play when user prefers reduced motion -->
    <script>
        (function () {
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                var el = document.getElementById('homeHeroCarousel');
                if (el) el.setAttribute('data-bs-ride', 'false');
            }
        })();
    </script>
</section>


<!-- =============================================
     WHY CONNECT MYUNI — 3 differentiator cards
     Overlaps upward into the hero bottom edge
     ============================================= -->
<section class="hero-cards-section">
    <div class="hero-cards-overlap">
        <div class="container">
            <div class="row g-4">

                <div class="col-lg-4 col-md-6">
                    <div class="service-card-item text-center">
                        <div class="service-icon-circle mx-auto mb-4">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                        <h3 class="service-card-title mb-3">Student-Centered Approach</h3>
                        <p class="service-card-text">We are dedicated to understanding the unique goals and needs of each student, providing personalized guidance to help them achieve academic success and navigate the complexities of international education.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="service-card-item text-center">
                        <div class="service-icon-circle mx-auto mb-4">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                        <h3 class="service-card-title mb-3">Tailored Placement Services</h3>
                        <p class="service-card-text">Our team connects students with institutions that best align with their academic and professional aspirations, ensuring they receive the best possible education opportunities around the world.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="service-card-item text-center">
                        <div class="service-icon-circle mx-auto mb-4">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                        <h3 class="service-card-title mb-3">Compliance &amp; Global Standards</h3>
                        <p class="service-card-text">We adhere strictly to international education and visa regulations, ensuring our students meet all requirements while enjoying a seamless and compliant study experience in their chosen destination.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


<!-- =============================================
     GLOBAL EDUCATION NETWORK SECTION
     Professional Statistics & Destinations
     ============================================= -->
<section id="about" class="cmi-3d-showcase-section">
    <div class="cmi-3d-backdrop-fx"></div>
    <div class="container position-relative">
        <div class="row">
            <!-- Full-width content -->
            <div class="col-12">
                <h2 class="cmi-3d-title">Your World-Class Education Universe, Connected in Real-Time</h2>

                <p class="cmi-3d-text">
                    Explore Connect MyUni's international gateway connecting ambitious students to top-tier universities across 5 continents. With over 21 years of dedicated service, we simplify admissions, visas, flight itineraries, and global placements with personalized excellence.
                </p>

                <!-- Hub Switcher Tags -->
                <div class="cmi-3d-hubs-filter mb-5">
                    <div class="cmi-filter-label mb-3">Target Destinations:</div>
                    <div class="cmi-filter-buttons d-flex flex-wrap gap-2">
                        <button type="button" class="cmi-3d-hub-btn active" data-hub="all">🌐 Global (All)</button>
                        <button type="button" class="cmi-3d-hub-btn" data-hub="uk">🇬🇧 United Kingdom</button>
                        <button type="button" class="cmi-3d-hub-btn" data-hub="us">🇺🇸 USA</button>
                        <button type="button" class="cmi-3d-hub-btn" data-hub="ca">🇨🇦 Canada</button>
                        <button type="button" class="cmi-3d-hub-btn" data-hub="my">🇲🇾 Malaysia</button>
                        <button type="button" class="cmi-3d-hub-btn" data-hub="ph">🇵🇭 Philippines</button>
                        <button type="button" class="cmi-3d-hub-btn" data-hub="au">🇦🇺 Australia</button>
                        <button type="button" class="cmi-3d-hub-btn" data-hub="ng">🇳🇬 Nigeria HQ</button>
                    </div>
                </div>

                <!-- Live Metrics HUD -->
                <div class="cmi-3d-metrics-grid mb-5">
                    <div class="cmi-metric-card">
                        <div class="cmi-metric-num">500+</div>
                        <div class="cmi-metric-label">Partner Institutions</div>
                    </div>
                    <div class="cmi-metric-card">
                        <div class="cmi-metric-num">21+</div>
                        <div class="cmi-metric-label">Years Track Record</div>
                    </div>
                    <div class="cmi-metric-card">
                        <div class="cmi-metric-num">30+</div>
                        <div class="cmi-metric-label">Study Destinations</div>
                    </div>
                    <div class="cmi-metric-card">
                        <div class="cmi-metric-num">98.4%</div>
                        <div class="cmi-metric-label">Visa Placement Rate</div>
                    </div>
                </div>

                <div class="cmi-3d-actions d-flex flex-wrap gap-3">
                    <a href="<?php echo $base_url; ?>universities.php" class="btn btn-hero-cta">EXPLORE UNIVERSITIES</a>
                    <a href="<?php echo $base_url; ?>about.php" class="btn btn-about-link">ABOUT CONNECT MYUNI</a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =============================================
     SERVICES SECTION
     ============================================= -->
<section id="services" class="services-section cinematic-services">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6">
                <h2 class="services-title">OUR SERVICES</h2>
                <div class="accordion accordion-services" id="servicesAccordion">

                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $idx => $svc): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="svcH<?php echo $idx; ?>">
                                <button class="accordion-button<?php echo $idx !== 0 ? ' collapsed' : ''; ?>"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#svcC<?php echo $idx; ?>"
                                        aria-expanded="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                                        aria-controls="svcC<?php echo $idx; ?>">
                                    <?php echo htmlspecialchars((string) ($svc['title'] ?? '')); ?>
                                </button>
                            </h2>
                            <div id="svcC<?php echo $idx; ?>"
                                 class="accordion-collapse collapse<?php echo $idx === 0 ? ' show' : ''; ?>"
                                 aria-labelledby="svcH<?php echo $idx; ?>"
                                 data-bs-parent="#servicesAccordion">
                                 <div class="accordion-body">
                                     <?php echo nl2br(htmlspecialchars((string) ($svc['description'] ?? ''))); ?>
                                 </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Static fallback services -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Student Placement</button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#servicesAccordion">
                                <div class="accordion-body">We connect qualified students with world-class universities and colleges, ensuring they find the perfect institution that matches their academic goals and aspirations.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">Training Programs</button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#servicesAccordion">
                                <div class="accordion-body">Our comprehensive training programs prepare students for international education, covering academic skills, cultural adaptation, and professional development.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">Test Preparation Services</button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#servicesAccordion">
                                <div class="accordion-body">Expert preparation for standardized tests including IELTS, TOEFL, SAT, GRE, and GMAT with proven strategies and experienced instructors.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">Language Training</button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#servicesAccordion">
                                <div class="accordion-body">Intensive English and other language programs designed to enhance communication skills and academic proficiency for international study.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">Consulting Services</button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#servicesAccordion">
                                <div class="accordion-body">Personalized consulting to guide your academic journey, from university selection to career planning and professional development.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSix">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">Post-Study Support</button>
                            </h2>
                            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#servicesAccordion">
                                <div class="accordion-body">Continuous support after graduation including career guidance, visa assistance, and networking opportunities with alumni and industry professionals.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSeven">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">Scholarship/Financial Aid Assistance</button>
                            </h2>
                            <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#servicesAccordion">
                                <div class="accordion-body">Expert guidance in identifying and applying for scholarships, grants, and financial aid opportunities to make education affordable and accessible.</div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="col-lg-6">
                <div class="services-oval-wrapper">
                    <!-- Ambient Glow FX -->
                    <div class="services-backdrop-glow"></div>

                    <!-- Outer Pulsing Circle Orbit Ring -->
                    <div class="services-oval-orbit"></div>

                    <!-- Complete Circle Portrait Frame -->
                    <div class="services-oval-container">
                        <img src="<?php echo $base_url; ?>frontend/assets/images/image2.png" alt="Education Consulting Services" class="services-image">
                        <div class="services-oval-gloss"></div>
                    </div>

                    <!-- Floating Services Badge -->
                    <div class="services-floating-badge">
                        <i class="fas fa-graduation-cap text-primary me-2"></i>
                        <span>End-to-End University Support</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =============================================
     OUR APPROACH — red hero background
     ============================================= -->
<section class="approach-hero-section cinematic-approach">
    <div class="approach-hero-overlay"></div>
    <div class="approach-hero-content">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h2 class="approach-hero-title text-white">OUR APPROACH</h2>
                    <p class="approach-hero-text text-white">
                        At Connect MyUni, we believe in a personalized, student-centered approach to education consulting. We take the time to understand your unique goals, strengths, and challenges, tailoring our services to meet your individual needs. Whether you're navigating the complexities of studying abroad or seeking professional development, we are with you every step of the way, offering expert guidance and practical support.
                    </p>
                    <p class="approach-hero-text text-white">
                        Our holistic approach combines academic counseling, training, and consulting with a commitment to empowering students and professionals to reach their full potential. By fostering strong partnerships with universities, businesses, and organizations worldwide, we ensure that our clients have access to the best opportunities available. At Connect MyUni, your success is our mission.
                    </p>
                    <a href="#contact" class="btn btn-approach-hero">TALK TO OUR CONSULTANTS</a>
                </div>

                <div class="col-lg-6">
                    <div class="approach-image-wrapper">
                        <!-- Ambient Glow FX -->
                        <div class="approach-backdrop-glow"></div>

                        <!-- Decorative outer pulsing circle orbit -->
                        <div class="approach-oval-orbit"></div>

                        <!-- Complete Circle Frame -->
                        <div class="approach-oval-container">
                            <img src="<?php echo $base_url; ?>frontend/assets/images/image3.png"
                                 alt="Our Consultants Team" class="approach-image">
                            <div class="approach-oval-gloss"></div>
                        </div>

                        <!-- Floating Advisory Badge -->
                        <div class="approach-floating-badge">
                            <i class="fas fa-award text-warning me-2"></i>
                            <span>Student-First Advisory</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =============================================
     LATEST NEWS & EVENTS SECTION
     ============================================= -->
<section id="events" class="events-section modern-events">
    <div class="container">
        <div class="events-header">
            <div class="events-badge">
                <i class="fas fa-calendar-alt"></i>
                <span>Upcoming Events</span>
            </div>
            <h2 class="events-title">LATEST NEWS & EVENTS</h2>
            <p class="events-subtitle">Stay updated with our latest educational events and news</p>
        </div>

        <div class="events-content">
            <?php if (!empty($events)): ?>
            <!-- CMS-driven event grid -->
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <div class="event-image-wrapper">
                        <img src="<?php echo htmlspecialchars($event['image'] ?: $base_url . 'frontend/assets/images/image1.png'); ?>"
                             alt="<?php echo htmlspecialchars($event['title']); ?>"
                             loading="lazy">
                        <?php if (!empty($event['category'])): ?>
                        <span class="event-badge <?php echo strtolower($event['category']); ?>"><?php echo htmlspecialchars(strtoupper($event['category'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="event-content">
                        <div class="event-meta">
                            <div class="event-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo !empty($event['date']) ? date('F j, Y', strtotime($event['date'])) : 'TBA'; ?>
                            </div>
                            <div class="event-type">
                                <i class="fas fa-tag"></i>
                                <?php echo !empty($event['category']) ? htmlspecialchars($event['category']) : 'General'; ?>
                            </div>
                        </div>
                        <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p class="event-description">
                            <?php echo htmlspecialchars(mb_strimwidth($event['description'] ?? '', 0, 120, '...')); ?>
                        </p>
                        <div class="event-actions">
                            <a href="<?php echo $base_url; ?>event-detail.php?id=<?php echo (int) $event['id']; ?>" class="btn-event-view">
                                View Details
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <!-- Static fallback events -->
            <div class="events-grid">
                <div class="event-card">
                    <div class="event-image-wrapper">
                        <img src="<?php echo $base_url; ?>frontend/assets/images/gallery/gallery1.png" alt="Navigating Global Education" loading="lazy">
                        <span class="event-badge webinar">WEBINAR</span>
                    </div>
                    <div class="event-content">
                        <div class="event-meta">
                            <div class="event-date">
                                <i class="fas fa-calendar"></i>
                                June 15, 2024
                            </div>
                            <div class="event-type">
                                <i class="fas fa-tag"></i>
                                Education
                            </div>
                        </div>
                        <h3 class="event-title">Navigating Global Education</h3>
                        <p class="event-description">Join us for an exclusive webinar on how to choose the right university abroad and prepare for your journey.</p>
                        <div class="event-actions">
                            <a href="<?php echo $base_url; ?>events.php" class="btn-event-view">
                                View Details
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="event-card">
                    <div class="event-image-wrapper">
                        <img src="<?php echo $base_url; ?>frontend/assets/images/gallery/gallery2.png" alt="IELTS Preparation Masterclass" loading="lazy">
                        <span class="event-badge workshop">WORKSHOP</span>
                    </div>
                    <div class="event-content">
                        <div class="event-meta">
                            <div class="event-date">
                                <i class="fas fa-calendar"></i>
                                July 10, 2024
                            </div>
                            <div class="event-type">
                                <i class="fas fa-tag"></i>
                                Training
                            </div>
                        </div>
                        <h3 class="event-title">IELTS Preparation Masterclass</h3>
                        <p class="event-description">Intensive workshop covering all aspects of IELTS exam preparation with expert trainers and proven strategies.</p>
                        <div class="event-actions">
                            <a href="<?php echo $base_url; ?>events.php" class="btn-event-view">
                                View Details
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="event-card">
                    <div class="event-image-wrapper">
                        <img src="<?php echo $base_url; ?>frontend/assets/images/gallery/gallery3.png" alt="Study Abroad Success Stories" loading="lazy">
                        <span class="event-badge video">VIDEO</span>
                    </div>
                    <div class="event-content">
                        <div class="event-meta">
                            <div class="event-date">
                                <i class="fas fa-calendar"></i>
                                July 20, 2024
                            </div>
                            <div class="event-type">
                                <i class="fas fa-tag"></i>
                                Success Stories
                            </div>
                        </div>
                        <h3 class="event-title">Study Abroad Success Stories</h3>
                        <p class="event-description">Watch inspiring stories from our successful students studying abroad and their transformational experiences.</p>
                        <div class="event-actions">
                            <a href="<?php echo $base_url; ?>events.php" class="btn-event-view">
                                View Details
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="events-footer">
            <a href="<?php echo $base_url; ?>events.php" class="btn btn-view-all">
                View All Events
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>


<!-- =============================================
     CONTACT SECTION
     ============================================= -->
<section id="contact" class="contact-section cinematic-contact-hub">
    <div class="container">
        <!-- Section Header -->
        <div class="contact-header text-center mb-5">
            <div class="contact-badge mb-2">
                <span class="contact-badge-dot"></span>
                <span>Get In Touch</span>
            </div>
            <h2 class="contact-title">CONNECT WITH OUR TEAM</h2>
            <p class="contact-subtitle">Speak directly with our senior educational consultants for personalized university admissions and visa guidance.</p>
        </div>

        <div class="contact-content-wrapper">
            <!-- Left: Interactive Map Showcase -->
            <div class="contact-map-card">
                <div class="map-header-bar">
                    <div class="map-header-left">
                        <div class="map-pin-pulse"></div>
                        <div>
                            <h4 class="map-hub-title">Abuja Headquarters</h4>
                            <p class="map-hub-sub">Lincoln College Campus, FCT Abuja</p>
                        </div>
                    </div>
                    <span class="map-status-pill"><i class="fas fa-clock me-1"></i> Mon – Sat: 8am – 6pm</span>
                </div>
                <div class="map-viewport">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.4531280450886!2d3.1656!3d9.0765!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x104ba5e78cffffff%3A0x8c8c8c8c8c8c8c8c!2s2%20Michika%20St%2C%20Garki%2C%20Abuja!5e0!3m2!1sen!2sng!4v1234567890"
                        width="100%" height="340"
                        style="border:0;"
                        allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="map-card-footer">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="map-address-hint"><i class="fas fa-directions me-1 text-primary"></i> Kurudu Azhata, along Nyanya-Karshi Road</span>
                        <a href="https://maps.google.com/?q=Lincoln+College+Kurudu+Abuja" target="_blank" rel="noopener" class="btn btn-sm btn-map-directions">
                            Get Directions <i class="fas fa-external-link-alt ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: Premium Structured Contact Cards Grid & Instant Channels -->
            <div class="contact-details-hub">
                <div class="contact-tiles-stack">
                    <!-- Address Tile -->
                    <div class="cmi-contact-tile address-tile">
                        <div class="tile-icon-box address">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="tile-body">
                            <span class="tile-label">Physical Campus</span>
                            <h4 class="tile-heading">Main Office Location</h4>
                            <p class="tile-value">Plot 100, Lincoln College of Science Management &amp; Technology, Nyanya-Karshi Rd, Kurudu Azhata, FCT Abuja</p>
                        </div>
                    </div>

                    <!-- Phone Tile -->
                    <a href="tel:+2348063325541" class="cmi-contact-tile phone-tile">
                        <div class="tile-icon-box phone">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="tile-body">
                            <span class="tile-label">Admissions Hotline</span>
                            <h4 class="tile-heading">+234 806 332 5541</h4>
                            <p class="tile-subtext">Direct voice call &amp; admissions consultation</p>
                        </div>
                        <div class="tile-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>

                    <!-- Email Tile -->
                    <a href="mailto:info@connectmyuni.net" class="cmi-contact-tile email-tile">
                        <div class="tile-icon-box email">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="tile-body">
                            <span class="tile-label">Official Inquiries</span>
                            <h4 class="tile-heading">info@connectmyuni.net</h4>
                            <p class="tile-subtext">Average response time: &lt; 2 hours</p>
                        </div>
                        <div class="tile-action-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                </div>

                <!-- Instant WhatsApp Live Chat Banner Card -->
                <div class="whatsapp-banner-card">
                    <div class="whatsapp-banner-content">
                        <div class="whatsapp-pulse-avatar">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="whatsapp-banner-text">
                            <h4 class="whatsapp-banner-title">Need Instant Answers?</h4>
                            <p class="whatsapp-banner-desc">Connect directly with an education advisor on WhatsApp for fast-track university guidance.</p>
                        </div>
                    </div>
                    <a href="https://wa.me/+639176923263" class="btn btn-whatsapp-cta" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp me-2"></i> Chat on WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vanilla JS Cinematic Motion Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return; // Disable motion
    }

    // Hero parallax and scroll
    const heroBgs = document.querySelectorAll('.cinematic-hero .hero-bg-wrapper img');
    const heroContent = document.querySelector('.cinematic-hero .hero-content');

    // About parallax
    const aboutTitle = document.querySelector('.cinematic-about .about-title');

    // Observers for reveals
    const observerOptions = { root: null, rootMargin: '0px', threshold: 0.15 };

    const revealElements = document.querySelectorAll('.service-card-item, .accordion-item, .event-card, .contact-info');
    revealElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
    });

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    revealElements.forEach(el => revealObserver.observe(el));

    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                const scrollY = window.scrollY;

                if (scrollY < window.innerHeight && heroContent) {
                    heroBgs.forEach(bg => {
                        bg.style.transform = `translateY(${scrollY * 0.3}px) scale(${1 + scrollY * 0.0002})`;
                    });
                    heroContent.style.transform = `translateY(${scrollY * 0.1}px)`;
                    heroContent.style.opacity = 1 - (scrollY / (window.innerHeight * 0.8));
                }

                if (aboutTitle) {
                    const rect = aboutTitle.getBoundingClientRect();
                    if (rect.top < window.innerHeight && rect.bottom > 0) {
                        const shift = (rect.top - window.innerHeight / 2) * -0.05;
                        aboutTitle.style.transform = `translateY(${shift}px)`;
                    }
                }

                ticking = false;
            });
            ticking = true;
        }
    });
});
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>