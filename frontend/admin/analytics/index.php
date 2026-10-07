<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Database;

AuthMiddleware::requireAuth();

$pdo = Database::getConnection();

// 1. Gather high-level metrics
$totalEvents = (int) $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$publishedEvents = (int) $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'published'")->fetchColumn();
$totalViews = (int) $pdo->query("SELECT COALESCE(SUM(views_count), 0) FROM events")->fetchColumn();
$totalRegistrations = (int) $pdo->query("SELECT COUNT(*) FROM event_registrations")->fetchColumn();
$totalInquiries = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$unreadInquiries = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
$totalAiGenerations = (int) $pdo->query("SELECT COUNT(*) FROM ai_content_requests")->fetchColumn();
$totalUniversities = (int) $pdo->query("SELECT COUNT(*) FROM universities")->fetchColumn();

// 2. Events by Category
$categoryCounts = [
    'webinar' => (int) $pdo->query("SELECT COUNT(*) FROM events WHERE category = 'webinar'")->fetchColumn(),
    'workshop' => (int) $pdo->query("SELECT COUNT(*) FROM events WHERE category = 'workshop'")->fetchColumn(),
    'video' => (int) $pdo->query("SELECT COUNT(*) FROM events WHERE category = 'video'")->fetchColumn(),
    'announcement' => (int) $pdo->query("SELECT COUNT(*) FROM events WHERE category = 'announcement'")->fetchColumn(),
];

// 3. Top performing events by views
$stmtTopEvents = $pdo->query("
    SELECT e.id, e.title, e.category, e.event_date, e.status, e.views_count,
           COUNT(r.id) AS registration_count
    FROM events e
    LEFT JOIN event_registrations r ON r.event_id = e.id
    GROUP BY e.id, e.title, e.category, e.event_date, e.status, e.views_count
    ORDER BY e.views_count DESC, registration_count DESC
    LIMIT 8
");
$topEvents = $stmtTopEvents->fetchAll();

// 4. Recent registrations
$recentRegistrations = $pdo->query("
    SELECT r.id, r.full_name, r.email, r.field_of_study, r.event_location, r.created_at, e.title AS event_title
    FROM event_registrations r
    LEFT JOIN events e ON e.id = r.event_id
    ORDER BY r.created_at DESC
    LIMIT 6
")->fetchAll();

// 5. AI Content generations breakdown
$aiBreakdown = $pdo->query("
    SELECT content_type, COUNT(*) AS cnt
    FROM ai_content_requests
    GROUP BY content_type
    ORDER BY cnt DESC
")->fetchAll();

$page_title = 'Analytics & Insights';
include __DIR__ . '/../components/admin-header.php';
?>

<div class="page-heading">
    <h1>Platform Analytics</h1>
    <p>Track visitor engagement, event reach, registrations, and administrative volume in real time</p>
</div>

<!-- 4 Top KPI Cards -->
<div class="stat-grid" style="margin-bottom:28px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(76,142,247,0.15);color:var(--accent);">
            <i class="fas fa-eye"></i>
        </div>
        <div class="stat-info">
            <span class="stat-val"><?php echo number_format($totalViews); ?></span>
            <span class="stat-lbl">Total Event Views</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(34,197,94,0.15);color:var(--success);">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-info">
            <span class="stat-val"><?php echo number_format($totalRegistrations); ?></span>
            <span class="stat-lbl">Student Registrations</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,0.15);color:var(--warning);">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-info">
            <span class="stat-val"><?php echo number_format($totalInquiries); ?></span>
            <span class="stat-lbl">Inquiries (<?php echo $unreadInquiries; ?> New)</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(168,85,247,0.15);color:#c084fc;">
            <i class="fas fa-wand-magic-sparkles"></i>
        </div>
        <div class="stat-info">
            <span class="stat-val"><?php echo number_format($totalAiGenerations); ?></span>
            <span class="stat-lbl">AI Generations Run</span>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:28px;" class="analytics-grid">
    <!-- Top Performing Events -->
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h3 style="font-size:1rem;font-weight:700;color:var(--txt);margin:0;">Top Performing Events</h3>
                <span style="font-size:0.75rem;color:var(--txt-muted);">Ranked by visitor view counts & student interest</span>
            </div>
            <a href="<?php echo $admin_url; ?>events/" class="btn btn-ghost" style="padding:4px 10px;font-size:0.8rem;">
                View All
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Views</th>
                        <th>Sign-ups</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($topEvents)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:24px;color:var(--txt-muted);">No event performance data yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topEvents as $ev): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--txt);"><?php echo htmlspecialchars($ev['title']); ?></strong>
                                </td>
                                <td>
                                    <span style="text-transform:capitalize;font-size:0.8rem;color:var(--txt-muted);">
                                        <?php echo htmlspecialchars($ev['category']); ?>
                                    </span>
                                </td>
                                <td style="font-size:0.8rem;color:var(--txt-muted);">
                                    <?php echo htmlspecialchars(date('M d, Y', strtotime($ev['event_date']))); ?>
                                </td>
                                <td>
                                    <span style="font-weight:700;color:var(--accent);display:inline-flex;align-items:center;gap:4px;">
                                        <i class="fas fa-eye" style="font-size:0.75rem;"></i> <?php echo (int) $ev['views_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight:700;color:var(--success);display:inline-flex;align-items:center;gap:4px;">
                                        <i class="fas fa-user-check" style="font-size:0.75rem;"></i> <?php echo (int) $ev['registration_count']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Distribution & Overview -->
    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card" style="padding:20px;">
            <h3 style="font-size:0.95rem;font-weight:700;color:var(--txt);margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-chart-pie" style="color:var(--accent);"></i> Event Categories
            </h3>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php
                $catMeta = [
                    'webinar' => ['label' => 'Webinars', 'color' => '#a855f7'],
                    'workshop' => ['label' => 'Workshops', 'color' => '#22c55e'],
                    'video' => ['label' => 'Videos', 'color' => '#f59e0b'],
                    'announcement' => ['label' => 'Announcements', 'color' => '#4C8EF7'],
                ];
                foreach ($catMeta as $key => $meta):
                    $cnt = $categoryCounts[$key] ?? 0;
                    $pct = $totalEvents > 0 ? round(($cnt / $totalEvents) * 100) : 0;
                ?>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:5px;">
                        <span style="color:var(--txt);"><?php echo $meta['label']; ?></span>
                        <span style="color:var(--txt-muted);font-weight:600;"><?php echo $cnt; ?> (<?php echo $pct; ?>%)</span>
                    </div>
                    <div style="width:100%;height:6px;background:rgba(255,255,255,0.06);border-radius:3px;overflow:hidden;">
                        <div style="width:<?php echo $pct; ?>%;height:100%;background:<?php echo $meta['color']; ?>;border-radius:3px;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" style="padding:20px;">
            <h3 style="font-size:0.95rem;font-weight:700;color:var(--txt);margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-brain" style="color:#c084fc;"></i> AI Studio Usage
            </h3>
            <?php if (empty($aiBreakdown)): ?>
                <p style="color:var(--txt-muted);font-size:0.82rem;margin:0;">No AI generations executed yet.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php foreach ($aiBreakdown as $row): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.04);font-size:0.82rem;">
                            <span style="color:var(--txt);text-transform:capitalize;">
                                <i class="fas fa-file-lines me-2" style="color:var(--accent);font-size:0.75rem;"></i>
                                <?php echo htmlspecialchars(str_replace('_', ' ', (string)$row['content_type'])); ?>
                            </span>
                            <span style="font-weight:700;color:var(--txt);background:rgba(255,255,255,0.06);padding:2px 8px;border-radius:4px;">
                                <?php echo (int) $row['cnt']; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Event Registrations -->
<div class="card" style="padding:0;overflow:hidden;margin-bottom:28px;">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);">
        <h3 style="font-size:1rem;font-weight:700;color:var(--txt);margin:0;">Recent Student Registrations</h3>
        <span style="font-size:0.75rem;color:var(--txt-muted);">Latest students registering for overseas education sessions</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Event</th>
                    <th>Field of Study</th>
                    <th>Location Preference</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentRegistrations)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:24px;color:var(--txt-muted);">No student registrations logged yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentRegistrations as $reg): ?>
                        <tr>
                            <td><strong style="color:var(--txt);"><?php echo htmlspecialchars($reg['full_name']); ?></strong></td>
                            <td style="color:var(--txt-muted);"><?php echo htmlspecialchars($reg['email']); ?></td>
                            <td><span style="color:var(--accent);"><?php echo htmlspecialchars($reg['event_title'] ?? 'General Consultation'); ?></span></td>
                            <td style="color:var(--txt-muted);"><?php echo htmlspecialchars($reg['field_of_study']); ?></td>
                            <td><span class="badge" style="background:rgba(255,255,255,0.06);"><?php echo htmlspecialchars($reg['event_location']); ?></span></td>
                            <td style="font-size:0.8rem;color:var(--txt-muted);"><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($reg['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
