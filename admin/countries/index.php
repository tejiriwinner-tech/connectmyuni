<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

require_once __DIR__ . '/../services/CountryService.php';
use ConnectMyUni\Services\CountryService;

$countryService = new CountryService();
$countries = $countryService->getAll();

$page_title = 'Manage Countries';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Countries</h1>
    <p>Manage countries for university listings</p>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Country deleted successfully.
    </div>
<?php endif; ?>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Country saved successfully.
    </div>
<?php endif; ?>

<div style="margin-bottom: 20px;">
    <a href="countries/create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Country
    </a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Flag</th>
                    <th>Featured</th>
                    <th>Sort Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($countries)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--txt-muted);">
                            No countries found. <a href="countries/create.php">Create your first country</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($countries as $country): ?>
                        <tr>
                            <td><?php echo $country['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($country['name']); ?></strong>
                                <?php if (!empty($country['description'])): ?>
                                    <br><small style="color: var(--txt-muted);"><?php echo htmlspecialchars(substr($country['description'], 0, 60)) . (strlen($country['description']) > 60 ? '...' : ''); ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 1.5rem;"><?php echo htmlspecialchars($country['flag_emoji'] ?? '🌍'); ?></td>
                            <td>
                                <?php if ($country['is_featured']): ?>
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid #22c55e;">Yes</span>
                                <?php else: ?>
                                    <span class="badge" style="background: var(--surface2); color: var(--txt-muted);">No</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $country['sort_order']; ?></td>
                            <td>
                                <a href="countries/edit.php?id=<?php echo $country['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="countries/delete.php?id=<?php echo $country['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete"
                                   onclick="return confirm('Are you sure you want to delete this country?')">
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
