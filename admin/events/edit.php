<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$eventId = (int) $_GET['id'];

require_once __DIR__ . '/../../services/EventService.php';
use ConnectMyUni\Services\EventService;

$eventService = new EventService();
$event = $eventService->getById($eventId);

if (!$event) {
    header('Location: index.php?error=notfound');
    exit;
}

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
            $data = [
                'title' => $title,
                'description' => $description,
                'date' => $date,
                'time' => $time ?: null,
                'location' => $location,
                'category' => $category,
                'registration_available' => $registrationAvailable,
                'status' => $status,
            ];
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = __DIR__ . '/../../uploads/events/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $_FILES['image']['name']);
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $data['image_path'] = '/uploads/events/' . $fileName;
                    
                    // Delete old image
                    if (!empty($event['image_path']) && file_exists(__DIR__ . '/../../' . $event['image_path'])) {
                        unlink(__DIR__ . '/../../' . $event['image_path']);
                    }
                }
            }
            
            if ($eventService->update($eventId, $data)) {
                header('Location: index.php?saved=1');
                exit;
            } else {
                $error = 'Failed to update event. Please try again.';
            }
        }
    }
}

$page_title = 'Edit Event';
include '../admin-header.php';
?>

<div class="page-heading">
    <h1>Edit Event</h1>
    <p>Update event details</p>
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
                        <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($event['title']); ?>">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Date <span style="color: var(--danger);">*</span></label>
                                <input type="date" name="date" class="form-control" required value="<?php echo htmlspecialchars($event['date']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">Time</label>
                                <input type="time" name="time" class="form-control" value="<?php echo htmlspecialchars($event['time'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Location <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="location" class="form-control" required value="<?php echo htmlspecialchars($event['location']); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control">
                            <option value="general" <?php echo $event['category'] === 'general' ? 'selected' : ''; ?>>General</option>
                            <option value="workshop" <?php echo $event['category'] === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                            <option value="seminar" <?php echo $event['category'] === 'seminar' ? 'selected' : ''; ?>>Seminar</option>
                            <option value="webinar" <?php echo $event['category'] === 'webinar' ? 'selected' : ''; ?>>Webinar</option>
                            <option value="career" <?php echo $event['category'] === 'career' ? 'selected' : ''; ?>>Career Fair</option>
                            <option value="cultural" <?php echo $event['category'] === 'cultural' ? 'selected' : ''; ?>>Cultural</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="draft" <?php echo $event['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $event['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $event['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="registration_available" class="form-check-input" style="width: 18px; height: 18px;" <?php echo $event['registration_available'] ? 'checked' : ''; ?>>
                            <span>Registration Available</span>
                        </label>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Current Image</label>
                        <?php if (!empty($event['image_path']) && file_exists(__DIR__ . '/../../' . $event['image_path'])): ?>
                            <img src="<?php echo $event['image_path']; ?>" alt="" style="width: 100%; max-width: 200px; border-radius: 8px; margin-bottom: 10px;">
                        <?php else: ?>
                            <p style="color: var(--txt-muted);">No image uploaded</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Change Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Event
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
