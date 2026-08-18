<?php
// Admin Enquiry Inbox — list, view, and manage contact_messages.
// All state-changing actions run BEFORE any HTML output so header() redirects
// are safe (mirrors the public contact.php pattern).
require_once __DIR__ . '/../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\ContactMessageService;

AuthMiddleware::requireAuth();

$service = new ContactMessageService();
$error = '';
$flash = $_SESSION['admin_flash'] ?? '';
unset($_SESSION['admin_flash']);

// ── State-changing actions (status update / delete) — POST + CSRF only ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['admin_flash'] = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $id     = (int) ($_POST['id'] ?? 0);
        try {
            if ($action === 'status' && $id > 0) {
                $status = (string) ($_POST['status'] ?? '');
                $_SESSION['admin_flash'] = $service->updateStatus($id, $status)
                    ? 'Enquiry status updated to "' . htmlspecialchars($status) . '".'
                    : 'Could not update enquiry status.';
            } elseif ($action === 'delete' && $id > 0) {
                $_SESSION['admin_flash'] = $service->delete($id) ? 'Enquiry deleted.' : 'Could not delete enquiry.';
            }
        } catch (\Throwable $e) {
            error_log('Enquiry admin action failed: ' . $e->getMessage());
            $_SESSION['admin_flash'] = 'Something went wrong. Please try again.';
        }
    }
    header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/contact-messages.php');
    exit;
}

// ── Data loading ──
$viewId = (int) ($_GET['view'] ?? 0);
$enquiry = null;
$enquiries = [];
try {
    if ($viewId > 0) {
        $enquiry = $service->findById($viewId);
        if ($enquiry && ($enquiry['status'] ?? '') === 'new') {
            $service->updateStatus($viewId, 'read'); // auto-mark read when opened
        }
    } else {
        $enquiries = $service->getAll(200, 0);
    }
} catch (\Throwable $e) {
    error_log('Enquiry admin load failed: ' . $e->getMessage());
    $error = 'Could not load enquiries. Please try again later.';
}

$statusLabel = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'closed' => 'Closed'];
$statusClass = ['new' => 'warning', 'read' => 'secondary', 'replied' => 'success', 'closed' => 'dark'];

$page_title = 'Enquiries';
include __DIR__ . '/components/admin-header.php';
?><?php if ($flash): ?>
<div class="alert alert-info"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($flash); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($enquiry): ?>
    <div class="page-heading d-flex justify-content-between align-items-center">
        <div>
            <h1>Enquiry #<?php echo (int) $enquiry['id']; ?></h1>
            <p>Submitted <?php echo htmlspecialchars(date('j M Y, g:i A', strtotime($enquiry['created_at'] ?? 'now'))); ?></p>
        </div>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>contact-messages.php">← Back to Enquiries</a>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($enquiry['name'] ?? ''); ?></dd>

                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9"><a href="mailto:<?php echo htmlspecialchars($enquiry['email'] ?? ''); ?>"><?php echo htmlspecialchars($enquiry['email'] ?? ''); ?></a></dd>

                <dt class="col-sm-3">Phone</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($enquiry['phone'] ?? '—'); ?></dd>

                <dt class="col-sm-3">Subject</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($enquiry['subject'] ?? ''); ?></dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span class="badge bg-<?php echo $statusClass[$enquiry['status'] ?? 'new'] ?? 'secondary'; ?>">
                        <?php echo $statusLabel[$enquiry['status'] ?? 'new'] ?? ucfirst($enquiry['status'] ?? 'new'); ?>
                    </span>
                </dd>

                <dt class="col-sm-3">Message</dt>
                <dd class="col-sm-9"><div class="p-3 bg-light rounded"><?php echo nl2br(htmlspecialchars($enquiry['message'] ?? '')); ?></div></dd>
            </dl>

            <hr>
            <h6>Update Status</h6>
            <form method="POST" action="" class="d-flex gap-2 align-items-center flex-wrap">
                <?php echo Security::csrfInput(); ?>
                <input type="hidden" name="action" value="status">
                <input type="hidden" name="id" value="<?php echo (int) $enquiry['id']; ?>">
                <select class="form-control form-control-sm" name="status" style="max-width:200px;">
                    <?php foreach ($statusLabel as $val => $label): ?>
                        <option value="<?php echo $val; ?>" <?php echo ($enquiry['status'] ?? '') === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Update</button>
            </form>

            <form method="POST" action="" class="mt-3" onsubmit="return confirm('Delete this enquiry permanently?');">
                <?php echo Security::csrfInput(); ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo (int) $enquiry['id']; ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Delete Enquiry</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="page-heading">
        <h1>Enquiry Inbox</h1>
        <p>Enquiries submitted through the public contact / enquire forms.</p>
    </div>
    <?php if (empty($enquiries)): ?>
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                No enquiries yet. Enquiries submitted via the public forms will appear here.
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enquiries as $row): ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['phone'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($row['subject'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(date('j M Y', strtotime($row['created_at'] ?? 'now'))); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $statusClass[$row['status'] ?? 'new'] ?? 'secondary'; ?>">
                                    <?php echo $statusLabel[$row['status'] ?? 'new'] ?? ucfirst($row['status'] ?? 'new'); ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="<?php echo $admin_url; ?>contact-messages.php?view=<?php echo (int) $row['id']; ?>">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/components/footer.php'; ?>