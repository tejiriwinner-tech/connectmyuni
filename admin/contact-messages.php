<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;
AuthMiddleware::requireAuth();

require_once __DIR__ . '/../repositories/ContactMessageRepository.php';
use ConnectMyUni\Repositories\ContactMessageRepository;

$messageRepo = new ContactMessageRepository();
$messages = $messageRepo->getAll(50);
$totalMessages = $messageRepo->count();
$newMessages = $messageRepo->countByStatus('new');
$readMessages = $messageRepo->countByStatus('read');

$page_title = 'Contact Messages';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Contact Messages</h1>
    <p>Manage contact form submissions</p>
</div>

<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Status updated successfully.
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(26, 86, 219, 0.1); color: #1a56db;">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalMessages; ?></h3>
                <p>Total Messages</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                <i class="fas fa-envelope-open"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $newMessages; ?></h3>
                <p>New Messages</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $readMessages; ?></h3>
                <p>Read</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--txt-muted);">
                            No messages found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td><?php echo $message['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                <?php if ($message['phone']): ?>
                                    <br><small style="color: var(--txt-muted);"><?php echo htmlspecialchars($message['phone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($message['email']); ?></td>
                            <td><?php echo htmlspecialchars($message['subject']); ?></td>
                            <td>
                                <?php if ($message['status'] === 'new'): ?>
                                    <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid #22c55e;">New</span>
                                <?php elseif ($message['status'] === 'read'): ?>
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid #f59e0b;">Read</span>
                                <?php else: ?>
                                    <span class="badge" style="background: var(--surface2); color: var(--txt-muted);"><?php echo ucfirst($message['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" 
                                        onclick="viewMessage(<?php echo $message['id']; ?>, '<?php echo htmlspecialchars($message['name']); ?>', '<?php echo htmlspecialchars($message['email']); ?>', '<?php echo htmlspecialchars($message['subject']); ?>', '<?php echo htmlspecialchars($message['message']); ?>')"
                                        title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($message['status'] !== 'read'): ?>
                                    <a href="?mark_read=<?php echo $message['id']; ?>" class="btn btn-sm btn-warning" title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $message['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete"
                                   onclick="return confirm('Delete this message?')">
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

<!-- Message View Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalTitle">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>From:</strong> <span id="msgName"></span></p>
                <p><strong>Email:</strong> <span id="msgEmail"></span></p>
                <p><strong>Subject:</strong> <span id="msgSubject"></span></p>
                <hr>
                <p><strong>Message:</strong></p>
                <div id="msgContent" style="background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 300px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>
</div>

</main>

<style>
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.stat-content h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: var(--txt);
}

.stat-content p {
    margin: 0;
    font-size: 13px;
    color: var(--txt-muted);
}
</style>

<script>
function viewMessage(id, name, email, subject, message) {
    document.getElementById('messageModalTitle').textContent = 'Message #' + id;
    document.getElementById('msgName').textContent = name;
    document.getElementById('msgEmail').textContent = email;
    document.getElementById('msgSubject').textContent = subject;
    document.getElementById('msgContent').textContent = message;
    
    new bootstrap.Modal(document.getElementById('messageModal')).show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
