<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Config\Security;
use ConnectMyUni\Repositories\GalleryRepository;
use ConnectMyUni\Helpers\MediaResolver;

AuthMiddleware::requireAuth();
$page_title = 'Gallery';
include __DIR__ . '/../components/admin-header.php';

$repo    = new GalleryRepository();
$gallery = [];
$dbError = null;
try {
    $gallery = $repo->getAll();
} catch (\Throwable $e) {
    $dbError = $e->getMessage();
}
?>
<div class="page-heading">
    <h1>Gallery</h1>
    <p>Manage gallery images. Images are stored under storage/uploads/gallery/ with a storage-relative key in image_path.</p>
    <a class="btn btn-primary" href="<?php echo $admin_url; ?>gallery/create.php">+ Add Image</a>
</div>

<?php if ($dbError): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($dbError); ?></div>
<?php elseif (empty($gallery)): ?>
    <div class="card"><p class="form-hint" style="padding:18px">No gallery images yet.</p></div>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead><tr><th></th><th>Title</th><th>Category</th><th>Order</th><th>Active</th><th style="width:150px"></th></tr></thead>
            <tbody>
            <?php foreach ($gallery as $g): ?>
                <tr>
                    <td>
                        <?php $img = MediaResolver::url($g['image_path'] ?? ''); ?>
                        <?php if ($img !== ''): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="" style="width:64px;height:48px;object-fit:cover;border-radius:6px;display:block">
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($g['title'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($g['category'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars((string) ($g['sort_order'] ?? '')); ?></td>
                    <td><?php echo !empty($g['is_active']) ? 'Yes' : 'No'; ?></td>
                    <td>
                        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>gallery/edit.php?id=<?php echo (int) ($g['id'] ?? 0); ?>">Edit</a>
                        <form method="POST" action="<?php echo $admin_url; ?>gallery/delete.php" style="display:inline" onsubmit="return confirm('Delete this image?');">
                            <?php echo Security::csrfInput(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) ($g['id'] ?? 0); ?>">
                            <button type="submit" class="btn btn-ghost" style="color:var(--danger)">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>