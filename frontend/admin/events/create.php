<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\EventService;
use ConnectMyUni\Services\MediaService;

AuthMiddleware::requireAuth();
$page_title = 'New Event';
include __DIR__ . '/../components/admin-header.php';

$error   = '';
$success = '';
$service = new EventService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
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

        // Optional event image upload.
        if (!empty($_FILES['image']['name'])) {
            $upload = (new MediaService())->store($_FILES['image'], 'events');
            if ($upload['key'] !== null) {
                $data['image_path'] = $upload['key'];
            } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = $upload['error'];
            }
        }

        if ($error === '') {
            try {
                $service->createEvent($data);
                $success = 'Event created. <a href="' . $admin_url . 'events/">Back to list</a>';
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>New Event</h1>
    <p>Create a new event. The optional image is validated and stored under storage/uploads/events/.</p>
</div>

<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<div class="card">
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
            <input class="form-control" type="text" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Category <span class="req">*</span></label>
            <select class="form-control" name="category" required>
                <?php foreach (['webinar', 'workshop', 'announcement', 'video'] as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo (($_POST['category'] ?? '') === $cat) ? 'selected' : ''; ?>><?php echo ucfirst($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Date <span class="req">*</span></label>
            <input class="form-control" type="date" name="date" required value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Time</label>
            <input class="form-control" type="time" name="event_time" value="<?php echo htmlspecialchars($_POST['event_time'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Description <span class="req">*</span></label>
            <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Details</label>
            <textarea class="form-control" name="details" rows="4"><?php echo htmlspecialchars($_POST['details'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Event Image (JPEG / PNG / WEBP, max 5 MB)</label>
            <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <p class="form-hint">Optional. A unique file is generated; only its relative key is saved.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Registration Link</label>
            <input class="form-control" type="url" name="registration_link" value="<?php echo htmlspecialchars($_POST['registration_link'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Location</label>
            <input class="form-control" type="text" name="location" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Duration</label>
            <input class="form-control" type="text" name="duration" value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Capacity</label>
            <input class="form-control" type="text" name="capacity" value="<?php echo htmlspecialchars($_POST['capacity'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Requirements</label>
            <input class="form-control" type="text" name="requirements" value="<?php echo htmlspecialchars($_POST['requirements'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Contact Info</label>
            <input class="form-control" type="text" name="contact_info" value="<?php echo htmlspecialchars($_POST['contact_info'] ?? ''); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Create Event</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>events/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

