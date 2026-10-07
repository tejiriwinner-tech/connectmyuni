<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';
use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

$page_title = 'Gallery Management';
$base_url = CONNECTMYUNI_BASE_URL;
$admin_url = CONNECTMYUNI_BASE_URL . 'frontend/admin/';

$service = new \ConnectMyUni\Services\GalleryService();
$images = $service->getAll();
$activeCount = $service->countActive();
$totalCount = $service->countAll();
$categories = $service->countCategories();
?>
<?php include __DIR__ . '/../components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Gallery Management</h1>
    <p>Manage gallery images for the ConnectMyUni website. Add new images to supplement the existing hardcoded gallery.</p>
    <a class="btn btn-primary" href="<?php echo $admin_url; ?>gallery/create.php">+ Add New Image</a>
</div>

<div class="card">
    <div class="card-header">
        <h3>Gallery Overview</h3>
    </div>
    <div class="card-body">
        <p>Total images: <strong><?php echo $totalCount; ?></strong> | Active: <strong><?php echo $activeCount; ?></strong> | Inactive: <strong><?php echo $totalCount - $activeCount; ?></strong></p>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th style="width:150px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($images as $image): ?>
                    <tr>
                        <td>
                            <?php 
                            $img = \ConnectMyUni\Helpers\MediaResolver::url($image['image_path'] ?? '');
                            if ($img !== ''): ?>
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="" style="width:80px;height:60px;object-fit:cover;border-radius:6px;display:block">
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($image['title'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($image['category'] ?? 'general'); ?></td>
                        <td><?php echo !empty($image['is_active']) ? 'Active' : 'Inactive'; ?></td>
                        <td><?php echo (int)($image['sort_order'] ?? 0); ?></td>
                        <td>
                            <a class="btn btn-ghost" href="<?php echo $admin_url; ?>gallery/edit.php?id=<?php echo (int) ($image['id'] ?? 0); ?>">Edit</a>
                            <form method="POST" action="<?php echo $admin_url; ?>gallery/delete.php" style="display:inline" onsubmit="return confirm('Delete this image?');">
                                <?php echo Security::csrfInput(); ?>
                                <input type="hidden" name="id" value="<?php echo (int) ($image['id'] ?? 0); ?>">
                                <button type="submit" class="btn btn-ghost" style="color:var(--danger)">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card" style="margin-top:20px">
    <div class="section-label">Gallery Information</div>
    <div style="padding:18px">
        <p style="font-size:0.85rem;color:var(--txt-muted);margin-bottom:12px">
            <i class="fas fa-info-circle"></i> Images added here will be displayed on the public gallery page alongside the existing hardcoded images.
        </p>
        <p style="font-size:0.85rem;color:var(--txt-muted)">
            <i class="fas fa-shield-alt"></i> Hardcoded images in the frontend/assets/images/gallery/ folder cannot be deleted from this interface.
        </p>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>