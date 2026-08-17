<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Config\Security;
use ConnectMyUni\Services\UniversityService;
use ConnectMyUni\Helpers\MediaResolver;

AuthMiddleware::requireAuth();
$page_title = 'Universities';
include __DIR__ . '/../components/admin-header.php';

$service = new UniversityService();
$unis    = [];
$dbError = null;
try {
    $unis = $service->getAll();
} catch (\Throwable $e) {
    $dbError = $e->getMessage();
}
?>
<div class="page-heading">
    <h1>Universities</h1>
    <p>Manage partner universities. Logos are uploaded to storage/uploads/universities/ and saved to logo_path.</p>
    <a class="btn btn-primary" href="<?php echo $admin_url; ?>universities/create.php">+ New University</a>
</div>

<?php if ($dbError): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($dbError); ?></div>
<?php elseif (empty($unis)): ?>
    <div class="card"><p class="form-hint" style="padding:18px">No universities yet.</p></div>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead><tr><th></th><th>Name</th><th>Country</th><th>Featured</th><th style="width:150px"></th></tr></thead>
            <tbody>
            <?php foreach ($unis as $u): ?>
                <tr>
                    <td>
                        <?php $logo = MediaResolver::url($u['logo_path'] ?? ''); ?>
                        <?php if ($logo !== ''): ?>
                            <img src="<?php echo htmlspecialchars($logo); ?>" alt="" style="width:48px;height:48px;object-fit:contain;border-radius:6px;display:block">
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($u['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($u['country_name'] ?? ''); ?></td>
                    <td><?php echo !empty($u['is_featured']) ? 'Yes' : 'No'; ?></td>
                    <td>
                        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>universities/edit.php?id=<?php echo (int) ($u['id'] ?? 0); ?>">Edit</a>
                        <form method="POST" action="<?php echo $admin_url; ?>universities/delete.php" style="display:inline" onsubmit="return confirm('Delete this university?');">
                            <?php echo Security::csrfInput(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) ($u['id'] ?? 0); ?>">
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

