<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';
use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();
$csrfToken = Security::generateCsrfToken();
?>
<?php include __DIR__ . '/components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Add Gallery Image</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>Upload New Gallery Image</h3>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="">
            <?php echo Security::csrfInput(); ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="mb-3">
                <label class="form-label">Title <span class="req">*</span></label>
                <input type="text" class="form-control" name="title" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Alt Text <span class="req">*</span></label>
                <input type="text" class="form-control" name="alt_text" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select class="form-control" name="category">
                    <option value="campus" <?php echo (($_POST['category'] ?? '') === 'campus') ? 'selected' : ''; ?>>Campus Life</option>
                    <option value="events" <?php echo (($_POST['category'] ?? '') === 'events') ? 'selected' : ''; ?>>Events</option>
                    <option value="graduation" <?php echo (($_POST['category'] ?? '') === 'graduation') ? 'selected' : ''; ?>>Graduation</option>
                    <option value="airport" <?php echo (($_POST['category'] ?? '') === 'airport') ? 'selected' : ''; ?>>Airport/Departure</option>
                    <option value="general" <?php echo (($_POST['category'] ?? '') === 'general') ? 'selected' : ''; ?>>General</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" name="sort_order" value="<?php echo (($_POST['sort_order'] ?? 0)); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Image <span class="req">*</span></label>
                <input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/webp" required>
                <small class="text-muted">Allowed: JPEG, PNG, WebP. Max size: 5 MB.</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" name="submit">Upload Image</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $upload = (new \ConnectMyUni\Services\GalleryService())->storeImage($_FILES['image'] ?? [], $_POST['category'] ?? 'gallery');
        if ($upload['error']) {
            $error = $upload['error'];
        } else {
            $key = $upload['key'];
            $pdo = \ConnectMyUni\Database::getConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO gallery_images (title, image_path, alt_text, category, sort_order, is_active)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $r = $stmt->execute([
                $_POST['title'] ?? '',
                'storage/uploads/' . $key,
                $_POST['alt_text'] ?? '',
                $_POST['category'] ?? 'general',
                ($_POST['sort_order'] ?? 0),
                1,
            ]);
            if ($r) {
                $success = 'Gallery image uploaded successfully. <a href="index.php">Back to gallery management</a>.';
            } else {
                $error = 'Database error while saving image record.';
            }
        }
    }
}