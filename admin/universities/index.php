<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;
AuthMiddleware::requireAuth();

require_once __DIR__ . '/../services/UniversityService.php';
use ConnectMyUni\Services\UniversityService;

$universityService = new UniversityService();
$universities = $universityService->getAll();

$page_title = 'Manage Universities';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Universities</h1>
    <p>Manage partner universities</p>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> University saved successfully.
    </div>
<?php endif; ?>

<div style="margin-bottom: 20px;">
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New University
    </a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>Country</th>
                    <th>Website</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($universities)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--txt-muted);">
                            No universities found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($universities as $uni): ?>
                        <tr>
                            <td><?php echo $uni['id']; ?></td>
                            <td>
                                <?php if (!empty($uni['logo_path']) && file_exists(__DIR__ . '/../' . $uni['logo_path'])): ?>
                                    <img src="<?php echo $uni['logo_path']; ?>" alt="" style="width: 40px; height: 40px; object-fit: contain;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: var(--surface2); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-university" style="color: var(--txt-muted);"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($uni['name']); ?></strong>
                                <?php if ($uni['location']): ?>
                                    <br><small style="color: var(--txt-muted);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($uni['location']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($uni['country_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($uni['website_url']): ?>
                                    <a href="<?php echo htmlspecialchars($uni['website_url']); ?>" target="_blank" class="btn btn-sm btn-info" title="Visit Website">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($uni['is_featured']): ?>
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid #22c55e;">Yes</span>
                                <?php else: ?>
                                    <span class="badge" style="background: var(--surface2); color: var(--txt-muted);">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $uni['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $uni['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete"
                                   onclick="return confirm('Delete this university?')">
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
