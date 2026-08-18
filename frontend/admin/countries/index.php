<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Config\Security;
use ConnectMyUni\Services\CountryService;

AuthMiddleware::requireAuth();
$page_title = 'Countries';
include __DIR__ . '/../components/admin-header.php';

$service = new CountryService();
$countries = [];
$dbError = null;
try {
    $countries = $service->getAllWithUniversityCounts();
} catch (\Throwable $e) {
    $dbError = $e->getMessage();
}

// One-shot notice set by delete.php when a delete was blocked.
$flash = $_SESSION['admin_flash'] ?? '';
unset($_SESSION['admin_flash']);
?>
<div class="page-heading">
    <h1>Countries</h1>
    <p>Manage countries. Universities reference these countries; a country that still has universities cannot be deleted.</p>
    <a class="btn btn-primary" href="<?php echo $admin_url; ?>countries/create.php">+ New Country</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-warning"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flash); ?></div>
<?php endif; ?>
<?php if ($dbError): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($dbError); ?></div>
<?php elseif (empty($countries)): ?>
    <div class="card"><p class="form-hint" style="padding:18px">No countries yet. Create your first country.</p></div>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr><th>Flag</th><th>Name</th><th>Slug</th><th>Universities</th><th>Featured</th><th>Order</th><th style="width:150px"></th></tr>
            </thead>
            <tbody>
            <?php foreach ($countries as $c): ?>
                <tr>
                    <td><span style="font-size:1.25rem"><?php echo CountryService::flagEmoji((string) ($c['flag_emoji'] ?? '')); ?></span></td>
                    <td><?php echo htmlspecialchars($c['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($c['slug'] ?? ''); ?></td>
                    <td><?php echo (int) ($c['university_count'] ?? 0); ?></td>
                    <td>
                        <?php if (!empty($c['is_featured'])): ?>
                            <span class="badge" style="background:var(--primary)">Featured</span>
                        <?php else: ?>
                            <span class="badge" style="background:var(--txt-muted)">Standard</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo (int) ($c['sort_order'] ?? 0); ?></td>
                    <td>
                        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>countries/edit.php?id=<?php echo (int) ($c['id'] ?? 0); ?>">Edit</a>
                        <form method="POST" action="<?php echo $admin_url; ?>countries/delete.php" style="display:inline" onsubmit="return confirm('Delete this country?');">
                            <?php echo Security::csrfInput(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) ($c['id'] ?? 0); ?>">
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

