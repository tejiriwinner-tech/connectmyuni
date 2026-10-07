<?php
// SEO Meta Configuration
$page_title = 'Top Partner Universities & Study Destinations | Connect MyUni';
$meta_description = 'Explore top accredited universities across the United Kingdom, United States, Canada, Australia, and Europe. Find undergraduate and postgraduate programs, tuition fees, and scholarship opportunities.';
$meta_keywords = 'partner universities, study abroad destinations, universities in uk, universities in canada, universities in usa, university courses abroad, scholarship universities';

$origin = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'connectmyuni.com');

$schema_json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'CollectionPage',
    'name'     => 'Partner Universities & Institutions',
    'description' => $meta_description,
    'breadcrumb' => [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => $origin . '/'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Partner Universities',
                'item' => $origin . '/universities.php'
            ]
        ]
    ]
];

require_once __DIR__ . '/../components/header.php';

// ─────────────────────────────────────────────
// Partner Universities — from the CMS database
// ─────────────────────────────────────────────
use ConnectMyUni\Services\CountryService;
use ConnectMyUni\Services\UniversityService;

try {
    $uniService = new UniversityService();
    $allUniversities       = $uniService->getAll();
    $featuredUniversities  = $uniService->getFeatured();
} catch (\Throwable $e) {
    error_log('Universities load failed: ' . $e->getMessage());
    $allUniversities      = [];
    $featuredUniversities = [];
}

// Enhanced fallback to handle database structure
if (empty($allUniversities)) {
    $allUniversities = [
        [
            'name' => 'University of Oxford',
            'country_name' => 'United Kingdom',
            'flag_emoji' => '🇬🇧',
            'location' => 'Oxford, England',
            'description' => 'One of the world\'s leading research universities, offering world-class education across disciplines.',
            'is_featured' => true
        ],
        [
            'name' => 'University of Cambridge',
            'country_name' => 'United Kingdom',
            'flag_emoji' => '🇬🇧',
            'location' => 'Cambridge, England',
            'description' => 'A prestigious research university known for academic excellence and historic tradition.',
            'is_featured' => true
        ],
        [
            'name' => 'Harvard University',
            'country_name' => 'USA',
            'flag_emoji' => '🇺🇸',
            'location' => 'Cambridge, Massachusetts',
            'description' => 'Ivy League research university with global influence and distinguished alumni.',
            'is_featured' => true
        ],
        [
            'name' => 'Stanford University',
            'country_name' => 'USA',
            'flag_emoji' => '🇺🇸',
            'location' => 'Stanford, California',
            'description' => 'Leading research university known for innovation and entrepreneurship.',
            'is_featured' => false
        ],
        [
            'name' => 'University of Toronto',
            'country_name' => 'Canada',
            'flag_emoji' => '🇨🇦',
            'location' => 'Toronto, Ontario',
            'description' => 'Top-ranked Canadian university with diverse programs and research opportunities.',
            'is_featured' => true
        ],
        [
            'name' => 'University of British Columbia',
            'country_name' => 'Canada',
            'flag_emoji' => '🇨🇦',
            'location' => 'Vancouver, British Columbia',
            'description' => 'Premier research university with stunning campus and global reputation.',
            'is_featured' => false
        ],
        [
            'name' => 'University of Malaya',
            'country_name' => 'Malaysia',
            'flag_emoji' => '🇲🇾',
            'location' => 'Kuala Lumpur, Malaysia',
            'description' => 'Malaysia\'s oldest and most prestigious university with strong research programs.',
            'is_featured' => true
        ],
        [
            'name' => 'University of the Philippines',
            'country_name' => 'Philippines',
            'flag_emoji' => '🇵🇭',
            'location' => 'Diliman, Quezon City',
            'description' => 'The national university of the Philippines with excellence in education and research.',
            'is_featured' => true
        ],
        [
            'name' => 'University of Sydney',
            'country_name' => 'Australia',
            'flag_emoji' => '🇦🇺',
            'location' => 'Sydney, New South Wales',
            'description' => 'Australia\'s first university and a member of the prestigious Group of Eight.',
            'is_featured' => true
        ],
        [
            'name' => 'University of Melbourne',
            'country_name' => 'Australia',
            'flag_emoji' => '🇦🇺',
            'location' => 'Melbourne, Victoria',
            'description' => 'Leading Australian university known for research and teaching excellence.',
            'is_featured' => false
        ],
        [
            'name' => 'University of Lagos',
            'country_name' => 'Nigeria',
            'flag_emoji' => '🇳🇬',
            'location' => 'Lagos, Nigeria',
            'description' => 'Nigeria\'s premier university with strong academic programs and research output.',
            'is_featured' => true
        ],
        [
            'name' => 'University of Ibadan',
            'country_name' => 'Nigeria',
            'flag_emoji' => '🇳🇬',
            'location' => 'Ibadan, Oyo State',
            'description' => 'First and premier Nigerian university with excellent academic reputation.',
            'is_featured' => false
        ]
    ];
    
    // Re-populate featured universities from fallback data
    $featuredUniversities = array_filter($allUniversities, function($uni) {
        return !empty($uni['is_featured']);
    });
}

// Ensure universities have country_name and flag_emoji from database
foreach ($allUniversities as $key => $uni) {
    if (!isset($uni['country_name']) && isset($uni['country_id'])) {
        // Try to get country name from CountryService
        try {
            $countryService = new CountryService();
            $country = $countryService->getById($uni['country_id']);
            if ($country) {
                $allUniversities[$key]['country_name'] = $country['name'] ?? 'Unknown';
                $allUniversities[$key]['flag_emoji'] = $country['flag_emoji'] ?? '🌍';
            }
        } catch (\Throwable $e) {
            $allUniversities[$key]['country_name'] = 'Unknown';
            $allUniversities[$key]['flag_emoji'] = '🌍';
        }
    }
    // Ensure flag_emoji exists
    if (!isset($allUniversities[$key]['flag_emoji'])) {
        $allUniversities[$key]['flag_emoji'] = '🌍';
    }
    // Ensure country_name exists
    if (!isset($allUniversities[$key]['country_name'])) {
        $allUniversities[$key]['country_name'] = 'Unknown';
    }
}

// Group universities by country, preserving the DB sort order.
$partner_universities = [];
foreach ($allUniversities as $uni) {
    $country = (string) ($uni['country_name'] ?? 'Other');
    if (!isset($partner_universities[$country])) {
        $partner_universities[$country] = [
            'flag'         => CountryService::flagEmoji((string) ($uni['flag_emoji'] ?? '')),
            'universities' => [],
        ];
    }
    $partner_universities[$country]['universities'][] = $uni;
}

// Featured partner cards (driven by is_featured in the CMS).
$featured_partners = [];
foreach ($featuredUniversities as $i => $uni) {
    $abbr = '';
    // Reuse the official abbreviation from the name, e.g. "Nottingham Trent University (NTU)".
    if (preg_match('/\(([^)]+)\)/', (string) $uni['name'], $m)) {
        $abbr = trim($m[1]);
    }
    $featured_partners[] = [
        'number'   => sprintf('%02d', $i + 1),
        'name'     => (string) $uni['name'],
        'location' => (string) ($uni['location'] ?? ''),
        'desc'     => (string) ($uni['description'] ?? ''),
        'abbr'     => $abbr,
    ];
}
?>

<!-- ── Page Hero ──────────────────────────────────────────── -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-hero__title cmi-fade-up">Partner Universities</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?php echo $base_url; ?>index.php">Home</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Partner Universities</li>
            </ol>
        </nav>
    </div>
</section>

<!-- ── Intro Banner ───────────────────────────────────────── -->
<!-- <section class="partner-intro py-5">
    <div class="container text-center">
        <p class="partner-intro__quote">
            "Our project places education specialists in <strong>diverse universities abroad</strong>
            to enhance global learning experiences."
        </p>
        <p class="partner-intro__sub text-muted">
            Connect MyUni partners with leading universities across the world to give Nigerian
            students access to quality international education.
        </p>
    </div>
</section> -->

<!-- ── Featured Partners ──────────────────────────────────── -->
<?php if (!empty($featured_partners)): ?>
<section class="featured-partners-section cmi-section-enter">
    <div class="container">
        <div class="featured-header">
            <h2 class="featured-title cmi-fade-up">Featured Partners</h2>
            <p class="featured-subtitle cmi-fade-up">Our most prestigious university partnerships worldwide</p>
        </div>
        <div class="featured-grid">
            <?php foreach ($featured_partners as $fp): ?>
                <div class="featured-card cmi-fade-up">
                    <div class="featured-card-bg"></div>
                    <div class="featured-card-content">
                        <div class="featured-number"><?php echo htmlspecialchars($fp['number']); ?></div>
                        <?php if ($fp['abbr'] !== ''): ?>
                        <div class="featured-abbr"><?php echo htmlspecialchars($fp['abbr']); ?></div>
                        <?php endif; ?>
                        <h3 class="featured-name"><?php echo htmlspecialchars($fp['name']); ?></h3>
                        <?php if ($fp['location'] !== ''): ?>
                        <div class="featured-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($fp['location']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($fp['desc'] !== ''): ?>
                        <p class="featured-desc"><?php echo htmlspecialchars($fp['desc']); ?></p>
                        <?php endif; ?>
                        <div class="featured-cta">
                            <span class="featured-link">Learn More <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Full University Grid ──────────────────────────────── -->
<section class="university-grid-section cmi-section-enter">
    <div class="container">
        <div class="university-header">
            <h2 class="university-title cmi-fade-up">All Partner Universities</h2>
            <p class="university-subtitle cmi-fade-up">Explore our global network of partner institutions</p>
        </div>

        <?php if (empty($partner_universities)): ?>
            <div class="uni-empty text-center py-5">
                <p class="mb-0 text-muted">No partner universities are available yet. Please check back soon.</p>
            </div>
        <?php else: ?>
        <!-- Country filter pills -->
        <div class="country-filter-pills cmi-fade-up">
            <button class="filter-pill active" data-country="all">
                <span class="pill-icon">🌍</span>
                <span class="pill-text">All Countries</span>
                <span class="pill-count"><?php echo count($allUniversities); ?></span>
            </button>
            <?php foreach ($partner_universities as $country => $group): ?>
                <button class="filter-pill" data-country="<?php echo htmlspecialchars($country); ?>">
                    <span class="pill-icon"><?php echo $group['flag']; ?></span>
                    <span class="pill-text"><?php echo htmlspecialchars($country); ?></span>
                    <span class="pill-count"><?php echo count($group['universities']); ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- University Cards Grid -->
        <div class="university-cards-grid" id="universityGrid">
            <?php foreach ($partner_universities as $country => $group): ?>
                <div class="country-group" data-country="<?php echo htmlspecialchars($country); ?>">
                    <div class="country-header">
                        <span class="country-flag-large"><?php echo $group['flag']; ?></span>
                        <h3 class="country-name"><?php echo htmlspecialchars($country); ?></h3>
                        <span class="country-count"><?php echo count($group['universities']); ?> universities</span>
                    </div>
                    <div class="country-universities">
                        <?php foreach ($group['universities'] as $uni): ?>
                            <div class="university-card">
                                <div class="uni-card-content">
                                    <h4 class="uni-name"><?php echo htmlspecialchars((string) $uni['name']); ?></h4>
                                    <?php if (!empty($uni['location'])): ?>
                                    <div class="uni-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($uni['location']); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($uni['description'])): ?>
                                    <p class="uni-description"><?php echo htmlspecialchars($uni['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="uni-card-actions">
                                    <a href="#" class="uni-view-btn">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── CTA Section ────────────────────────────────────────── -->
<section class="partner-cta-section cmi-section-enter py-5 my-4">
    <div class="container">
        <div class="partner-cta text-center p-4 p-md-5">
            <h2 class="partner-cta__title cmi-fade-up">Ready to Start Your Study Abroad Journey?</h2>
            <p class="partner-cta__sub cmi-fade-up">
                Our experienced educational counsellors will match you with the best-fit university, navigate admission requirements, and guide your visa processing from start to finish.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3 mt-4 cmi-fade-up">
                <a href="<?php echo $base_url; ?>registration.php" class="btn btn-hero-cta px-4 py-3 d-inline-flex align-items-center gap-2 shadow">
                    <i class="fas fa-user-graduate"></i>
                    <span>Apply for Admission</span>
                </a>
                <a href="<?php echo $base_url; ?>contact.php" class="btn btn-about-link px-4 py-3 d-inline-flex align-items-center gap-2">
                    <i class="fas fa-headset"></i>
                    <span>Talk to a Counsellor</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ── Page-specific CSS ───────────────────────────────────── -->
<style>
    /* ── Hero ── */
    .page-hero {
        background: linear-gradient(135deg, #0F0E2E 0%, #1E1B4B 60%, #0F766E 100%) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 5rem 0 3.5rem !important;
        color: #FFFFFF !important;
    }

    .page-hero__title {
        font-family: var(--font-heading) !important;
        font-size: clamp(2.2rem, 4vw, 3.4rem);
        font-weight: 800;
        margin-bottom: 0.75rem;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        text-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
        letter-spacing: -0.02em;
    }

    /* ── Featured Partners Section ── */
    .featured-partners-section {
        padding: 4.5rem 0;
        background: linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%);
    }

    .featured-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .featured-title {
        font-family: var(--font-heading) !important;
        font-size: clamp(1.85rem, 3vw, 2.5rem);
        font-weight: 800;
        margin-bottom: 0.6rem;
        color: var(--color-primary);
        position: relative;
        display: inline-block;
        letter-spacing: -0.02em;
    }

    .featured-subtitle {
        font-size: 1.1rem;
        color: var(--text-secondary);
        margin-top: 0.5rem;
        max-width: 620px;
        margin-left: auto;
        margin-right: auto;
    }

    .featured-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .featured-card {
        position: relative;
        background: #FFFFFF;
        border-radius: var(--radius-xl);
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.85);
        box-shadow: var(--shadow-card);
        transition: transform var(--transition-spring), box-shadow var(--transition-normal), border-color var(--transition-normal);
        cursor: pointer;
    }

    .featured-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-secondary);
        transform: scaleX(0);
        transition: transform 0.35s ease;
        transform-origin: left;
    }

    .featured-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-card-hover);
        border-color: rgba(13, 148, 136, 0.4);
    }

    .featured-card:hover::before {
        transform: scaleX(1);
    }

    .featured-card-content {
        position: relative;
        padding: 2.25rem 2rem;
        z-index: 1;
    }

    .featured-number {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        font-family: var(--font-heading);
        font-size: 3rem;
        font-weight: 900;
        color: var(--color-primary);
        opacity: 0.08;
        line-height: 1;
    }

    .featured-abbr {
        display: inline-block;
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
        color: #FFFFFF;
        padding: 0.35rem 0.95rem;
        border-radius: 50px;
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 0.82rem;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        box-shadow: 0 2px 8px rgba(13, 148, 136, 0.25);
    }

    .featured-name {
        font-family: var(--font-heading) !important;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--color-primary);
        margin-bottom: 0.75rem;
        line-height: 1.35;
    }

    .featured-location {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-bottom: 1rem;
        font-weight: 500;
    }

    .featured-location i {
        color: var(--color-secondary);
    }

    .featured-desc {
        color: var(--text-secondary);
        line-height: 1.65;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
    }

    .featured-cta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .featured-link {
        color: var(--color-secondary);
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: gap var(--transition-fast), color var(--transition-fast);
    }

    .featured-card:hover .featured-link {
        gap: 0.75rem;
        color: var(--color-secondary-hover);
    }

    /* ── University Grid Section ── */
    .university-grid-section {
        padding: 5rem 0;
        background: #F8FAFC;
    }

    .university-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .university-title {
        font-family: var(--font-heading) !important;
        font-size: clamp(1.85rem, 3vw, 2.5rem);
        font-weight: 800;
        margin-bottom: 0.5rem;
        color: var(--color-primary);
        letter-spacing: -0.02em;
    }

    .university-subtitle {
        font-size: 1.1rem;
        color: var(--text-secondary);
        margin-top: 0.5rem;
    }

    /* Country Filter Pills */
    .country-filter-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: center;
        margin-bottom: 3.5rem;
        max-width: 1050px;
        margin-left: auto;
        margin-right: auto;
    }

    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.65rem 1.25rem;
        border: 1.5px solid #E2E8F0;
        border-radius: 50px;
        background: #FFFFFF;
        cursor: pointer;
        transition: all var(--transition-fast);
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-secondary);
        box-shadow: var(--shadow-sm);
    }

    .filter-pill:hover {
        border-color: var(--color-secondary);
        color: var(--color-secondary);
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(13, 148, 136, 0.15);
    }

    .filter-pill.active {
        background: var(--gradient-brand) !important;
        border-color: transparent !important;
        color: #FFFFFF !important;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.25);
        transform: translateY(-2px);
    }

    .pill-icon {
        font-size: 1.15rem;
        line-height: 1;
    }

    .pill-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.2rem 0.65rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .filter-pill:not(.active) .pill-count {
        background: #F1F5F9;
        color: var(--text-muted);
    }

    /* University Cards Grid */
    .university-cards-grid {
        display: flex;
        flex-direction: column;
        gap: 3.5rem;
    }

    .country-group {
        animation: fadeInUp 0.5s ease forwards;
    }

    .country-group.hidden {
        display: none;
    }

    .country-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.75rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #E2E8F0;
    }

    .country-flag-large {
        font-size: 2.25rem;
        line-height: 1;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }

    .country-name {
        font-family: var(--font-heading) !important;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--color-primary);
        margin: 0;
        letter-spacing: -0.01em;
    }

    .country-count {
        margin-left: auto;
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.15) 0%, rgba(30, 27, 75, 0.08) 100%);
        color: var(--color-secondary);
        border: 1px solid rgba(13, 148, 136, 0.25);
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-family: var(--font-heading);
        font-size: 0.85rem;
        font-weight: 700;
    }

    .country-universities {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 1.75rem;
    }

    .university-card {
        background: #FFFFFF !important;
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        border-radius: var(--radius-xl) !important;
        padding: 1.75rem !important;
        transition: transform var(--transition-spring), box-shadow var(--transition-normal), border-color var(--transition-normal) !important;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: var(--shadow-sm) !important;
    }

    .university-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: var(--gradient-secondary);
        transform: scaleX(0);
        transition: transform 0.3s ease;
        transform-origin: left;
    }

    .university-card:hover {
        transform: translateY(-6px) !important;
        box-shadow: var(--shadow-card-hover) !important;
        border-color: rgba(13, 148, 136, 0.35) !important;
    }

    .university-card:hover::before {
        transform: scaleX(1);
    }

    .uni-card-content {
        position: relative;
        z-index: 1;
    }

    .uni-name {
        font-family: var(--font-heading) !important;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--color-primary);
        margin-bottom: 0.65rem;
        line-height: 1.35;
    }

    .uni-location {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-muted);
        font-size: 0.85rem;
        margin-bottom: 0.85rem;
        font-weight: 500;
    }

    .uni-location i {
        color: var(--color-secondary);
    }

    .uni-description {
        color: var(--text-secondary);
        line-height: 1.6;
        font-size: 0.88rem;
        margin-bottom: 1.25rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .uni-card-actions {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #F1F5F9;
    }

    .uni-view-btn {
        color: var(--color-secondary);
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: gap var(--transition-fast), color var(--transition-fast);
    }

    .university-card:hover .uni-view-btn {
        gap: 0.75rem;
        color: var(--color-secondary-hover);
    }

    /* ── CTA Section ── */
    .partner-cta-section {
        background: transparent;
    }

    .partner-cta {
        background: linear-gradient(135deg, #0F0E2E 0%, #1E1B4B 60%, #0F766E 100%) !important;
        color: #FFFFFF !important;
        border-radius: var(--radius-2xl);
        box-shadow: var(--shadow-2xl);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .partner-cta__title {
        font-family: var(--font-heading) !important;
        font-size: clamp(1.85rem, 3.5vw, 2.75rem);
        font-weight: 800;
        color: #FFFFFF !important;
        margin-bottom: 0.85rem;
        letter-spacing: -0.02em;
        text-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
    }

    .partner-cta__sub {
        margin-bottom: 1.5rem;
        color: rgba(255, 255, 255, 0.88) !important;
        font-size: 1.12rem;
        line-height: 1.65;
        max-width: 680px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .featured-grid {
            grid-template-columns: 1fr;
        }
        
        .country-filter-pills {
            gap: 0.5rem;
        }
        
        .filter-pill {
            padding: 0.55rem 1rem;
            font-size: 0.85rem;
        }
        
        .country-universities {
            grid-template-columns: 1fr;
        }
        
        .country-header {
            flex-wrap: wrap;
        }
        
        .country-count {
            margin-left: 0;
            margin-top: 0.5rem;
        }
    }
</style>

<!-- ── Country Filter Script ──────────────────────────────── -->
<script>
    (function() {
        const pills = document.querySelectorAll('.filter-pill');
        const countryGroups = document.querySelectorAll('.country-group');

        pills.forEach(pill => {
            pill.addEventListener('click', function() {
                // Update active pill
                pills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                const selected = this.dataset.country;

                countryGroups.forEach(group => {
                    if (selected === 'all' || group.dataset.country === selected) {
                        group.classList.remove('hidden');
                        // Reset animation
                        group.style.animation = 'none';
                        group.offsetHeight; // Trigger reflow
                        group.style.animation = 'fadeInUp 0.6s ease forwards';
                    } else {
                        group.classList.add('hidden');
                    }
                });
            });
        });
    })();
</script>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
