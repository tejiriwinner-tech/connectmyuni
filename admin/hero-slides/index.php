<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;
AuthMiddleware::requireAuth();

require_once __DIR__ . '/../services/HeroSlideService.php';
use ConnectMyUni\Services\HeroSlideService;

$slideService = new HeroSlideService();
$slides = $slideService->getAll();

$page_title = 'Manage Hero Slides';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Hero Slides</h1>
    <p>Manage homepage carousel slides</p>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Slide deleted successfully.
    </div>
<?php endif; ?>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Slide saved successfully.
    </div>
<?php endif; ?>

<div style="margin-bottom: 20px;">
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Slide
    </a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Subtitle</th>
                    <th>Order</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($slides)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--txt-muted);">
                            No slides found. <a href="create.php">Create your first slide</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($slides as $slide): ?>
                        <tr>
                            <td><?php echo $slide['id']; ?></td>
                            <td>
                                <?php if (!empty($slide['image_path']) && file_exists(__DIR__ . '/../' . $slide['image_path'])): ?>
                                    <img src="<?php echo $slide['image_path']; ?>" alt="" style="width: 120px; height: 60px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 120px; height: 60px; background: var(--surface2); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="color: var(--txt-muted);"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($slide['title']); ?></strong>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(substr($slide['subtitle'] ?? '', 0, 50)) . (strlen($slide['subtitle'] ?? '') > 50 ? '...' : ''); ?>
                            </td>
                            <td><?php echo $slide['sort_order']; ?></td>
                            <td>
                                <?php if ($slide['is_active']): ?>
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">Yes</span>
                                <?php else: ?>
                                    <span class="badge" style="background: var(--surface2); color: var(--txt-muted);">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $slide['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $slide['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete"
                                   onclick="return confirm('Delete this slide?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
<?php include '../footer.php'; ?>
