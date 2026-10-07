<?php
// ─────────────────────────────────────────────
// Services — from the CMS database with fallback
// ─────────────────────────────────────────────
require_once __DIR__ . '/../../backend/bootstrap.php';
use ConnectMyUni\Services\ServiceService;

$services = [];
try {
    $services = (new ServiceService())->getAllActive();
} catch (\Throwable $e) {
    error_log('Services load failed: ' . $e->getMessage());
    $services = [];
}

// SEO Meta Configuration
$page_title = 'Our Services | University Admissions, Visa Processing & Scholarships';
$meta_description = 'Comprehensive study abroad services: university application guidance, student visa filing, scholarship placement, IELTS/TOEFL advice, and pre-departure briefings.';
$meta_keywords = 'study abroad services, university admission help, student visa processing nigeria, overseas scholarship assistance, pre departure briefing';

$origin = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'connectmyuni.com');

$schema_json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'ItemList',
    'name'     => 'Connect MyUni Overseas Education Services',
    'description' => $meta_description,
    'itemListElement' => [
        [
            '@type' => 'Service',
            'position' => 1,
            'name' => 'University Placement & Admission Guidance',
            'provider' => ['@type' => 'EducationalOrganization', 'name' => 'Connect MyUni']
        ],
        [
            '@type' => 'Service',
            'position' => 2,
            'name' => 'Student Visa Application Counseling',
            'provider' => ['@type' => 'EducationalOrganization', 'name' => 'Connect MyUni']
        ],
        [
            '@type' => 'Service',
            'position' => 3,
            'name' => 'Scholarship & Financial Aid Sourcing',
            'provider' => ['@type' => 'EducationalOrganization', 'name' => 'Connect MyUni']
        ],
        [
            '@type' => 'Service',
            'position' => 4,
            'name' => 'Pre-Departure Briefing & Airport Accommodation',
            'provider' => ['@type' => 'EducationalOrganization', 'name' => 'Connect MyUni']
        ]
    ]
];

// Fallback services if database is empty or connection fails
if (empty($services)) {
    $services = [
        [
            'title' => 'University Placement Services',
            'description' => 'We connect qualified students with world-class universities and colleges, ensuring they find the perfect institution that matches their academic goals and aspirations. Our expert team guides you through the entire application process.',
            'icon_class' => 'fa-university'
        ],
        [
            'title' => 'Visa Assistance & Processing',
            'description' => 'Navigate the complex visa application process with our expert guidance. We provide comprehensive support for student visas, work permits, and immigration documentation for various countries.',
            'icon_class' => 'fa-passport'
        ],
        [
            'title' => 'Test Preparation Services',
            'description' => 'Expert preparation for standardized tests including IELTS, TOEFL, SAT, GRE, and GMAT. Our experienced instructors provide proven strategies and practice materials to help you achieve your target scores.',
            'icon_class' => 'fa-graduation-cap'
        ],
        [
            'title' => 'Language Training Programs',
            'description' => 'Intensive English and other language programs designed to enhance communication skills and academic proficiency for international study. Improve your language skills for academic success.',
            'icon_class' => 'fa-language'
        ],
        [
            'title' => 'Scholarship & Financial Aid Assistance',
            'description' => 'Expert guidance in identifying and applying for scholarships, grants, and financial aid opportunities. We help make education affordable and accessible for deserving students.',
            'icon_class' => 'fa-hand-holding-usd'
        ],
        [
            'title' => 'Career Counseling & Guidance',
            'description' => 'Personalized counseling to guide your academic journey, from university selection to career planning and professional development. We help you make informed decisions about your future.',
            'icon_class' => 'fa-user-tie'
        ],
        [
            'title' => 'Pre-Departure Orientation',
            'description' => 'Comprehensive preparation programs to help students adapt to their new environment. Includes cultural orientation, accommodation assistance, and practical guidance for studying abroad.',
            'icon_class' => 'fa-plane-departure'
        ],
        [
            'title' => 'Post-Arrival Support Services',
            'description' => 'Continuous support after arrival including accommodation assistance, airport pickup, banking setup, local registration, and ongoing academic and personal support throughout your study period.',
            'icon_class' => 'fa-headset'
        ]
    ];
}

include __DIR__ . '/../components/header.php';
?>

<!-- Page Hero -->
<section class="page-hero cmi-section-enter">
    <div class="container">
        <div class="hero-content text-center">
            <div class="hero-badge">
                <i class="fas fa-compass"></i>
                <span>Comprehensive Advisory</span>
            </div>
            <h1 class="page-hero__title cmi-fade-up">Our Premium Services</h1>
            <p class="hero-subtitle cmi-fade-up">
                From profile assessment and university selection to visa interview prep and pre-departure briefings, we deliver end-to-end guidance for your academic success.
            </p>
            <div class="hero-breadcrumb">
                <a href="<?php echo $base_url; ?>index.php">Home</a>
                <span class="mx-2">/</span>
                <span>Services</span>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-page-section cmi-section-enter">
    <div class="container">
        <div class="services-content-wrapper">
            <!-- Left Services List -->
            <div class="services-left">
                <h2 class="services-page-title cmi-fade-up">Our Services</h2>

                <div class="services-accordion">
                    <?php foreach ($services as $svc): ?>
                    <div class="service-accordion-item">
                        <button class="service-accordion-header" onclick="toggleAccordion(this)">
                            <span>
                                <?php if (!empty($svc['icon_class'])): ?>
                                    <i class="fas <?php echo htmlspecialchars($svc['icon_class']); ?> me-2"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($svc['title'] ?? ''); ?>
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="service-accordion-body">
                            <p><?php echo htmlspecialchars($svc['description'] ?? ''); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Services Image -->
            <div class="services-right cmi-slide-in-right">
                <div class="services-image-container">
                    <img src="<?php echo $base_url; ?>frontend/assets/images/background1.png" alt="Our Services">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Get In Touch Section -->
<section class="get-in-touch-section cmi-section-enter">
    <div class="container">
        <h2 class="get-in-touch-title cmi-fade-up">Get In Touch With Us</h2>
        <div class="contact-details">
            <div class="contact-detail-item cmi-fade-up">
                <i class="fas fa-phone"></i>
                <span>Phone: +234 806 332 5541</span>
            </div>
            <div class="contact-detail-item cmi-fade-up">
                <i class="fas fa-envelope"></i>
                <span>Email: info@connectmyuni.org</span>
            </div>
            <div class="contact-detail-item cmi-fade-up">
                <i class="fas fa-globe"></i>
                <span>Visit: www.connectmyuni.org</span>
            </div>
            <div class="contact-detail-item cmi-fade-up">
                <i class="fas fa-map-marker-alt"></i>
                <span>Office: Suite B3, 1st Floor, 2 Michika St, Abuja, Nigeria</span>
            </div>
        </div>
    </div>
</section>

<script>
    function toggleAccordion(button) {
        const item = button.parentElement;
        const body = item.querySelector('.service-accordion-body');
        const isActive = item.classList.contains('active');

        // Close all accordion items
        document.querySelectorAll('.service-accordion-item').forEach(el => {
            el.classList.remove('active');
        });

        // Open clicked item if it wasn't active
        if (!isActive) {
            item.classList.add('active');
        }
    }
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
