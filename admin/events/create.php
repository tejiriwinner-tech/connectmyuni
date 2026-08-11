<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

require_once __DIR__ . '/../../services/EventService.php';
use ConnectMyUni\Services\EventService;

$eventService = new EventService();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../config/Security.php';
    use ConnectMyUni\Config\Security;
    
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date = $_POST['date'] ?? '';
        $time = trim($_POST['time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        $status = trim($_POST['status'] ?? 'draft');
        $registrationAvailable = isset($_POST['registration_available']) ? 1 : 0;
        
        if (empty($title)) {
            $error = 'Event title is required.';
        } elseif (empty($date)) {
            $error = 'Event date is required.';
        } elseif (empty($location)) {
            $error = 'Event location is required.';
        } else {
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = __DIR__ . '/../../uploads/events/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $_FILES['image']['name']);
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $imagePath = '/uploads/events/' . $fileName;
                }
            }
            
            $eventId = $eventService->create([
                'title' => $title,
                'description' => $description,
                'date' => $date,
                'time' => $time ?: null,
                'location' => $location,
                'category' => $category,
                'image_path' => $imagePath,
                'registration_available' => $registrationAvailable,
                'status' => $status,
            ]);
            
            if ($eventId) {
                header('Location: index.php?saved=1');
                exit;
            } else {
                $error = 'Failed to create event. Please try again.';
            }
        }
    }
}

$page_title = 'Add New Event';
include '../admin-header.php';
?>

<div class="page-heading">
    <h1>Add New Event</h1>
    <p>Create a new event for your audience</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding: 30px;">
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo ConnectMyUni\Config\Security::generateCsrfToken(); ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Event Title <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        <small style="color: var(--txt-muted);">You can use HTML tags for formatting</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Date <span style="color: var(--danger);">*</span></label>
                                <input type="date" name="date" class="form-control" required value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Time</label>
                                <input type="time" name="time" class="form-control" value="<?php echo htmlspecialchars($_POST['time'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Location <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="location" class="form-control" required value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control">
                            <option value="general">General</option>
                            <option value="workshop">Workshop</option>
                            <option value="seminar">Seminar</option>
                            <option value="webinar">Webinar</option>
                            <option value="career">Career Fair</option>
                            <option value="cultural">Cultural</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="registration_available" style="width: 18px; height: 18px;">
                            <span>Registration Available</span>
                        </label>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Event Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small style="color: var(--txt-muted);">Max size: 2MB. Formats: JPG, PNG, WebP</small>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Event
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</main>
<?php include '../footer.php'; ?>
