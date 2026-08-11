<?php include 'header.php'; ?>

<?php
require_once __DIR__ . '/includes/CacheManager.php';
use ConnectMyUni\CacheManager;

$events = CacheManager::getEvents();
usort($events, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

$categories = array_unique(array_map(function($e) {
    return $e['category'] ?? 'announcement';
}, $events));
sort($categories);
?>

<section class="events-page-section">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-5">
                <div class="events-filters">
                    <button class="filter-btn active" data-filter="all">All Events</button>
                    <?php foreach ($categories as $cat): ?>
                        <button class="filter-btn" data-filter="<?php echo htmlspecialchars($cat); ?>">
                            <?php echo ucfirst(htmlspecialchars($cat)); ?>s
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-12">
                <?php if (empty($events)): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">No events available at the moment. Check back soon!</p>
                    </div>
                <?php else: ?>
                    <div class="events-page-grid" id="eventsPageGrid">
                        <?php foreach ($events as $event): 
                            $eventId = $event['id'] ?? $event['eventId'] ?? uniqid();
                            $badge = strtoupper($event['category'] ?? 'EVENT');
                        ?>
                            <div class="event-page-card" data-category="<?php echo htmlspecialchars($event['category'] ?? ''); ?>">
                                <div class="event-page-image">
                                    <img src="<?php echo htmlspecialchars($event['image'] ?? 'asset/image1.png'); ?>" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>"
                                         loading="lazy">
                                    <span class="event-page-badge"><?php echo $badge; ?></span>
                                </div>
                                <div class="event-page-content">
                                    <h3 class="event-page-title-card"><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p class="event-page-date">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('F d, Y', strtotime($event['date'])); ?>
                                    </p>
                                    <p class="event-page-description">
                                        <?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 150)); ?>
                                        <?php if (strlen($event['description'] ?? '') > 150) echo '...'; ?>
                                    </p>
                                    <a href="event-detail.php?id=<?php echo urlencode($eventId); ?>" class="btn btn-event-details">
                                        Learn More
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script defer>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const eventCards = document.querySelectorAll('.event-page-card');

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                eventCards.forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-category') === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
</script>

<?php include 'footer.php'; ?>
