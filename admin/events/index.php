<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

require_once __DIR__ . '/../services/EventService.php';
use ConnectMyUni\Services\EventService;

$eventService = new EventService();
$events = $eventService->getAll();

$page_title = 'Manage Events';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Events</h1>
    <p>Manage all events on the platform</p>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Event deleted successfully.
    </div>
<?php endif; ?>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Event saved successfully.
    </div>
<?php endif; ?>

<div style="margin-bottom: 20px;">
    <a href="events/create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Event
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
                    <th>Date</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--txt-muted);">
                            No events found. <a href="events/create.php">Create your first event</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo $event['id']; ?></td>
                            <td>
                                <?php if (!empty($event['image_path']) && file_exists(__DIR__ . '/../' . $event['image_path'])): ?>
                                    <img src="<?php echo $event['image_path']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: var(--surface2); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="color: var(--txt-muted);"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                <?php if (strlen($event['description'] ?? '') > 50): ?>
                                    <br><small style="color: var(--txt-muted);"><?php echo htmlspecialchars(substr($event['description'], 0, 50)) . '...'; ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($event['date'])); ?></td>
                            <td>
                                <span class="badge" style="background: var(--surface2); color: var(--txt);">
                                    <?php echo htmlspecialchars(ucfirst($event['category'] ?? 'general')); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($event['status'] === 'published'): ?>
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid #22c55e;">Published</span>
                                <?php elseif ($event['status'] === 'draft'): ?>
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid #f59e0b;">Draft</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444;">Archived</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="events/edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="events/delete.php?id=<?php echo $event['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete"
                                   onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
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
