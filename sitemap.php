<?php
declare(strict_types=1);

/**
 * Connect MyUni — Dynamic XML Sitemap Generator
 * Conforms to Sitemaps.org Protocol 0.9
 */

require_once __DIR__ . '/backend/bootstrap.php';
use ConnectMyUni\Services\EventService;

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex, follow');

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'connectmyuni.com';
$origin = "{$scheme}://{$host}";
$base = rtrim(CONNECTMYUNI_BASE_URL, '/');

$staticPages = [
    ['loc' => $base . '/',                'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => $base . '/universities.php','priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => $base . '/services.php',    'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => $base . '/events.php',      'priority' => '0.8', 'changefreq' => 'daily'],
    ['loc' => $base . '/about.php',       'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => $base . '/contact.php',     'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => $base . '/gallery.php',     'priority' => '0.7', 'changefreq' => 'weekly'],
    ['loc' => $base . '/registration.php','priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => $base . '/updates.php',     'priority' => '0.7', 'changefreq' => 'daily'],
];

// Fetch published dynamic events from CMS
$events = [];
try {
    $eventService = new EventService();
    $events = $eventService->getAllForPublic();
} catch (\Throwable $e) {
    $events = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php foreach ($staticPages as $page): ?>
    <url>
        <loc><?php echo htmlspecialchars($origin . $page['loc']); ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq><?php echo $page['changefreq']; ?></changefreq>
        <priority><?php echo $page['priority']; ?></priority>
    </url>
<?php endforeach; ?>

<?php foreach ($events as $event): 
    $eventId = (string)($event['id'] ?? '');
    if ($eventId === '') continue;
    $eventLoc = $origin . $base . '/event-detail.php?id=' . urlencode($eventId);
    $lastMod = !empty($event['updated_at']) ? date('Y-m-d', strtotime($event['updated_at'])) : date('Y-m-d', strtotime($event['created_at'] ?? 'now'));
    $flyer = $event['image_path'] ?? $event['image_url'] ?? '';
?>
    <url>
        <loc><?php echo htmlspecialchars($eventLoc); ?></loc>
        <lastmod><?php echo htmlspecialchars($lastMod); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
<?php if (!empty($flyer)): 
        $imgUrl = (str_starts_with($flyer, 'http://') || str_starts_with($flyer, 'https://'))
            ? $flyer
            : ($origin . '/' . ltrim($flyer, '/'));
?>
        <image:image>
            <image:loc><?php echo htmlspecialchars($imgUrl); ?></image:loc>
            <image:title><?php echo htmlspecialchars($event['title'] ?? 'Connect MyUni Event'); ?></image:title>
        </image:image>
<?php endif; ?>
    </url>
<?php endforeach; ?>
</urlset>
