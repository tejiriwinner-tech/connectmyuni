<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Config\Security;
use ConnectMyUni\Services\EventService;
use ConnectMyUni\Helpers\MediaResolver;

AuthMiddleware::requireAuth();
$page_title = 'Events';
include __DIR__ . '/../components/admin-header.php';

$service = new EventService();
$events  = [];
$dbError = null;
try {
    $events = $service->getAll();
} catch (\Throwable $e) {
    $dbError = $e->getMessage();
}
?>
<div class="page-heading">
    <h1>Events</h1>
    <p>Manage event listings. Event images are uploaded to storage/uploads/events/ and a storage-relative key saved to image_path.</p>
    <a class="btn btn-primary" href="<?php echo $admin_url; ?>events/create.php">+ New Event</a>
</div>

<?php if ($dbError): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($dbError); ?></div>
<?php elseif (empty($events)): ?>
    <div class="card"><p class="form-hint" style="padding:18px">No events yet. Create your first event.</p></div>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr><th></th><th>Title</th><th>Category</th><th>Date</th><th>Status</th><th style="width:150px"></th></tr>
            </thead>
            <tbody>
            <?php foreach ($events as $e): ?>
                <tr>
                    <td>
                        <?php $img = MediaResolver::url($e['image_path'] ?? ''); ?>
                        <?php if ($img !== ''): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="" style="width:64px;height:40px;object-fit:cover;border-radius:6px;display:block">
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($e['title'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($e['category'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($e['event_date'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($e['status'] ?? ''); ?></td>
                    <td>
                        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>events/edit.php?id=<?php echo (int) ($e['id'] ?? 0); ?>">Edit</a>
                        <form method="POST" action="<?php echo $admin_url; ?>events/delete.php" style="display:inline" onsubmit="return confirm('Delete this event?');">
                            <?php echo Security::csrfInput(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) ($e['id'] ?? 0); ?>">
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

