<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';
use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

$service = new \ConnectMyUni\Services\GalleryService();
$images = $service->getAll();
$activeCount = $service->countActive();
$totalCount = $service->countAll();
$categories = $service->countCategories();
?>
<?php include __DIR__ . '/components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Gallery Management</h1>
    <p>Manage gallery images for the ConnectMyUni website.</p>
</div>

<div class="card">
    <div class="card-header">
        <h3>Gallery Overview</h3>
    </div>
    <div class="card-body">
        <p>Total images: <strong><?php echo $totalCount; ?></strong> | Active: <strong><?php echo $activeCount; ?></strong> | Inactive: <strong><?php echo $totalCount - $activeCount; ?></strong></p>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($images as $i => $image): ?>
                    <tr>
                        <td><?php echo ($i + 1); ?></td>
                        <td>
                            <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($image['title'] ?? ''); ?>" readonly>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($image['category'] ?? 'general'); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $image['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $image['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo (int)($image['sort_order'] ?? 0); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="edit.php?id=<?php echo $image['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                <a href="delete.php?id=<?php echo $image['id']; ?>" class="btn btn-outline-danger">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3>Toggle Active/Inactive</h3>
    </div>
    <div class="card-body">
        <p>Change the active status of images to show/hide them on the public gallery.</p>
        <p class="text-muted small">Note: Inactive images are hidden from the public gallery but remain in the database.</p>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>