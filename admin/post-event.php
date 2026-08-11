<?php include 'admin-header.php'; ?>

<?php
$success = false;
$error   = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title             = trim($_POST['title'] ?? '');
    $category          = trim($_POST['category'] ?? '');
    $date              = trim($_POST['date'] ?? '');
    $description       = trim($_POST['description'] ?? '');
    $image             = trim($_POST['image'] ?? 'asset/image1.png');
    $registration_link = trim($_POST['registration_link'] ?? '');
    $details           = trim($_POST['details'] ?? '');
    $what_to_expect    = trim($_POST['what_to_expect'] ?? '');
    $requirements      = trim($_POST['requirements'] ?? '');
    $contact_info      = trim($_POST['contact_info'] ?? '');
    $duration          = trim($_POST['duration'] ?? '');
    $capacity          = trim($_POST['capacity'] ?? '');

    if (empty($title) || empty($category) || empty($date) || empty($description)) {
        $error   = true;
        $message = 'Please fill in all required fields — Title, Category, Date, and Description.';
    } elseif (!in_array($category, ['webinar','workshop','announcement','video'])) {
        $error   = true;
        $message = 'Invalid category selected.';
    } else {
        $eventsFile = dirname(__DIR__, 2) . '/data/events.json';
        if (!is_dir(dirname($eventsFile))) mkdir(dirname($eventsFile), 0755, true);
        $events = [];
        if (file_exists($eventsFile)) $events = json_decode(file_get_contents($eventsFile), true) ?? [];

        $eventId = strtolower(str_replace(' ', '-', $title)) . '-' . date('Y-m-d-His');
        
        // Normalize: store as 'id' for CacheManager compatibility
        $newEvent = [
            'id' => $eventId,
            'title' => $title,
            'category' => $category,
            'date' => $date,
            'description' => $description,
            'image' => $image,
            'registration_link' => $registration_link,
            'details' => $details,
            'what_to_expect' => $what_to_expect,
            'requirements' => $requirements,
            'contact_info' => $contact_info,
            'duration' => $duration,
            'capacity' => $capacity,
        ];
        
        $events[] = $newEvent;

        if (file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            // Clear cache after successful write
            require_once __DIR__ . '/../includes/CacheManager.php';
            \ConnectMyUni\CacheManager::clearEventsCache();
            
            $success = true;
            $message = 'Event published successfully. It will appear on the Events and Updates pages.';
            $_POST   = [];
        } else {
            $error   = true;
            $message = 'Failed to save event. Check file permissions on the data/ directory.';
        }
    }
}

function old($key, $default = '') {
    return htmlspecialchars($_POST[$key] ?? $default);
}
?>

<div class="page-heading">
    <h1>Post New Event</h1>
    <p>Fill in the details below to publish a new event to the website.</p>
</div>

<?php if ($success): ?>
<div class="alert alert-success">
    <i class="fas fa-circle-check"></i>
    <div><?php echo $message; ?> &nbsp;<a href="../events.php" target="_blank" style="color:inherit;font-weight:700;">View events →</a></div>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="fas fa-triangle-exclamation"></i>
    <div><?php echo $message; ?></div>
</div>
<?php endif; ?>

<form method="POST" action="" id="eventForm">
<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

    <!-- ── Left column — main fields ── -->
    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Core info card -->
        <div class="card">
            <div class="section-label" style="margin-bottom:20px;">Core Information</div>

            <div class="form-group">
                <label class="form-label">Event Title <span class="req">*</span></label>
                <input type="text" name="title" class="form-control"
                       placeholder="e.g. IELTS Preparation Masterclass"
                       value="<?php echo old('title'); ?>" required>
                <p class="form-hint">Keep it concise and engaging — this is the first thing visitors will see.</p>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Category <span class="req">*</span></label>
                    <select name="category" class="form-control" required>
                        <option value="">— Select —</option>
                        <?php
                        $cats = ['webinar'=>'Webinar','workshop'=>'Workshop',
                                 'announcement'=>'Announcement','video'=>'Video'];
                        foreach ($cats as $val => $label):
                            $sel = old('category') === $val ? 'selected' : '';
                        ?>
                        <option value="<?php echo $val; ?>" <?php echo $sel; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Event Date <span class="req">*</span></label>
                    <input type="date" name="date" class="form-control"
                           value="<?php echo old('date'); ?>" required>
                </div>
            </div>
        </div>

        <!-- Description card -->
        <div class="card">
            <div class="section-label" style="margin-bottom:20px;">Content</div>

            <div class="form-group">
                <label class="form-label">Short Description <span class="req">*</span></label>
                <textarea name="description" class="form-control" rows="4"
                          placeholder="A compelling summary of the event (shown on cards and listings)..."
                          required><?php echo old('description'); ?></textarea>
                <p class="form-hint">This appears on event cards. Aim for 1–2 sentences.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Full Details</label>
                <textarea name="details" class="form-control" rows="5"
                          placeholder="In-depth information shown on the event detail page..."><?php echo old('details'); ?></textarea>
                <p class="form-hint">Visible only on the dedicated event detail page.</p>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">What to Expect</label>
                <textarea name="what_to_expect" class="form-control" rows="4"
                          placeholder="One item per line:&#10;Live Q&A session&#10;Expert presentations&#10;Networking opportunities"><?php echo old('what_to_expect'); ?></textarea>
                <p class="form-hint">Each line will render as a checklist item on the detail page.</p>
            </div>
        </div>

        <!-- Media & registration -->
        <div class="card">
            <div class="section-label" style="margin-bottom:20px;">Media & Registration</div>

            <div class="form-group">
                <label class="form-label">Image Path</label>
                <input type="text" name="image" class="form-control"
                       placeholder="asset/image1.png"
                       value="<?php echo old('image','asset/image1.png'); ?>">
                <p class="form-hint">Relative path to the event cover image. Default: asset/image1.png</p>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Registration Link</label>
                <input type="url" name="registration_link" class="form-control"
                       placeholder="https://forms.example.com/register"
                       value="<?php echo old('registration_link'); ?>">
                <p class="form-hint">External URL where attendees can sign up.</p>
            </div>
        </div>

    </div>

    <!-- ── Right column — meta fields + submit ── -->
    <div style="display:flex;flex-direction:column;gap:20px;position:sticky;top:80px;">

        <!-- Logistics -->
        <div class="card">
            <div class="section-label" style="margin-bottom:20px;">Logistics</div>

            <div class="form-group">
                <label class="form-label">Duration</label>
                <input type="text" name="duration" class="form-control"
                       placeholder="e.g. 2 hours"
                       value="<?php echo old('duration'); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Capacity</label>
                <input type="text" name="capacity" class="form-control"
                       placeholder="e.g. 50 participants"
                       value="<?php echo old('capacity'); ?>">
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Requirements</label>
                <input type="text" name="requirements" class="form-control"
                       placeholder="e.g. Intermediate English"
                       value="<?php echo old('requirements'); ?>">
            </div>
        </div>

        <!-- Contact -->
        <div class="card">
            <div class="section-label" style="margin-bottom:20px;">Contact</div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Contact Information</label>
                <textarea name="contact_info" class="form-control" rows="4"
                          placeholder="Email, phone, WhatsApp..."><?php echo old('contact_info'); ?></textarea>
            </div>
        </div>

        <!-- Submit -->
        <div class="card" style="background:rgba(0,82,204,0.06);border-color:rgba(0,82,204,0.2);">
            <p style="font-size:0.8rem;color:var(--txt-muted);margin-bottom:16px;line-height:1.6;">
                Once published, this event will appear on the <strong style="color:var(--txt);">Events</strong> and
                <strong style="color:var(--txt);">Homepage</strong> sections immediately.
            </p>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px 20px;font-size:0.9rem;">
                <i class="fas fa-paper-plane"></i> Publish Event
            </button>
            <a href="index.php" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:10px;">
                Cancel
            </a>
        </div>

        <!-- Navigation hint -->
        <div style="font-size:0.78rem;color:var(--txt-muted);line-height:1.6;text-align:center;">
            <a href="../events.php" target="_blank" style="color:var(--accent);text-decoration:none;">
                <i class="fas fa-external-link-alt"></i> View published events
            </a>
        </div>
    </div>

</div>
</form>

</main>
<?php include '../footer.php'; ?>
</body>
</html>
