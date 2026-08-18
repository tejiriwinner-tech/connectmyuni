<?php include __DIR__ . '/components/admin-header.php'; ?>

<?php
// Load real database statistics (classes autoloaded via bootstrap.php)
require_once __DIR__ . '/../../backend/bootstrap.php';

use ConnectMyUni\Repositories\EventRepository;
use ConnectMyUni\Repositories\CountryRepository;
use ConnectMyUni\Repositories\UniversityRepository;
use ConnectMyUni\Repositories\ContactMessageRepository;
use ConnectMyUni\Repositories\TestimonialRepository;
use ConnectMyUni\Repositories\GalleryRepository;

$eventRepo = new EventRepository();
$countryRepo = new CountryRepository();
$universityRepo = new UniversityRepository();
$messageRepo = new ContactMessageRepository();
$testimonialRepo = new TestimonialRepository();
$galleryRepo = new GalleryRepository();

// Get counts from database
$totalEvents = count($eventRepo->getAll());
$totalCountries = count($countryRepo->getAll());
$totalUniversities = count($universityRepo->getAll());
$totalEnquiries = $messageRepo->count();
$totalTestimonials = count($testimonialRepo->getAll());

$webinars = count(array_filter($eventRepo->getAll(), fn($e) => ($e['category'] ?? '') === 'webinar'));
$workshops = count(array_filter($eventRepo->getAll(), fn($e) => ($e['category'] ?? '') === 'workshop'));
$latestEventsEvents = array_slice(array_reverse($eventRepo->getAll()), 0, 5);
?>

<div class="page-heading">
    <h1>Dashboard</h1>
    <p>Welcome back — here's what's happening with Connect MyUni.</p>
</div>

<!-- Stat cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(0,82,204,0.15);color:#4C8EF7;">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-val"><?php echo $totalEvents; ?></div>
        <div class="stat-label">Total Events</div>
        <div class="stat-change up"><i class="fas fa-arrow-up"></i> Live</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(168,85,247,0.15);color:#a855f7;">
            <i class="fas fa-video"></i>
        </div>
        <div class="stat-val"><?php echo $webinars; ?></div>
        <div class="stat-label">Webinars</div>
        <div class="stat-change muted">Published</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(34,197,94,0.12);color:#22c55e;">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-val"><?php echo $workshops; ?></div>
        <div class="stat-label">Workshops</div>
        <div class="stat-change muted">Published</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;">
            <i class="fas fa-university"></i>
        </div>
        <div class="stat-val"><?php echo $totalUniversities; ?></div>
        <div class="stat-label">Universities</div>
        <div class="stat-change muted">Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(239,68,68,0.12);color:#ef4444;">
            <i class="fas fa-globe-americas"></i>
        </div>
        <div class="stat-val"><?php echo $totalCountries; ?></div>
        <div class="stat-label">Countries</div>
        <div class="stat-change muted">Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,0.12);color:#10b981;">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-val"><?php echo $totalEnquiries; ?></div>
        <div class="stat-label">Enquiries</div>
        <div class="stat-change muted">Received</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-val"><?php echo $totalTestimonials; ?></div>
        <div class="stat-label">Testimonials</div>
        <div class="stat-change muted">Published</div>
    </div>
</div>

<!-- Two-col layout -->
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    <!-- Quick actions -->
    <div>
        <div class="section-label">Quick Actions</div>
        <div class="action-grid">
            <a class="action-box" href="post-event.php">
                <div class="box-icon" style="background:rgba(0,82,204,0.18);color:#4C8EF7;">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <h3>Post New Event</h3>
                <p>Create and publish webinars, workshops, announcements, or video content.</p>
                <div class="box-link"><i class="fas fa-arrow-right"></i> Go to form</div>
            </a>
            <a class="action-box" href="<?php echo $base_url; ?>events.php" target="_blank">
                <div class="box-icon" style="background:rgba(34,197,94,0.14);color:#22c55e;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>View Events Page</h3>
                <p>See how your posted events appear to visitors on the public events page.</p>
                <div class="box-link"><i class="fas fa-arrow-right"></i> Open page</div>
            </a>
            <a class="action-box" href="<?php echo $base_url; ?>index.php" target="_blank">
                <div class="box-icon" style="background:rgba(245,158,11,0.13);color:#f59e0b;">
                    <i class="fas fa-globe"></i>
                </div>
                <h3>Visit Live Site</h3>
                <p>Preview the full Connect MyUni website as a visitor would see it.</p>
                <div class="box-link"><i class="fas fa-arrow-right"></i> Open site</div>
            </a>
            <div class="action-box disabled">
                <div class="box-icon" style="background:rgba(136,146,164,0.1);color:#8892a4;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Analytics</h3>
                <p>View event performance, page views, and user engagement metrics.</p>
                <div class="box-link" style="color:var(--txt-muted);">
                    <i class="fas fa-lock"></i> Coming soon
                </div>
            </div>
            <div class="action-box disabled">
                <div class="box-icon" style="background:rgba(136,146,164,0.1);color:#8892a4;">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Manage Users</h3>
                <p>Control admin access and user roles across the platform.</p>
                <div class="box-link" style="color:var(--txt-muted);">
                    <i class="fas fa-lock"></i> Coming soon
                </div>
            </div>
            <div class="action-box disabled">
                <div class="box-icon" style="background:rgba(136,146,164,0.1);color:#8892a4;">
                    <i class="fas fa-gear"></i>
                </div>
                <h3>Site Settings</h3>
                <p>Configure contact info, SEO metadata, and general site preferences.</p>
                <div class="box-link" style="color:var(--txt-muted);">
                    <i class="fas fa-lock"></i> Coming soon
                </div>
            </div>
        </div>
    </div>

    <!-- Recent events sidebar -->
    <div>
        <div class="section-label">Recent Events</div>
        <div class="card" style="padding:0;overflow:hidden;">
            <?php if (empty($latestEvents)): ?>
                <div style="padding:24px;text-align:center;color:var(--txt-muted);font-size:0.85rem;">
                    <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;opacity:0.4;"></i>
                    No events posted yet
                </div>
            <?php else: ?>
                <?php foreach($latestEvents as $i => $ev): ?>
                <div style="
                    display:flex;align-items:flex-start;gap:12px;
                    padding:14px 18px;
                    <?php echo $i < count($latestEvents)-1 ? 'border-bottom:1px solid var(--border);' : ''; ?>
                ">
                    <?php
                    $cat = $ev['category'] ?? 'event';
                    $colors = ['webinar'=>['rgba(168,85,247,0.15)','#a855f7'],
                               'workshop'=>['rgba(34,197,94,0.12)','#22c55e'],
                               'video'=>['rgba(245,158,11,0.12)','#f59e0b'],
                               'announcement'=>['rgba(0,82,204,0.15)','#4C8EF7']];
                    $icons  = ['webinar'=>'fa-video','workshop'=>'fa-chalkboard-teacher',
                               'video'=>'fa-film','announcement'=>'fa-bullhorn'];
                    [$bg,$fg] = $colors[$cat] ?? ['rgba(136,146,164,0.1)','#8892a4'];
                    $icon = $icons[$cat] ?? 'fa-calendar';
                    ?>
                    <div style="width:34px;height:34px;border-radius:8px;background:<?php echo $bg;?>;
                                color:<?php echo $fg;?>;display:flex;align-items:center;
                                justify-content:center;flex-shrink:0;font-size:0.85rem;">
                        <i class="fas <?php echo $icon;?>"></i>
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:0.82rem;font-weight:600;color:var(--txt);
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($ev['title']); ?>
                        </div>
                        <div style="font-size:0.72rem;color:var(--txt-muted);margin-top:2px;">
                            <?php echo htmlspecialchars(date('d M Y', strtotime($ev['date']))); ?>
                            &nbsp;·&nbsp;
                            <span style="text-transform:capitalize;"><?php echo $cat; ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div style="padding:12px 18px;border-top:1px solid var(--border);">
                    <a href="<?php echo $base_url; ?>events.php" target="_blank"
                       style="font-size:0.78rem;font-weight:600;color:var(--accent);text-decoration:none;
                              display:flex;align-items:center;gap:5px;">
                        View all events <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Getting started tips -->
        <div class="section-label" style="margin-top:24px;">Tips</div>
        <div class="card" style="padding:18px;">
            <ul style="list-style:none;display:flex;flex-direction:column;gap:12px;">
                <?php
                $tips = [
                    ['fas fa-circle-check','#22c55e','Post your first event using the form'],
                    ['fas fa-circle-check','#22c55e','Events auto-appear on the homepage carousel'],
                    ['fas fa-circle-dot','#4C8EF7','Use categories to organize your content'],
                    ['fas fa-circle-dot','#4C8EF7','Add registration links for better engagement'],
                    ['fas fa-circle','#8892a4','Analytics coming in a future update'],
                ];
                foreach($tips as [$ico,$col,$text]):
                ?>
                <li style="display:flex;align-items:flex-start;gap:10px;font-size:0.82rem;color:var(--txt-muted);">
                    <i class="fas <?php echo $ico;?>" style="color:<?php echo $col;?>;margin-top:1px;flex-shrink:0;"></i>
                    <?php echo $text; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
