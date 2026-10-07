<?php
// SEO Meta Configuration
$page_title = 'Student Success Gallery & Campus Moments | Connect MyUni';
$meta_description = 'Browse photos of our student visa success stories, university campus departures, graduation ceremonies, and annual overseas education fairs.';
$meta_keywords = 'student visa success stories, study abroad photos, campus moments, graduation photos, university fair gallery';

$origin = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'connectmyuni.com');

$schema_json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'CollectionPage',
    'name'     => 'Connect MyUni Student Gallery & Moments',
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
                'name' => 'Gallery',
                'item' => $origin . '/gallery.php'
            ]
        ]
    ]
];

require_once __DIR__ . '/../components/header.php';

// ─────────────────────────────────────────────────────────────
// Gallery Images
// Uses images from frontend/assets/images/gallery/ folder
// ─────────────────────────────────────────────────────────────
$gallery_items = [
    [
        'file'     => $base_url . 'frontend/assets/images/gallery/gallery1.png',
        'caption'  => 'Students collaborating during campus orientation',
        'category' => 'campus',
        'label'    => 'Campus Life',
    ],
    [
        'file'     => $base_url . 'frontend/assets/images/gallery/gallery2.png',
        'caption'  => 'Connect MyUni team at a student outreach event',
        'category' => 'events',
        'label'    => 'Events',
    ],
    [
        'file'     => $base_url . 'frontend/assets/images/gallery/gallery3.png',
        'caption'  => 'Students at the international departures',
        'category' => 'airport',
        'label'    => 'Departure',
    ],
    [
        'file'     => $base_url . 'frontend/assets/images/gallery/gallery4.png',
        'caption'  => 'Our student representative on campus',
        'category' => 'campus',
        'label'    => 'Campus Life',
    ],
    [
        'file'     => $base_url . 'frontend/assets/images/gallery/gallery5.png',
        'caption'  => 'Graduation ceremony celebration',
        'category' => 'graduation',
        'label'    => 'Graduation',
    ],
    [
        'file'     => $base_url . 'frontend/assets/images/gallery/gallery6.png',
        'caption'  => 'Students on their international journey',
        'category' => 'events',
        'label'    => 'Events',
    ],
    [
        'file'     => $base_url . 'frontend/assets/images/gallery/gallery7.png',
        'caption'  => 'A lively classroom session at university',
        'category' => 'campus',
        'label'    => 'Campus Life',
    ],
    [
        'file'     => $base_url . 'frontend/assets/images/gallery/gallery8.png',
        'caption'  => 'Connect MyUni students together on campus',
        'category' => 'campus',
        'label'    => 'Campus Life',
    ],
];

// ── DB-driven: add published gallery_images to hardcoded ones ──
$galleryLabelMap = [
    'campus'     => 'Campus Life',
    'airport'    => 'Departure',
    'graduation' => 'Graduation',
    'events'     => 'Events & Team',
];

$dbGallery = [];
try {
    foreach ((new \ConnectMyUni\Repositories\GalleryRepository())->getActive(60) as $row) {
        $resolved = \ConnectMyUni\Helpers\MediaResolver::url($row['image_path'] ?? '');
        if ($resolved === '') {
            continue;
        }
        $dbGallery[] = [
            'file'     => $resolved,
            'caption'  => $row['alt_text'] ?? $row['title'] ?? '',
            'category' => $row['category'] ?? 'general',
            'label'    => $galleryLabelMap[$row['category'] ?? ''] ?? ucfirst($row['category'] ?? 'General'),
        ];
    }
} catch (\Throwable $e) {
    $dbGallery = [];
}
// Merge database images with hardcoded images (keep both)
if (!empty($dbGallery)) {
    $gallery_items = array_merge($gallery_items, $dbGallery);
}

$categories = [
    'all'        => 'All',
    'campus'     => 'Campus Life',
    'airport'    => 'Departure',
    'graduation' => 'Graduation',
    'events'     => 'Events & Team',
];
?>

<!-- ── Gallery Hero Section ──────────────────────────────── -->
<section class="gallery-hero cmi-section-enter">
    <div class="hero-background"></div>
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-images"></i>
                <span>Photo Gallery</span>
            </div>
            <h1 class="gallery-hero__title cmi-fade-up">Gallery</h1>
            <p class="gallery-hero__subtitle cmi-fade-up">
                Explore inspiring moments from our students' journeys around the world
            </p>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($gallery_items); ?></div>
                    <div class="stat-label">Photos</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($categories) - 1; ?></div>
                    <div class="stat-label">Categories</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Filter Bar ─────────────────────────────────────────── -->
<section class="filter-bar cmi-fade-up">
    <div class="container">
        <div class="filter-bar__inner">
            <div class="filter-label">
                <i class="fas fa-filter"></i>
                <span>Filter by:</span>
            </div>
            <div class="filter-buttons">
                <?php foreach ($categories as $key => $label): ?>
                    <button
                        class="filter-btn <?php echo $key === 'all' ? 'filter-btn--active' : ''; ?>"
                        data-filter="<?php echo $key; ?>">
                        <span class="btn-icon">
                            <?php if ($key === 'all'): ?>
                                <i class="fas fa-th"></i>
                            <?php elseif ($key === 'campus'): ?>
                                <i class="fas fa-university"></i>
                            <?php elseif ($key === 'airport'): ?>
                                <i class="fas fa-plane-departure"></i>
                            <?php elseif ($key === 'graduation'): ?>
                                <i class="fas fa-graduation-cap"></i>
                            <?php elseif ($key === 'events'): ?>
                                <i class="fas fa-calendar-alt"></i>
                            <?php endif; ?>
                        </span>
                        <span class="btn-text"><?php echo htmlspecialchars($label); ?></span>
                        <span class="btn-count">
                            <?php 
                            $count = $key === 'all' 
                                ? count($gallery_items) 
                                : count(array_filter($gallery_items, fn($item) => $item['category'] === $key));
                            echo $count;
                            ?>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ── Responsive Gallery Grid ────────────────────────────── -->
<section class="gallery-section cmi-gallery-cinematic">
    <div class="container gallery-container">
        <div class="gallery-grid" id="galleryGrid">
            <?php foreach ($gallery_items as $i => $item): ?>
                <div
                    class="gallery-item"
                    data-category="<?php echo htmlspecialchars($item['category']); ?>"
                    data-index="<?php echo $i; ?>"
                    style="animation-delay: <?php echo min($i * 0.05, 0.5); ?>s">
                    <div class="gallery-item__inner">
                        <div class="gallery-item__image-wrapper">
                            <img
                                src="<?php echo htmlspecialchars($item['file']); ?>"
                                alt="<?php echo htmlspecialchars($item['caption']); ?>"
                                class="gallery-item__img"
                                loading="lazy"
                                onerror="this.closest('.gallery-item').classList.add('gallery-item--placeholder')">
                            <div class="gallery-item__overlay">
                                <div class="overlay-content">
                                    <span class="gallery-item__label">
                                        <?php echo htmlspecialchars($item['label']); ?>
                                    </span>
                                    <p class="gallery-item__caption">
                                        <?php echo htmlspecialchars($item['caption']); ?>
                                    </p>
                                    <button class="gallery-item__zoom" aria-label="View full image">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty state -->
        <div class="gallery-empty" id="galleryEmpty" style="display:none">
            <div class="gallery-empty__icon">
                <i class="fas fa-images"></i>
            </div>
            <h3>No photos in this category yet.</h3>
            <p>Check back later for more amazing moments!</p>
        </div>
    </div>
</section>

<!-- ── Lightbox ───────────────────────────────────────────── -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Image viewer">
    <button class="lightbox__close" id="lightboxClose" aria-label="Close">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M18 6L6 18M6 6l12 12" />
        </svg>
    </button>
    <button class="lightbox__nav lightbox__nav--prev" id="lightboxPrev" aria-label="Previous">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </button>
    <button class="lightbox__nav lightbox__nav--next" id="lightboxNext" aria-label="Next">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M9 18l6-6-6-6" />
        </svg>
    </button>
    <div class="lightbox__stage">
        <img src="" alt="" class="lightbox__img" id="lightboxImg">
        <div class="lightbox__meta">
            <span class="lightbox__tag" id="lightboxTag"></span>
            <p class="lightbox__caption" id="lightboxCaption"></p>
        </div>
    </div>
    <div class="lightbox__counter" id="lightboxCounter"></div>
</div>
<div class="lightbox__backdrop" id="lightboxBackdrop"></div>

<!-- ── CTA Strip ──────────────────────────────────────────── -->
<section class="gallery-cta">
    <div class="container">
        <div class="gallery-cta__inner">
            <div>
                <h2 class="gallery-cta__title">Ready to write your own story?</h2>
                <p class="gallery-cta__sub">Join hundreds of students who've made the leap with Connect MyUni.</p>
            </div>
            <div class="gallery-cta__actions">
                <a href="<?php echo $base_url; ?>registration.php" class="btn-cta btn-cta--primary">
                    Register Now
                </a>
                <a href="<?php echo $base_url; ?>contact.php" class="btn-cta btn-cta--ghost">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ── Gallery CSS ────────────────────────────────────────── -->
<style>
    /* ── Local tokens ── */
    :root {
        --primary-gradient: linear-gradient(135deg, #1E1B4B 0%, #0D9488 100%);
        --accent-gradient: linear-gradient(135deg, #0F766E 0%, #F59E0B 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.07);
        --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.15);
        --border-radius: 20px;
    }

    /* ── Hero Section ── */
    /* ── Hero Section ── */
    .gallery-hero {
        position: relative;
        padding: 5rem 0 4rem;
        overflow: hidden;
        background: linear-gradient(135deg, #0F0E2E 0%, #1E1B4B 60%, #0F766E 100%) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: #FFFFFF !important;
    }

    .hero-background, .hero-overlay {
        display: none !important;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
        max-width: 800px;
        margin: 0 auto;
    }

    .hero-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.12) !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        color: var(--color-accent) !important;
        padding: 0.45rem 1.1rem;
        border-radius: 50px;
        font-family: var(--font-heading);
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .gallery-hero__title {
        font-size: clamp(2.2rem, 4vw, 3.25rem);
        font-weight: 800;
        margin-bottom: 0.75rem;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        text-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
        line-height: 1.15;
    }

    .gallery-hero__subtitle {
        font-size: 1.15rem;
        color: rgba(255, 255, 255, 0.9) !important;
        max-width: 650px;
        margin: 0 auto 1.75rem;
        line-height: 1.6;
    }

    .hero-stats {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 2.5rem !important;
        margin-top: 0.5rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--color-accent) !important;
        -webkit-text-fill-color: var(--color-accent) !important;
        line-height: 1;
        margin-bottom: 0.3rem;
    }

    .stat-label {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.85) !important;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-divider {
        width: 1px;
        height: 36px;
        background: rgba(255, 255, 255, 0.25) !important;
    }

    /* ── Filter Bar ── */
    .filter-bar {
        position: sticky;
        top: 70px;
        z-index: 100;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem 0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .filter-bar__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
    }

    .filter-label i {
        color: #2563eb;
    }

    .filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .filter-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 2px solid #e9ecef;
        background: white;
        border-radius: 50px;
        padding: 0.6rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: rgba(37, 99, 235, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
    }

    .filter-btn--active {
        background: var(--primary-gradient);
        border-color: transparent;
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
    }

    .btn-icon {
        font-size: 1rem;
    }

    .btn-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.15rem 0.5rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .filter-btn:not(.filter-btn--active) .btn-count {
        background: #f1f5f9;
        color: #64748b;
    }

    /* ── Gallery Section ── */
    .gallery-section {
        padding: 4rem 0;
        background: white;
    }

    .gallery-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Responsive Grid Layout - Auto-adjusting based on content */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        min-height: 400px;
    }

    @media (max-width: 1200px) {
        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
        }
    }

    @media (max-width: 768px) {
        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
    }

    @media (max-width: 480px) {
        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }
    }

    /* Gallery Item */
    .gallery-item {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeUp 0.6s ease forwards;
    }

    @keyframes fadeUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .gallery-item--hidden {
        display: none;
    }

    .gallery-item__inner {
        position: relative;
        border-radius: var(--border-radius);
        overflow: hidden;
        background: #f1f5f9;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }

    .gallery-item:hover .gallery-item__inner {
        box-shadow: var(--shadow-md);
        transform: translateY(-4px);
    }

    .gallery-item__image-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: var(--border-radius);
        aspect-ratio: 4/3;
    }

    .gallery-item__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .gallery-item:hover .gallery-item__img {
        transform: scale(1.1);
    }

    /* Overlay */
    .gallery-item__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(37, 99, 235, 0.9) 0%, transparent 60%);
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: flex-end;
        padding: 1.5rem;
    }

    .gallery-item:hover .gallery-item__overlay {
        opacity: 1;
    }

    .overlay-content {
        width: 100%;
        transform: translateY(20px);
        transition: transform 0.3s ease;
    }

    .gallery-item:hover .overlay-content {
        transform: translateY(0);
    }

    .gallery-item__label {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        margin-bottom: 0.5rem;
    }

    .gallery-item__caption {
        color: rgba(255, 255, 255, 0.95);
        font-size: 0.9rem;
        line-height: 1.4;
        margin: 0 0 0.75rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .gallery-item__zoom {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0;
        margin-left: auto;
    }

    .gallery-item__zoom:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: scale(1.1);
    }

    /* Placeholder when image missing */
    .gallery-item--placeholder .gallery-item__img {
        opacity: 0;
    }

    .gallery-item--placeholder .gallery-item__image-wrapper {
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    }

    /* Empty state */
    .gallery-empty {
        text-align: center;
        padding: 5rem 0;
        color: #64748b;
    }

    .gallery-empty__icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .gallery-empty h3 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: #1e293b;
    }

    .gallery-empty p {
        font-size: 1rem;
        color: #64748b;
    }

    /* ── Lightbox ── */
    .lightbox__backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.95);
        z-index: 1050;
        backdrop-filter: blur(8px);
    }

    .lightbox {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1051;
        align-items: center;
        justify-content: center;
    }

    .lightbox--open,
    .lightbox--open+* {
        display: flex;
    }

    .lightbox--open~.lightbox__backdrop {
        display: block;
    }

    .lightbox__stage {
        max-width: min(95vw, 1000px);
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .lightbox__img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 12px;
        box-shadow: 0 25px 100px rgba(0, 0, 0, 0.5);
        display: block;
    }

    .lightbox__meta {
        margin-top: 1.5rem;
        text-align: center;
        max-width: 600px;
    }

    .lightbox__tag {
        display: inline-block;
        background: var(--primary-gradient);
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        margin-bottom: 0.5rem;
    }

    .lightbox__caption {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1rem;
        margin: 0;
        line-height: 1.5;
    }

    .lightbox__counter {
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.9rem;
        letter-spacing: 0.1em;
        background: rgba(0, 0, 0, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 50px;
    }

    .lightbox__close {
        position: fixed;
        top: 1.5rem;
        right: 2rem;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .lightbox__close:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: rotate(90deg);
    }

    .lightbox__nav {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 50%;
        width: 55px;
        height: 55px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .lightbox__nav:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-50%) scale(1.1);
    }

    .lightbox__nav--prev {
        left: 2rem;
    }

    .lightbox__nav--next {
        right: 2rem;
    }

    /* ── CTA Section ── */
    .gallery-cta {
        background: var(--primary-gradient);
        padding: 4rem 0;
        color: white;
    }

    .gallery-cta__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .gallery-cta__title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .gallery-cta__sub {
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
        font-size: 1rem;
    }

    .gallery-cta__actions {
        display: flex;
        gap: 1rem;
        flex-shrink: 0;
    }

    .btn-cta {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.85rem 2rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-cta--primary {
        background: white;
        color: #2563eb;
    }

    .btn-cta--primary:hover {
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .btn-cta--ghost {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.5);
    }

    .btn-cta--ghost:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .gallery-hero {
            padding: 4rem 0 3rem;
        }

        .hero-stats {
            flex-direction: column;
            gap: 1rem;
        }

        .stat-divider {
            width: 40px;
            height: 1px;
        }

        .filter-bar__inner {
            flex-direction: column;
            align-items: flex-start;
        }

        .filter-buttons {
            width: 100%;
        }

        .filter-btn {
            flex: 1;
            justify-content: center;
        }

        .gallery-cta__inner {
            flex-direction: column;
            text-align: center;
        }

        .gallery-cta__actions {
            justify-content: center;
            width: 100%;
        }

        .btn-cta {
            flex: 1;
            justify-content: center;
        }

        .lightbox__close {
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
        }

        .lightbox__nav {
            width: 45px;
            height: 45px;
        }

        .lightbox__nav--prev {
            left: 1rem;
        }

        .lightbox__nav--next {
            right: 1rem;
        }
    }

    @media (max-width: 480px) {
        .gallery-hero {
            padding: 3rem 0 2rem;
        }

        .gallery-hero__title {
            font-size: 2rem;
        }

        .gallery-hero__subtitle {
            font-size: 1rem;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .filter-buttons {
            gap: 0.5rem;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
    }
</style>

<!-- ── Gallery JS ─────────────────────────────────────────── -->
<script>
    (function() {
        // ── Data ──────────────────────────────────────────────
        const items = document.querySelectorAll('.gallery-item');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const emptyState = document.getElementById('galleryEmpty');

        // ── Filter ────────────────────────────────────────────
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('filter-btn--active'));
                this.classList.add('filter-btn--active');

                const filter = this.dataset.filter;
                let visible = 0;

                items.forEach((item, i) => {
                    const match = filter === 'all' || item.dataset.category === filter;
                    item.classList.toggle('gallery-item--hidden', !match);
                    if (match) {
                        item.style.animationDelay = (visible * 0.06) + 's';
                        item.style.animation = 'none';
                        requestAnimationFrame(() => {
                            item.style.animation = '';
                        });
                        visible++;
                    }
                });

                emptyState.style.display = visible === 0 ? 'block' : 'none';
            });
        });

        // ── Lightbox ──────────────────────────────────────────
        const lightbox = document.getElementById('lightbox');
        const backdrop = document.getElementById('lightboxBackdrop');
        const lbImg = document.getElementById('lightboxImg');
        const lbCaption = document.getElementById('lightboxCaption');
        const lbTag = document.getElementById('lightboxTag');
        const lbCounter = document.getElementById('lightboxCounter');
        const lbClose = document.getElementById('lightboxClose');
        const lbPrev = document.getElementById('lightboxPrev');
        const lbNext = document.getElementById('lightboxNext');

        let currentIndex = 0;
        let visibleItems = [];

        function getVisibleItems() {
            return [...items].filter(el => !el.classList.contains('gallery-item--hidden'));
        }

        function openLightbox(index) {
            visibleItems = getVisibleItems();
            currentIndex = index;
            showImage(currentIndex);
            lightbox.classList.add('lightbox--open');
            lightbox.style.display = 'flex';
            backdrop.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('lightbox--open');
            lightbox.style.display = 'none';
            backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }

        function showImage(index) {
            const item = visibleItems[index];
            const img = item.querySelector('.gallery-item__img');
            const cap = item.querySelector('.gallery-item__caption');
            const lbl = item.querySelector('.gallery-item__label');
            lbImg.src = img.src;
            lbImg.alt = img.alt;
            lbCaption.textContent = cap ? cap.textContent : '';
            lbTag.textContent = lbl ? lbl.textContent : '';
            lbCounter.textContent = (index + 1) + ' / ' + visibleItems.length;
        }

        items.forEach((item, i) => {
            const zoomBtn = item.querySelector('.gallery-item__zoom');
            if (zoomBtn) {
                zoomBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    visibleItems = getVisibleItems();
                    const vi = visibleItems.indexOf(item);
                    openLightbox(vi >= 0 ? vi : 0);
                });
            }
            item.addEventListener('click', () => {
                visibleItems = getVisibleItems();
                const vi = visibleItems.indexOf(item);
                openLightbox(vi >= 0 ? vi : 0);
            });
        });

        lbClose.addEventListener('click', closeLightbox);
        backdrop.addEventListener('click', closeLightbox);

        lbPrev.addEventListener('click', (e) => {
            e.stopPropagation();
            currentIndex = (currentIndex - 1 + visibleItems.length) % visibleItems.length;
            showImage(currentIndex);
        });
        lbNext.addEventListener('click', (e) => {
            e.stopPropagation();
            currentIndex = (currentIndex + 1) % visibleItems.length;
            showImage(currentIndex);
        });

        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('lightbox--open')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') lbPrev.click();
            if (e.key === 'ArrowRight') lbNext.click();
        });
    })();
</script>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
