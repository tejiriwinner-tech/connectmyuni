<?php
$page_title = 'Gallery';
require_once 'header.php';

// ─────────────────────────────────────────────────────────────
// Gallery Images
// Uses images from asset/ folder with gallery prefix
// ─────────────────────────────────────────────────────────────
$gallery_items = [
    [
        'file'     => $base_url . 'asset/gallery1.png',
        'caption'  => 'Students collaborating during campus orientation',
        'category' => 'campus',
        'label'    => 'Campus Life',
    ],
    [
        'file'     => $base_url . 'asset/gallery2.png',
        'caption'  => 'Connect MyUni team at a student outreach event',
        'category' => 'events',
        'label'    => 'Events',
    ],
    [
        'file'     => $base_url . 'asset/gallery3.png',
        'caption'  => 'Students at the international departures',
        'category' => 'airport',
        'label'    => 'Departure',
    ],
    [
        'file'     => $base_url . 'asset/gallery4.png',
        'caption'  => 'Our student representative on campus',
        'category' => 'campus',
        'label'    => 'Campus Life',
    ],
    [
        'file'     => $base_url . 'asset/gallery5.png',
        'caption'  => 'Graduation ceremony celebration',
        'category' => 'graduation',
        'label'    => 'Graduation',
    ],
    [
        'file'     => $base_url . 'asset/gallery6.png',
        'caption'  => 'Students on their international journey',
        'category' => 'events',
        'label'    => 'Events',
    ],
    [
        'file'     => $base_url . 'asset/gallery7.png',
        'caption'  => 'A lively classroom session at university',
        'category' => 'campus',
        'label'    => 'Campus Life',
    ],
    [
        'file'     => $base_url . 'asset/gallery8.png',
        'caption'  => 'Connect MyUni students together on campus',
        'category' => 'campus',
        'label'    => 'Campus Life',
    ],
];

$categories = [
    'all'        => 'All',
    'campus'     => 'Campus Life',
    'airport'    => 'Departure',
    'graduation' => 'Graduation',
    'events'     => 'Events & Team',
];
?>

<!-- ── Gallery Hero Section ──────────────────────────────── -->
<section class="gallery-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-center">
                <h1 class="gallery-hero__title">Gallery</h1>
                <p class="gallery-hero__subtitle">
                    Explore inspiring moments from our students' journeys around the world
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ── Filter Bar ─────────────────────────────────────────── -->
<section class="filter-bar">
    <div class="container">
        <div class="filter-bar__inner">
            <?php foreach ($categories as $key => $label): ?>
                <button
                    class="filter-btn <?php echo $key === 'all' ? 'filter-btn--active' : ''; ?>"
                    data-filter="<?php echo $key; ?>">
                    <?php echo htmlspecialchars($label); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Masonry Gallery ────────────────────────────────────── -->
<section class="gallery-section">
    <div class="container-fluid gallery-container">
        <div class="masonry-grid" id="galleryGrid">
            <?php foreach ($gallery_items as $i => $item): ?>
                <div
                    class="masonry-item"
                    data-category="<?php echo htmlspecialchars($item['category']); ?>"
                    data-index="<?php echo $i; ?>"
                    style="animation-delay: <?php echo $i * 0.07; ?>s">
                    <div class="masonry-item__inner">
                        <img
                            src="<?php echo htmlspecialchars($item['file']); ?>"
                            alt="<?php echo htmlspecialchars($item['caption']); ?>"
                            class="masonry-item__img"
                            loading="lazy"
                            onerror="this.closest('.masonry-item').classList.add('masonry-item--placeholder')">
                        <div class="masonry-item__overlay">
                            <span class="masonry-item__label">
                                <?php echo htmlspecialchars($item['label']); ?>
                            </span>
                            <p class="masonry-item__caption">
                                <?php echo htmlspecialchars($item['caption']); ?>
                            </p>
                            <button class="masonry-item__zoom" aria-label="View full image">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty state -->
        <div class="gallery-empty" id="galleryEmpty" style="display:none">
            <div class="gallery-empty__icon">📷</div>
            <p>No photos in this category yet.</p>
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
        --white: #ffffff;
        --ink: #1a1a2e;
        --muted: #6b7280;
        --radius: 14px;
        --shadow: 0 8px 32px rgba(26, 86, 219, .13);
    }

    /* ── Hero ── */
    .gallery-hero {
        position: relative;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        padding: 4rem 0;
        color: #fff;
        text-align: center;
    }

    .gallery-hero__title {
        font-size: clamp(2.5rem, 6vw, 3.5rem);
        font-weight: 900;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }

    .gallery-hero__subtitle {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.85);
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* ── Filter Bar ── */
    .filter-bar {
        position: sticky;
        top: 62px;
        z-index: 100;
        background: rgba(255, 255, 255, .96);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid #e9ecef;
        padding: .9rem 0;
    }

    .filter-bar__inner {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .filter-btn {
        border: 1.5px solid #dee2e6;
        background: #fff;
        border-radius: 100px;
        padding: .4rem 1.1rem;
        font-size: .83rem;
        font-weight: 500;
        color: var(--muted);
        cursor: pointer;
        transition: all .2s;
    }

    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: var(--primary-light);
    }

    .filter-btn--active {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: #fff;
        font-weight: 600;
    }

    /* ── Gallery Section ── */
    .gallery-section {
        padding: 3rem 0 4rem;
        background: #f8f9fb;
    }

    .gallery-container {
        padding: 0 1.5rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Masonry via CSS columns */
    .masonry-grid {
        column-count: 4;
        column-gap: 1rem;
    }

    @media (max-width: 1200px) {
        .masonry-grid {
            column-count: 3;
        }
    }

    @media (max-width: 768px) {
        .masonry-grid {
            column-count: 2;
        }
    }

    @media (max-width: 480px) {
        .masonry-grid {
            column-count: 1;
        }
    }

    /* Gallery hero responsive */
    @media (max-width: 768px) {
        .gallery-hero {
            padding: 3rem 0;
        }

        .gallery-hero__title {
            font-size: 2rem;
        }

        .gallery-hero__subtitle {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .gallery-hero {
            padding: 2rem 0;
        }

        .gallery-hero__title {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .gallery-hero__subtitle {
            font-size: 0.95rem;
        }
    }

    /* ── Masonry Item ── */
    .masonry-item {
        break-inside: avoid;
        margin-bottom: 1rem;
        border-radius: var(--radius);
        overflow: hidden;
        cursor: pointer;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeUp .5s ease forwards;
    }

    @keyframes fadeUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .masonry-item--hidden {
        display: none;
    }

    .masonry-item__inner {
        position: relative;
        overflow: hidden;
        border-radius: var(--radius);
        background: #dde4f0;
    }

    .masonry-item__img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform .45s cubic-bezier(.25, .46, .45, .94);
        object-fit: cover;
    }

    .masonry-item:hover .masonry-item__img {
        transform: scale(1.07);
    }

    /* Overlay */
    .masonry-item__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(26, 86, 219, 0.88) 0%, transparent 55%);
        opacity: 0;
        transition: opacity .3s;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 1.25rem 1rem 1rem;
    }

    .masonry-item:hover .masonry-item__overlay {
        opacity: 1;
    }

    .masonry-item__label {
        display: inline-block;
        background: var(--secondary-color);
        color: #fff;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        padding: .18rem .55rem;
        border-radius: 100px;
        margin-bottom: .4rem;
        align-self: flex-start;
    }

    .masonry-item__caption {
        color: rgba(255, 255, 255, .92);
        font-size: .82rem;
        line-height: 1.4;
        margin: 0 0 .5rem;
    }

    .masonry-item__zoom {
        align-self: flex-end;
        background: rgba(255, 255, 255, .2);
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        cursor: pointer;
        transition: background .2s;
        padding: 0;
    }

    .masonry-item__zoom:hover {
        background: rgba(255, 255, 255, .35);
    }

    /* Placeholder when image missing */
    .masonry-item--placeholder .masonry-item__img {
        min-height: 200px;
        opacity: 0;
    }

    .masonry-item--placeholder .masonry-item__inner {
        background: linear-gradient(135deg, #dde4f0 0%, #c7d4ee 100%);
        min-height: 200px;
    }

    /* Empty state */
    .gallery-empty {
        text-align: center;
        padding: 5rem 0;
        color: var(--muted);
    }

    .gallery-empty__icon {
        font-size: 3rem;
        margin-bottom: .75rem;
    }

    /* ── Lightbox ── */
    .lightbox__backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(10, 12, 30, .92);
        z-index: 1050;
        backdrop-filter: blur(6px);
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
        max-width: min(90vw, 960px);
        max-height: 88vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .lightbox__img {
        max-width: 100%;
        max-height: 75vh;
        object-fit: contain;
        border-radius: 10px;
        box-shadow: 0 24px 80px rgba(0, 0, 0, .5);
        display: block;
    }

    .lightbox__meta {
        margin-top: 1.1rem;
        text-align: center;
    }

    .lightbox__tag {
        display: inline-block;
        background: var(--secondary-color);
        color: #fff;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        padding: .18rem .6rem;
        border-radius: 100px;
        margin-bottom: .4rem;
    }

    .lightbox__caption {
        color: rgba(255, 255, 255, .8);
        font-size: .9rem;
        margin: 0;
    }

    .lightbox__counter {
        position: fixed;
        bottom: 1.5rem;
        left: 50%;
        transform: translateX(-50%);
        color: rgba(255, 255, 255, .5);
        font-size: .8rem;
        letter-spacing: .08em;
    }

    .lightbox__close {
        position: fixed;
        top: 1.25rem;
        right: 1.5rem;
        background: rgba(255, 255, 255, .12);
        border: none;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        cursor: pointer;
        transition: background .2s;
    }

    .lightbox__close:hover {
        background: rgba(255, 255, 255, .25);
    }

    .lightbox__nav {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, .1);
        border: none;
        border-radius: 50%;
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        cursor: pointer;
        transition: background .2s;
    }

    .lightbox__nav:hover {
        background: rgba(255, 255, 255, .25);
    }

    .lightbox__nav--prev {
        left: 1.5rem;
    }

    .lightbox__nav--next {
        right: 1.5rem;
    }

    /* ── CTA ── */
    .gallery-cta {
        background: var(--primary-color);
        padding: 3.5rem 0;
        color: #fff;
    }

    .gallery-cta__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .gallery-cta__title {
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .gallery-cta__sub {
        color: rgba(255, 255, 255, .75);
        margin: 0;
        font-size: .95rem;
    }

    .gallery-cta__actions {
        display: flex;
        gap: 1rem;
        flex-shrink: 0;
    }

    .btn-cta {
        display: inline-block;
        padding: .7rem 1.75rem;
        border-radius: 100px;
        font-weight: 700;
        font-size: .9rem;
        text-decoration: none;
        transition: all .2s;
    }

    .btn-cta--primary {
        background: var(--secondary-color);
        color: #fff;
    }

    .btn-cta--primary:hover {
        background: var(--secondary-dark);
        color: #fff;
    }

    .btn-cta--ghost {
        background: transparent;
        color: #fff;
        border: 2px solid rgba(255, 255, 255, .5);
    }

    .btn-cta--ghost:hover {
        background: rgba(255, 255, 255, .12);
        border-color: #fff;
        color: #fff;
    }

    @media (max-width: 640px) {
        .gallery-cta__inner {
            flex-direction: column;
            text-align: center;
        }

        .gallery-cta__actions {
            justify-content: center;
        }
    }
</style>

<!-- ── Gallery JS ─────────────────────────────────────────── -->
<script>
    (function() {
        // ── Data ──────────────────────────────────────────────
        const items = document.querySelectorAll('.masonry-item');
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
                    item.classList.toggle('masonry-item--hidden', !match);
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
            return [...items].filter(el => !el.classList.contains('masonry-item--hidden'));
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
            const img = item.querySelector('.masonry-item__img');
            const cap = item.querySelector('.masonry-item__caption');
            const lbl = item.querySelector('.masonry-item__label');
            lbImg.src = img.src;
            lbImg.alt = img.alt;
            lbCaption.textContent = cap ? cap.textContent : '';
            lbTag.textContent = lbl ? lbl.textContent : '';
            lbCounter.textContent = (index + 1) + ' / ' + visibleItems.length;
        }

        items.forEach((item, i) => {
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

<?php require_once 'footer.php'; ?>