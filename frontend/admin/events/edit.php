<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\EventService;
use ConnectMyUni\Services\MediaService;
use ConnectMyUni\Helpers\MediaResolver;

AuthMiddleware::requireAuth();
$page_title = 'Edit Event';
include __DIR__ . '/../components/admin-header.php';

$service = new EventService();
$id      = (int) ($_GET['id'] ?? 0);
$event   = null;
if ($id > 0) {
    $event = $service->getEvent((string) $id);
}
if (!$event) {
    echo '<div class="card"><p class="form-hint" style="padding:18px">Event not found.</p></div>';
    include __DIR__ . '/../components/footer.php';
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $data = [
            'title'             => trim($_POST['title'] ?? ''),
            'category'          => trim($_POST['category'] ?? ''),
            'date'              => trim($_POST['date'] ?? ''),
            'event_time'        => trim($_POST['event_time'] ?? ''),
            'description'       => trim($_POST['description'] ?? ''),
            'details'           => trim($_POST['details'] ?? ''),
            'registration_link' => trim($_POST['registration_link'] ?? ''),
            'location'          => trim($_POST['location'] ?? ''),
            'duration'          => trim($_POST['duration'] ?? ''),
            'capacity'          => trim($_POST['capacity'] ?? ''),
            'requirements'      => trim($_POST['requirements'] ?? ''),
            'contact_info'      => trim($_POST['contact_info'] ?? ''),
        ];

        if (!empty($_FILES['image']['name'])) {
            $upload = (new MediaService())->store($_FILES['image'], 'events');
            if ($upload['key'] !== null) {
                $data['image_path'] = $upload['key'];
            } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = $upload['error'];
            }
        } elseif (!empty($_POST['ai_image_path'])) {
            $data['image_path'] = trim((string) $_POST['ai_image_path']);
        } else {
            $data['image_path'] = $event['image_path'] ?? null;
        }

        if ($error === '') {
            try {
                if ($service->updateEvent($id, $data)) {
                    $success = 'Event updated.';
                    $event   = $service->getEvent((string) $id);
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>Edit Event</h1>
    <p>Update the event. The existing image is kept unless a new one is uploaded.</p>
    <a class="btn btn-ghost" href="<?php echo $admin_url; ?>events/">Back</a>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

<div class="card">
    <?php if (($event['image_path'] ?? '') !== ''): ?>
        <img src="<?php echo htmlspecialchars(MediaResolver::url($event['image_path'])); ?>" alt="" style="max-width:200px;max-height:120px;object-fit:cover;border-radius:8px;margin-bottom:16px;display:block">
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo Security::csrfInput(); ?>
        <?php
        $aiContentType  = 'event';
        $aiFieldMap     = [
            'title'        => 'title',
            'description'  => 'description',
            'details'      => 'details',
            'category'     => 'category',
            'location'     => 'location',
            'cta'          => 'registration_link',
        ];
        $aiButtonLabel  = 'Generate Event Content with AI';
        include __DIR__ . '/../components/ai-content-generator.php';
        ?>
        <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input class="form-control" type="text" name="title" required value="<?php echo htmlspecialchars($event['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Category <span class="req">*</span></label>
            <select class="form-control" name="category" required>
                <?php foreach (['webinar', 'workshop', 'announcement', 'video'] as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo (($event['category'] ?? '') === $cat) ? 'selected' : ''; ?>><?php echo ucfirst($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Date <span class="req">*</span></label>
            <input class="form-control" type="date" name="date" required value="<?php echo htmlspecialchars($event['event_date'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Time</label>
            <input class="form-control" type="time" name="event_time" value="<?php echo htmlspecialchars($event['event_time'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Description <span class="req">*</span></label>
            <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Details</label>
            <textarea class="form-control" name="details" rows="4"><?php echo htmlspecialchars($event['details'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Event Flyer / Image (JPEG / PNG / WEBP, max 5 MB)</label>
            <?php
            $aiImageCategory = 'events';
            $aiImageButtonLabel = 'Generate Event Flyer with AI';
            include __DIR__ . '/../components/ai-image-generator.php';
            ?>
            <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <p class="form-hint">Leave empty to keep the current image, or generate a new AI flyer above.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Registration Link</label>
            <input class="form-control" type="url" name="registration_link" value="<?php echo htmlspecialchars($event['registration_link'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Location</label>
            <input class="form-control" type="text" name="location" value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Duration</label>
            <input class="form-control" type="text" name="duration" value="<?php echo htmlspecialchars($event['duration'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Capacity</label>
            <input class="form-control" type="text" name="capacity" value="<?php echo htmlspecialchars($event['capacity'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Requirements</label>
            <input class="form-control" type="text" name="requirements" value="<?php echo htmlspecialchars($event['requirements'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Contact Info</label>
            <input class="form-control" type="text" name="contact_info" value="<?php echo htmlspecialchars($event['contact_info'] ?? ''); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>events/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

