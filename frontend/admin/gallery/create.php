<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\GalleryRepository;
use ConnectMyUni\Services\MediaService;

AuthMiddleware::requireAuth();
$page_title = 'Add Gallery Image';
include __DIR__ . '/../components/admin-header.php';

$error   = '';
$success = '';
$repo    = new GalleryRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $data = [
            'title'      => trim($_POST['title'] ?? ''),
            'alt_text'   => trim($_POST['alt_text'] ?? ''),
            'category'   => trim($_POST['category'] ?? 'general'),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active'  => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if (empty($_FILES['image']['name'])) {
            $error = 'An image is required.';
        } else {
            $upload = (new MediaService())->store($_FILES['image'], 'gallery');
            if ($upload['key'] !== null) {
                $data['image_path'] = $upload['key'];
            } else {
                $error = $upload['error'];
            }
        }

        if ($error === '') {
            try {
                $repo->create($data);
                $success = 'Image added. <a href="' . $admin_url . 'gallery/">Back to list</a>';
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>Add Gallery Image</h1>
    <p>Add an image to the public gallery. Stored under storage/uploads/gallery/.</p>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<div class="card">
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo Security::csrfInput(); ?>
        <div class="form-group">
            <label class="form-label">Title</label>
            <input class="form-control" type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Image (JPEG / PNG / WEBP, max 5 MB) <span class="req">*</span></label>
            <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
        </div>
        <div class="form-group">
            <label class="form-label">Alt Text</label>
            <input class="form-control" type="text" name="alt_text" value="<?php echo htmlspecialchars($_POST['alt_text'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Category</label>
            <select class="form-control" name="category">
                <?php foreach (['campus', 'events', 'airport', 'graduation', 'general'] as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo (($_POST['category'] ?? 'general') === $cat) ? 'selected' : ''; ?>><?php echo ucfirst($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars((string) ($_POST['sort_order'] ?? 0)); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Active</label>
            <select class="form-control" name="is_active">
                <option value="1" <?php echo (($_POST['is_active'] ?? 1) == 1) ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo (($_POST['is_active'] ?? 1) == 0) ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Image</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>gallery/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>