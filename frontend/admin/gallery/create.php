<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\GalleryService;
use ConnectMyUni\Services\MediaService;

AuthMiddleware::requireAuth();
$page_title = 'Add Gallery Image';
$base_url = CONNECTMYUNI_BASE_URL;
$admin_url = CONNECTMYUNI_BASE_URL . 'frontend/admin/';

$error = '';
$success = '';
$service = new GalleryService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'alt_text' => trim($_POST['alt_text'] ?? ''),
            'category' => trim($_POST['category'] ?? 'general'),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ];

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $upload = (new MediaService())->store($_FILES['image'], 'gallery');
            if ($upload['key'] !== null) {
                $data['image_path'] = $upload['key'];
            } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = $upload['error'];
            }
        }

        if ($error === '') {
            try {
                $service->createImage([
                    'title' => $data['title'],
                    'image_path' => $data['image_path'] ?? null,
                    'alt_text' => $data['alt_text'],
                    'category' => $data['category'],
                    'sort_order' => $data['sort_order'],
                    'is_active' => 1,
                ]);
                $success = 'Gallery image uploaded successfully. <a href="' . $admin_url . 'gallery/">Back to gallery management</a>.';
            } catch (\Throwable $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<?php include __DIR__ . '/../components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Add Gallery Image</h1>
    <p>Add a new image to the gallery. It will appear alongside the existing hardcoded images.</p>
</div>

<div class="card">
    <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo Security::csrfInput(); ?>
        <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input class="form-control" type="text" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Alt Text <span class="req">*</span></label>
            <input class="form-control" type="text" name="alt_text" required value="<?php echo htmlspecialchars($_POST['alt_text'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Category</label>
            <select class="form-control" name="category">
                <option value="campus" <?php echo (($_POST['category'] ?? '') === 'campus') ? 'selected' : ''; ?>>Campus Life</option>
                <option value="events" <?php echo (($_POST['category'] ?? '') === 'events') ? 'selected' : ''; ?>>Events</option>
                <option value="graduation" <?php echo (($_POST['category'] ?? '') === 'graduation') ? 'selected' : ''; ?>>Graduation</option>
                <option value="airport" <?php echo (($_POST['category'] ?? '') === 'airport') ? 'selected' : ''; ?>>Airport/Departure</option>
                <option value="general" <?php echo (($_POST['category'] ?? '') === 'general') ? 'selected' : ''; ?>>General</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars($_POST['sort_order'] ?? '0'); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Image <span class="req">*</span></label>
            <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
            <p class="form-hint">Allowed: JPEG, PNG, WebP. Max size: 5 MB.</p>
        </div>
        <button type="submit" class="btn btn-primary">Upload Image</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>gallery/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>