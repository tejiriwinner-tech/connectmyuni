<?php
$page_title = 'Partner Universities';
require_once __DIR__ . '/../components/header.php';

// ─────────────────────────────────────────────
// Partner Universities — from the CMS database
// ─────────────────────────────────────────────
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

// ISO country code -> flag emoji (regional indicator symbols).
$isoToFlag = static function (?string $code): string {
    $code = strtoupper(trim((string) $code));
    if (strlen($code) !== 2 || !ctype_alpha($code)) {
        return '🌍';
    }
    return mb_chr(0x1F1E6 + ord($code[0]) - 0x41, 'UTF-8')
         . mb_chr(0x1F1E6 + ord($code[1]) - 0x41, 'UTF-8');
};

// Group universities by country, preserving the DB sort order.
$partner_universities = [];
foreach ($allUniversities as $uni) {
    $country = (string) ($uni['country_name'] ?? 'Other');
    if (!isset($partner_universities[$country])) {
        $partner_universities[$country] = [
            'flag'         => $isoToFlag((string) ($uni['flag_emoji'] ?? '')),
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
<section class="featured-partners py-5 bg-light cmi-section-enter">
    <div class="container">
        <h2 class="section-heading mb-4 cmi-fade-up">Featured Partners</h2>
        <div class="row g-4">
            <?php foreach ($featured_partners as $fp): ?>
                <div class="col-md-4">
                    <div class="fp-card h-100 cmi-fade-up">
                        <div class="fp-card__number">
                            <?php echo htmlspecialchars($fp['number']); ?>
                        </div>
                        <?php if ($fp['abbr'] !== ''): ?>
                        <div class="fp-card__abbr">
                            <?php echo htmlspecialchars($fp['abbr']); ?>
                        </div>
                        <?php endif; ?>
                        <h3 class="fp-card__name"><?php echo htmlspecialchars($fp['name']); ?></h3>
                        <?php if ($fp['location'] !== ''): ?>
                        <p class="fp-card__location text-muted small">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?php echo htmlspecialchars($fp['location']); ?>
                        </p>
                        <?php endif; ?>
                        <?php if ($fp['desc'] !== ''): ?>
                        <p class="fp-card__desc"><?php echo htmlspecialchars($fp['desc']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Quote strip -->
        <div class="quote-strip mt-5 cmi-fade-up">
            <i class="fas fa-quote-left quote-strip__icon"></i>
            <p class="quote-strip__text">
                Diverse student body enriches the educational experience and prepares
                graduates to thrive in a globalized world.
            </p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Full University Table ──────────────────────────────── -->
<section class="university-table-section py-5 cmi-section-enter">
    <div class="container">
        <h2 class="section-heading mb-4 cmi-fade-up">All Partner Universities</h2>

        <?php if (empty($partner_universities)): ?>
            <div class="uni-empty text-center py-5">
                <p class="mb-0 text-muted">No partner universities are available yet. Please check back soon.</p>
            </div>
        <?php else: ?>
        <!-- Country filter tabs -->
        <ul class="nav nav-tabs uni-tabs mb-4" id="countryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-country="all">All Countries</button>
            </li>
            <?php foreach (array_keys($partner_universities) as $country): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-country="<?php echo htmlspecialchars($country); ?>">
                        <?php echo htmlspecialchars($country); ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Table -->
        <div class="table-responsive uni-table-wrap">
            <table class="table uni-table">
                <thead>
                    <tr>
                        <th scope="col" style="width:220px">Country</th>
                        <th scope="col">Universities</th>
                    </tr>
                </thead>
                <tbody id="universityTableBody">
                    <?php foreach ($partner_universities as $country => $group): ?>
                        <tr class="uni-row" data-country="<?php echo htmlspecialchars($country); ?>">
                            <td class="uni-row__country">
                                <span class="country-flag me-2"><?php echo $group['flag']; ?></span>
                                <?php echo htmlspecialchars($country); ?>
                            </td>
                            <td>
                                <ul class="uni-list mb-0">
                                    <?php foreach ($group['universities'] as $uni): ?>
                                        <li><?php echo htmlspecialchars((string) $uni['name']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── CTA Section ────────────────────────────────────────── -->
<section class="partner-cta py-5 cmi-section-enter">
    <div class="container text-center">
        <h2 class="partner-cta__title cmi-fade-up">Ready to Study Abroad?</h2>
        <p class="partner-cta__sub text-muted cmi-fade-up">
            Our counsellors will match you with the best-fit university from our partner network.
        </p>
        <a href="<?php echo $base_url; ?>registration.php" class="btn btn-primary btn-lg me-2">
            Register Now
        </a>
        <a href="<?php echo $base_url; ?>contact.php" class="btn btn-outline-secondary btn-lg">
            Contact Us
        </a>
    </div>
</section>

<!-- ── Page-specific CSS ───────────────────────────────────── -->
<style>
    /* ── Hero ── */
    .page-hero {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 2.5rem 0 1.5rem;
    }

    .page-hero__title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: .5rem;
        color: var(--primary-color);
    }

    .breadcrumb-item a {
        color: #6c757d;
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: var(--primary-color);
    }

    .breadcrumb-item+.breadcrumb-item::before {
        color: #adb5bd;
    }

    /* ── Intro ── */
    .partner-intro__quote {
        font-size: 1.25rem;
        font-style: italic;
        color: var(--primary-color);
        max-width: 700px;
        margin: 0 auto 1rem;
    }

    .partner-intro__sub {
        max-width: 640px;
        margin: 0 auto;
    }

    /* ── Section heading ── */
    .section-heading {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        position: relative;
        padding-bottom: .75rem;
    }

    .section-heading::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 48px;
        height: 3px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    /* ── Featured Partner Cards ── */
    .fp-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1.75rem;
        position: relative;
        transition: box-shadow .25s, transform .25s;
    }

    .fp-card:hover {
        box-shadow: 0 8px 28px rgba(26, 86, 219, .12);
        transform: translateY(-4px);
    }

    .fp-card__number {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: .75rem;
        opacity: .12;
        position: absolute;
        top: 1.25rem;
        right: 1.5rem;
    }

    .fp-card__abbr {
        display: inline-block;
        border: 2px solid;
        border-radius: 6px;
        padding: .25rem .75rem;
        font-weight: 700;
        font-size: .95rem;
        margin-bottom: .75rem;
    }

    .fp-card__name {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: .25rem;
    }

    .fp-card__desc {
        font-size: .9rem;
        color: #555;
        line-height: 1.6;
    }

    /* ── Quote strip ── */
    .quote-strip {
        background: var(--primary-color);
        border-radius: 12px;
        padding: 2rem 2.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        color: #fff;
    }

    .quote-strip__icon {
        font-size: 2rem;
        opacity: .6;
        flex-shrink: 0;
        margin-top: .15rem;
    }

    .quote-strip__text {
        font-size: 1.1rem;
        font-style: italic;
        margin: 0;
        line-height: 1.7;
    }

    /* ── Country filter tabs ── */
    .uni-tabs {
        border-bottom: 2px solid #dee2e6;
        flex-wrap: wrap;
        gap: .25rem;
    }

    .uni-tabs .nav-link {
        border: 1px solid transparent;
        border-radius: 6px 6px 0 0;
        color: #555;
        font-size: .875rem;
        padding: .45rem .9rem;
        cursor: pointer;
        background: none;
        transition: background .2s, color .2s;
    }

    .uni-tabs .nav-link:hover {
        background: var(--primary-light);
        color: var(--primary-color);
    }

    .uni-tabs .nav-link.active {
        background: #fff;
        color: var(--primary-color);
        border-color: #dee2e6 #dee2e6 #fff;
        font-weight: 600;
    }

    /* ── University table ── */
    .uni-table-wrap {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        overflow: hidden;
    }

    .uni-table {
        margin-bottom: 0;
    }

    .uni-table thead th {
        background: var(--primary-light);
        font-weight: 600;
        color: var(--primary-color);
        font-size: .9rem;
        padding: .9rem 1.25rem;
        border-bottom: 2px solid #c7d8f8;
    }

    .uni-row td {
        padding: 1rem 1.25rem;
        vertical-align: top;
        border-color: #e9ecef;
    }

    .uni-row__country {
        font-weight: 600;
        color: var(--primary-color);
        font-size: .95rem;
        white-space: nowrap;
    }

    .country-flag {
        font-size: 1.2rem;
    }

    /* university list */
    .uni-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .uni-list li {
        padding: .25rem 0;
        font-size: .9rem;
        color: #444;
        display: flex;
        align-items: baseline;
        gap: .5rem;
    }

    .uni-list li::before {
        content: '•';
        color: var(--primary-color);
        font-size: 1rem;
        flex-shrink: 0;
    }

    /* hidden rows */
    .uni-row.hidden {
        display: none;
    }

    /* ── CTA ── */
    .partner-cta {
        background: #f8f9fa;
    }

    .partner-cta__title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: .5rem;
    }

    .partner-cta__sub {
        margin-bottom: 1.5rem;
    }
</style>

<!-- ── Country Filter Script ──────────────────────────────── -->
<script>
    (function() {
        const tabs = document.querySelectorAll('.uni-tabs .nav-link');
        const rows = document.querySelectorAll('.uni-row');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const selected = this.dataset.country;

                rows.forEach(row => {
                    if (selected === 'all' || row.dataset.country === selected) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });
        });
    })();
</script>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
