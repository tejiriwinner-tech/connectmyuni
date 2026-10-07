<?php
require_once __DIR__ . '/../../backend/bootstrap.php';
use ConnectMyUni\Services\EventService;

// Load events from database
$eventService = new EventService();
try {
    $events = $eventService->getAllForPublic();
} catch (\Throwable $e) {
    $events = [];
}

// Sort by date (newest first)
usort($events, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Get available categories
$categories = array_unique(array_map(function($e) {
    return $e['category'] ?? 'announcement';
}, $events));
sort($categories);

// SEO Meta Configuration
$page_title = 'Latest Updates, University News & Announcements | Connect MyUni';
$meta_description = 'Stay informed with the latest study abroad news, international scholarship deadlines, university admission updates, and immigration policy changes.';
$meta_keywords = 'study abroad news, university admission updates, scholarship deadlines, overseas education announcements';

$origin = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'connectmyuni.com');

$schema_json_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'CollectionPage',
    'name'     => 'Connect MyUni News & Updates',
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
                'name' => 'Updates',
                'item' => $origin . '/updates.php'
            ]
        ]
    ]
];

include __DIR__ . '/../components/header.php';
?>

<!-- Updates Content -->
<section class="updates-page-section cmi-section-enter">
    <div class="container">
        <div class="row">
            <!-- Filter Buttons -->
            <div class="col-12 mb-5">
                <div class="updates-filters cmi-fade-up">
                    <button class="filter-btn active" data-filter="all">All Updates</button>
                    <?php foreach ($categories as $cat): ?>
                        <button class="filter-btn" data-filter="<?php echo htmlspecialchars($cat); ?>">
                            <?php echo ucfirst(htmlspecialchars($cat)); ?>s
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Updates Grid -->
            <div class="col-12">
                <div class="updates-grid" id="updatesGrid">
                    <?php
                    if (empty($events)) {
                        echo '<div class="col-12 text-center py-5">
                                <p class="text-muted">No updates available at the moment. Check back soon!</p>
                            </div>';
                    } else {
                        foreach ($events as $event):
                    ?>
                            <div class="update-card cmi-fade-up" data-category="<?php echo htmlspecialchars($event['category']); ?>">
                                <div class="update-card-image">
                                    <!-- Lazy loading image -->
                                    <img loading="lazy" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3C/svg%3E"
                                        data-src="<?php echo htmlspecialchars($event['image']); ?>"
                                        alt="<?php echo htmlspecialchars($event['title']); ?>"
                                        width="400" height="300">
                                    <span class="update-badge"><?php echo strtoupper(htmlspecialchars($event['category'])); ?></span>
                                </div>
                                <div class="update-card-content">
                                    <h3 class="update-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p class="update-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('F d, Y', strtotime($event['date'])); ?>
                                    </p>
                                    <p class="update-description">
                                        <?php
                                        $shortDesc = substr(htmlspecialchars($event['description']), 0, 150);
                                        echo $shortDesc . (strlen($event['description']) > 150 ? '...' : '');
                                        ?>
                                    </p>
                                    <div class="update-actions">
                                        <a href="event-detail.php?id=<?php echo urlencode($event['id']); ?>" class="btn-read-more">
                                            Read More <i class="fas fa-arrow-right"></i>
                                        </a>
                                        <a href="registration.php?event_id=<?php echo urlencode($event['id']); ?>" class="btn-register">
                                            Register Now <i class="fas fa-user-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                    <?php
                        endforeach;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script defer>
    // Filter functionality (deferred)
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const updateCards = document.querySelectorAll('.update-card');

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                // Update active button
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Filter cards
                updateCards.forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-category') === filter) {
                        card.style.display = 'block';
                        setTimeout(() => card.classList.add('show'), 10);
                    } else {
                        card.style.display = 'none';
                        card.classList.remove('show');
                    }
                });
            });
        });
    });
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
