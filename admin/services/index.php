<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;
AuthMiddleware::requireAuth();

require_once __DIR__ . '/../services/ServiceService.php';
use ConnectMyUni\Services\ServiceService;

$serviceService = new ServiceService();
$services = $serviceService->getAll();

$page_title = 'Manage Services';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Services</h1>
    <p>Manage platform services</p>
</div>

<div style="margin-bottom: 20px;">
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Service
    </a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Icon</th>
                    <th>Active</th>
                    <th>Sort Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--txt-muted);">
                            No services found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo $service['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($service['title']); ?></strong>
                                <?php if ($service['description']): ?>
                                    <br><small style="color: var(--txt-muted);"><?php echo htmlspecialchars(substr($service['description'], 0, 60)) . '...'; ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($service['icon_class']): ?>
                                    <i class="<?php echo htmlspecialchars($service['icon_class']); ?>" style="font-size: 1.2rem;"></i>
                                <?php else: ?>
                                    <span style="color: var(--txt-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($service['is_active']): ?>
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">Active</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $service['sort_order']; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $service['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete"
                                   onclick="return confirm('Delete this service?')">
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
