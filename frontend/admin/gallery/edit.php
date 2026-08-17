<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\GalleryRepository;
use ConnectMyUni\Services\MediaService;
use ConnectMyUni\Helpers\MediaResolver;

AuthMiddleware::requireAuth();
$page_title = 'Edit Gallery Image';
include __DIR__ . '/../components/admin-header.php';

$repo = new GalleryRepository();
$id   = (int) ($_GET['id'] ?? 0);
$item = null;
if ($id > 0) {
    $item = $repo->findById($id);
}
if (!$item) {
    echo '<div class="card"><p class="form-hint" style="padding:18px">Image not found.</p></div>';
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
            'title'      => trim($_POST['title'] ?? ''),
            'alt_text'   => trim($_POST['alt_text'] ?? ''),
            'category'   => trim($_POST['category'] ?? 'general'),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active'  => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if (!empty($_FILES['image']['name'])) {
            $upload = (new MediaService())->store($_FILES['image'], 'gallery');
            if ($upload['key'] !== null) {
                $data['image_path'] = $upload['key'];
            } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = $upload['error'];
            }
        } else {
            $data['image_path'] = $item['image_path'] ?? null;
        }

        if ($error === '') {
            try {
                if ($repo->update($id, $data)) {
                    $success = 'Image updated.';
                    $item = $repo->findById($id);
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>Edit Gallery Image</h1>
    <p>Update the gallery image. The current file is kept unless a new one is uploaded.</p>
    <a class="btn btn-ghost" href="<?php echo $admin_url; ?>gallery/">Back</a>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

<div class="card">
    <?php $img = MediaResolver::url($item['image_path'] ?? ''); if ($img !== ''): ?>
        <img src="<?php echo htmlspecialchars($img); ?>" alt="" style="max-width:240px;max-height:150px;object-fit:cover;border-radius:8px;margin-bottom:16px;display:block">
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo Security::csrfInput(); ?>
        <div class="form-group">
            <label class="form-label">Title</label>
            <input class="form-control" type="text" name="title" value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Image (optional — leave empty to keep)</label>
            <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="form-group">
            <label class="form-label">Alt Text</label>
            <input class="form-control" type="text" name="alt_text" value="<?php echo htmlspecialchars($item['alt_text'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Category</label>
            <select class="form-control" name="category">
                <?php foreach (['campus', 'events', 'airport', 'graduation', 'general'] as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo (($item['category'] ?? 'general') === $cat) ? 'selected' : ''; ?>><?php echo ucfirst($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars((string) ($item['sort_order'] ?? 0)); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Active</label>
            <select class="form-control" name="is_active">
                <option value="1" <?php echo (($item['is_active'] ?? 1) == 1) ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo (($item['is_active'] ?? 1) == 0) ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>gallery/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>