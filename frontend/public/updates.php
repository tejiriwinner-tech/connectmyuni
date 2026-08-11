<?php include 'components/header.php'; ?>

<?php
// Include cache manager
require_once __DIR__ . '/../../backend/helpers/CacheManager.php';

use ConnectMyUni\CacheManager;

// Load events using cache
$events = CacheManager::getEvents();

// Sort by date (newest first)
usort($events, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<!-- Updates Content -->
<section class="updates-page-section">
    <div class="container">
        <div class="row">
            <!-- Filter Buttons -->
            <div class="col-12 mb-5">
                <div class="updates-filters">
                    <button class="filter-btn active" data-filter="all">All Updates</button>
                    <button class="filter-btn" data-filter="webinar">Webinars</button>
                    <button class="filter-btn" data-filter="workshop">Workshops</button>
                    <button class="filter-btn" data-filter="announcement">Announcements</button>
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
                            <div class="update-card" data-category="<?php echo htmlspecialchars($event['category']); ?>">
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

<?php include 'components/footer.php'; ?>
