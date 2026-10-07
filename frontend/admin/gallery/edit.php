<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';
use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

$id = (int) ($_GET['id'] ?? 0);
$service = new \ConnectMyUni\Services\GalleryService();
$image = $service->findById($id);

if (!$image) {
    echo '<div class="alert alert-danger">Gallery image not found.</div>';
    include __DIR__ . '/../components/footer.php';
    exit;
}
?>
<?php include __DIR__ . '/../components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Edit Gallery Image</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>Edit Gallery Image #<?php echo $image['id']; ?></h3>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php echo Security::csrfInput(); ?>
            <input type="hidden" name="id" value="<?php echo $image['id']; ?>">

            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($image['title'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="campus" <?php echo ($image['category'] ?? '') === 'campus' ? 'selected' : ''; ?>>Campus Life</option>
                    <option value="departure" <?php echo ($image['category'] ?? '') === 'departure' ? 'selected' : ''; ?>>Student Departure</option>
                    <option value="graduation" <?php echo ($image['category'] ?? '') === 'graduation' ? 'selected' : ''; ?>>Graduation</option>
                    <option value="events" <?php echo ($image['category'] ?? '') === 'events' ? 'selected' : ''; ?>>Events & Team</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="image_url" class="form-label">Image URL</label>
                <input type="text" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($image['image_url'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo (int) ($image['sort_order'] ?? 0); ?>">
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php echo !empty($image['is_active']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_active">Active (Visible on public site)</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" name="save">Save Changes</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $service = new \ConnectMyUni\Services\GalleryService();
        $imagePath = $image['image_path'];
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
            $upload = $service->storeImage($_FILES['new_image'] ?? [], $_POST['category'] ?? 'gallery');
            if ($upload['error']) {
                $error = $upload['error'];
            } else {
                $imagePath = 'storage/uploads/' . $upload['key'];
            }
        }
        $r = $service->updateMetadata($_POST['id'] ?? 0, [
            'title' => $_POST['title'] ?? '',
            'alt_text' => $_POST['alt_text'] ?? '',
            'category' => $_POST['category'] ?? 'general',
            'is_active' => ($_POST['is_active'] ?? $image['is_active']) === 1 ? 1 : 0,
        ]);
        if ($r) {
            $success = 'Gallery image updated successfully.';
            $image = $service->findById($_POST['id'] ?? 0);
        } else {
            $error = 'Database error while updating image.';
        }
    }
}