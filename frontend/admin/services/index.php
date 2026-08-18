<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Config\Security;
use ConnectMyUni\Services\ServiceService;

AuthMiddleware::requireAuth();
$page_title = 'Services';
include __DIR__ . '/../components/admin-header.php';

$service = new ServiceService();
$services = [];
$dbError = null;
try {
    $services = $service->getAll();
} catch (\Throwable $e) {
    $dbError = $e->getMessage();
}
?>
<div class="page-heading">
    <h1>Services</h1>
    <p>Manage the services shown on the public Services page. Active services appear publicly in sort order.</p>
    <a class="btn btn-primary" href="<?php echo $admin_url; ?>services/create.php">+ New Service</a>
</div>

<?php if ($dbError): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($dbError); ?></div>
<?php elseif (empty($services)): ?>
    <div class="card"><p class="form-hint" style="padding:18px">No services yet. Create your first service.</p></div>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr><th>Icon</th><th>Title</th><th>Slug</th><th>Order</th><th>Status</th><th style="width:150px"></th></tr>
            </thead>
            <tbody>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td>
                        <?php if (!empty($s['icon_class'])): ?>
                            <i class="fas <?php echo htmlspecialchars($s['icon_class']); ?>" style="font-size:1.25rem;color:var(--primary)"></i>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($s['title'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s['slug'] ?? ''); ?></td>
                    <td><?php echo (int) ($s['sort_order'] ?? 0); ?></td>
                    <td>
                        <?php if (!empty($s['is_active'])): ?>
                            <span class="badge" style="background:var(--success)">Active</span>
                        <?php else: ?>
                            <span class="badge" style="background:var(--txt-muted)">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>services/edit.php?id=<?php echo (int) ($s['id'] ?? 0); ?>">Edit</a>
                        <form method="POST" action="<?php echo $admin_url; ?>services/delete.php" style="display:inline" onsubmit="return confirm('Delete this service?');">
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
