<?php
require_once __DIR__ . '/../../backend/bootstrap.php';
use ConnectMyUni\Services\EventService;
use ConnectMyUni\Helpers\MediaResolver;

$eventService = new EventService();

// Get event ID from URL
$eventId = $_GET['id'] ?? null;
$event = null;

// All published events (used for related events).
try {
    $events = $eventService->getAllForPublic();
} catch (\Throwable $e) {
    $events = [];
}

// Load event from the database.
if ($eventId) {
    try {
        $event = $eventService->getForPublic((string) $eventId);
        if ($event && !empty($event['id'])) {
            $eventService->recordView((int) $event['id']);
        }
    } catch (\Throwable $e) {
        $event = null;
    }
}

$origin = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'connectmyuni.com');

if ($event) {
    $rawDesc = strip_tags($event['description'] ?? '');
    $cleanDesc = mb_substr($rawDesc, 0, 160);
    $page_title = htmlspecialchars($event['title']) . ' | Connect MyUni Event';
    $meta_description = !empty($cleanDesc) ? $cleanDesc : "Join {$event['title']} organized by Connect MyUni.";
    $og_type = 'article';
    $og_title = $event['title'];
    $og_description = $meta_description;
    
    // Resolve flyer image
    $flyerPath = $event['image_path'] ?? $event['image_url'] ?? '';
    if (!empty($flyerPath)) {
        $og_image = MediaResolver::url($flyerPath);
    }

    // Event Schema.org JSON-LD
    $eventDate = $event['date'] ?? date('Y-m-d');
    $eventTime = $event['time'] ?? '10:00:00';
    $startIso = date('c', strtotime("{$eventDate} {$eventTime}"));
    $locationType = (!empty($event['is_virtual']) || stripos($event['location'] ?? '', 'online') !== false || stripos($event['location'] ?? '', 'zoom') !== false) 
        ? 'VirtualLocation' 
        : 'Place';

    $schema_json_ld = [
        '@context' => 'https://schema.org',
        '@type'    => 'Event',
        'name'     => $event['title'],
        'startDate' => $startIso,
        'eventStatus' => 'https://schema.org/EventScheduled',
        'eventAttendanceMode' => ($locationType === 'VirtualLocation') 
            ? 'https://schema.org/OnlineEventAttendanceMode' 
            : 'https://schema.org/OfflineEventAttendanceMode',
        'description' => $meta_description,
        'image' => !empty($og_image) ? [$origin . $og_image] : [],
        'organizer' => [
            '@type' => 'EducationalOrganization',
            'name' => 'Connect MyUni',
            'url' => $origin . '/'
        ]
    ];
    if ($locationType === 'VirtualLocation') {
        $schema_json_ld['location'] = [
            '@type' => 'VirtualLocation',
            'url' => $origin . '/event-detail.php?id=' . urlencode((string)$eventId)
        ];
    } else {
        $schema_json_ld['location'] = [
            '@type' => 'Place',
            'name' => $event['location'] ?? 'Connect MyUni Admissions Centre',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $event['location'] ?? 'Nigeria',
                'addressCountry' => 'NG'
            ]
        ];
    }
} else {
    $page_title = 'Event Not Found | Connect MyUni';
    $meta_robots = 'noindex, nofollow';
}

include __DIR__ . '/../components/header.php';

// If event not found, show error
if (!$event) {
?>
    <section class="page-hero cmi-section-enter">
        <div class="container text-center">
            <h1 class="page-hero__title">Event Not Found</h1>
            <p class="hero-subtitle">The requested event could not be found or has concluded.</p>
            <div class="mt-3">
                <a href="<?php echo $base_url; ?>events.php" class="btn btn-hero-cta">Browse All Events</a>
            </div>
        </div>
    </section>
<?php
    include __DIR__ . '/../components/footer.php';
    exit;
}
?>

<!-- Page Hero -->
<section class="page-hero cmi-section-enter">
    <div class="container">
        <div class="hero-content text-center">
            <div class="hero-badge">
                <i class="fas fa-calendar-check"></i>
                <span><?php echo strtoupper(htmlspecialchars($event['category'] ?? 'EVENT')); ?></span>
            </div>
            <h1 class="page-hero__title cmi-fade-up"><?php echo htmlspecialchars($event['title']); ?></h1>
            <p class="hero-subtitle cmi-fade-up">
                <?php echo date('l, F j, Y', strtotime($event['date'])); ?> &bull; <?php echo htmlspecialchars($event['location'] ?? 'Online / Campus'); ?>
            </p>
            <div class="hero-breadcrumb">
                <a href="<?php echo $base_url; ?>index.php">Home</a>
                <span class="mx-2">/</span>
                <a href="<?php echo $base_url; ?>events.php">Events</a>
                <span class="mx-2">/</span>
                <span><?php echo htmlspecialchars(mb_strimwidth($event['title'], 0, 30, '...')); ?></span>
            </div>
        </div>
    </div>
</section>

<!-- Event Detail Content -->
<section class="event-detail-section cmi-section-enter">
    <div class="container">
        <div class="event-detail-wrapper">
            <!-- Main Content -->
            <div class="event-detail-main cmi-fade-up">
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
                    <div class="related-event-card cmi-fade-up">
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

<?php include __DIR__ . '/../components/footer.php'; ?>
