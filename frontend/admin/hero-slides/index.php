<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Config\Security;
use ConnectMyUni\Services\HeroSlideService;
use ConnectMyUni\Helpers\MediaResolver;

AuthMiddleware::requireAuth();
$page_title = 'Hero Slides';
include __DIR__ . '/../components/admin-header.php';

$service = new HeroSlideService();
$slides  = [];
$dbError = null;
try {
    $slides = $service->getAll();
} catch (\Throwable $e) {
    $dbError = $e->getMessage();
}
?>
<div class="page-heading">
    <h1>Hero Slides</h1>
    <p>Manage homepage hero slides (desktop + mobile images). Images stored under storage/uploads/hero/.</p>
    <a class="btn btn-primary" href="<?php echo $admin_url; ?>hero-slides/create.php">+ New Slide</a>
</div>

<?php if ($dbError): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($dbError); ?></div>
<?php elseif (empty($slides)): ?>
    <div class="card"><p class="form-hint" style="padding:18px">No hero slides yet.</p></div>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead><tr><th>Desktop</th><th>Mobile</th><th>Title</th><th>Order</th><th>Active</th><th style="width:150px"></th></tr></thead>
            <tbody>
            <?php foreach ($slides as $s): ?>
                <tr>
                    <td>
                        <?php $img = MediaResolver::url($s['image_path'] ?? ''); ?>
                        <?php if ($img !== ''): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="" style="width:96px;height:48px;object-fit:cover;border-radius:6px;display:block">
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <?php $m = MediaResolver::url($s['mobile_image_path'] ?? ''); ?>
                        <?php if ($m !== ''): ?>
                            <img src="<?php echo htmlspecialchars($m); ?>" alt="" style="width:40px;height:48px;object-fit:cover;border-radius:6px;display:block">
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($s['title'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars((string) ($s['sort_order'] ?? '')); ?></td>
                    <td><?php echo !empty($s['is_active']) ? 'Yes' : 'No'; ?></td>
                    <td>
                        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>hero-slides/edit.php?id=<?php echo (int) ($s['id'] ?? 0); ?>">Edit</a>
                        <form method="POST" action="<?php echo $admin_url; ?>hero-slides/delete.php" style="display:inline" onsubmit="return confirm('Delete this slide?');">
                            <?php echo Security::csrfInput(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) ($s['id'] ?? 0); ?>">
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

