<?php include 'header.php'; ?>

<?php
// Include cache manager
require_once __DIR__ . '/includes/CacheManager.php';

use ConnectMyUni\CacheManager;

// Get event ID from URL
$eventId = $_GET['id'] ?? null;
$event = null;

// Load event using cache
if ($eventId) {
    $event = CacheManager::getEventById($eventId);
}

// If event not found, show error
if (!$event) {
?>
    <section class="event-detail-section">
        <div class="container">
            <div class="text-center py-5">
                <h2>Event Not Found</h2>
                <p class="text-muted">The event you're looking for doesn't exist.</p>
                <a href="updates.php" class="btn btn-primary mt-3">Back to Updates</a>
            </div>
        </div>
    </section>
<?php
    include 'footer.php';
    exit;
}
?>

<!-- Event Detail Content -->
<section class="event-detail-section">
    <div class="container">
        <div class="event-detail-wrapper">
            <!-- Main Content -->
            <div class="event-detail-main">
                <!-- Featured Image -->
                <div class="event-detail-image">
                    <img loading="lazy" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 400'%3E%3C/svg%3E"
                        data-src="<?php echo htmlspecialchars($event['image']); ?>"
                        alt="<?php echo htmlspecialchars($event['title']); ?>"
                        width="800" height="400">
                    <span class="event-detail-badge"><?php echo strtoupper(htmlspecialchars($event['category'])); ?></span>
                </div>

                <!-- Event Details -->
                <div class="event-detail-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <strong>Event Date</strong>
                            <p><?php echo date('F d, Y', strtotime($event['date'])); ?></p>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <div>
                            <strong>Category</strong>
                            <p><?php echo htmlspecialchars($event['category']); ?></p>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-id-card"></i>
                        <div>
                            <strong>Event ID</strong>
                            <p><?php echo htmlspecialchars($event['id']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="event-detail-content">
                    <h2>Event Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>

                <!-- Additional Details -->
                <?php if (!empty($event['details'])): ?>
                    <div class="event-detail-content">
                        <h2>More Details</h2>
                        <p><?php echo nl2br(htmlspecialchars($event['details'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- What to Expect -->
                <?php if (!empty($event['what_to_expect'])): ?>
                    <div class="event-detail-content">
                        <h2>What to Expect</h2>
                        <ul class="event-expectations">
                            <?php foreach (array_filter(array_map('trim', explode("\n", $event['what_to_expect']))) as $item): ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Requirements -->
                <?php if (!empty($event['requirements'])): ?>
                    <div class="event-detail-content">
                        <h2>Requirements</h2>
                        <p><?php echo nl2br(htmlspecialchars($event['requirements'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Contact Info -->
                <?php if (!empty($event['contact_info'])): ?>
                    <div class="event-detail-content">
                        <h2>Contact Information</h2>
                        <p><?php echo nl2br(htmlspecialchars($event['contact_info'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="event-detail-sidebar">
                <!-- Registration Box -->
                <div class="sidebar-box registration-box">
                    <h3>Register for This Event</h3>
                    <a href="registration.php?event_id=<?php echo urlencode($event['id']); ?>" class="btn-register-large">
                        <i class="fas fa-user-plus"></i> Register Now
                    </a>
                    <p class="registration-info">Complete the form to secure your spot at this event</p>
                </div>

                <!-- Quick Facts -->
                <div class="sidebar-box">
                    <h3>Quick Facts</h3>
                    <div class="quick-facts">
                        <div class="fact-item">
                            <strong>Type</strong>
                            <span><?php echo ucfirst(htmlspecialchars($event['category'])); ?></span>
                        </div>
                        <div class="fact-item">
                            <strong>Date</strong>
                            <span><?php echo date('M d, Y', strtotime($event['date'])); ?></span>
                        </div>
                        <?php if (!empty($event['duration'])): ?>
                            <div class="fact-item">
                                <strong>Duration</strong>
                                <span><?php echo htmlspecialchars($event['duration']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($event['capacity'])): ?>
                            <div class="fact-item">
                                <strong>Capacity</strong>
                                <span><?php echo htmlspecialchars($event['capacity']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Share Events -->
                <div class="sidebar-box">
                    <h3>Share This Event</h3>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($event['title']); ?>" target="_blank" class="share-btn twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($event['title'] . ' - https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="mailto:?subject=<?php echo urlencode($event['title']); ?>&body=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" class="share-btn email">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- Related Events -->
<?php
$relatedEvents = array_filter($events ?? [], function ($e) use ($event) {
    return $e['category'] === $event['category'] && $e['id'] !== $event['id'];
});
$relatedEvents = array_slice($relatedEvents, 0, 3);

if (!empty($relatedEvents)):
?>
    <section class="event-related-section">
        <div class="container">
            <h2>Related Events</h2>
            <div class="related-events-grid">
                <?php foreach ($relatedEvents as $relatedEvent): ?>
                    <div class="related-event-card">
                        <div class="related-event-image">
                            <img src="<?php echo htmlspecialchars($relatedEvent['image']); ?>" alt="<?php echo htmlspecialchars($relatedEvent['title']); ?>">
                        </div>
                        <h4><?php echo htmlspecialchars($relatedEvent['title']); ?></h4>
                        <p class="event-date">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('M d, Y', strtotime($relatedEvent['date'])); ?>
                        </p>
                        <a href="event-detail.php?id=<?php echo urlencode($relatedEvent['id']); ?>" class="btn-view-event">
                            View Event <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include 'footer.php'; ?>