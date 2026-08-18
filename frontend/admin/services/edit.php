<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\ServiceService;

AuthMiddleware::requireAuth();
$page_title = 'Edit Service';
include __DIR__ . '/../components/admin-header.php';

$service = new ServiceService();
$id      = (int) ($_GET['id'] ?? 0);
$svcRow  = null;
if ($id > 0) {
    $svcRow = $service->getById($id);
}
if (!$svcRow) {
    echo '<div class="card"><p class="form-hint" style="padding:18px">Service not found.</p></div>';
    include __DIR__ . '/../components/footer.php';
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $data = [
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'icon_class'  => trim($_POST['icon_class'] ?? ''),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['title'] === '') {
            $error = 'Title is required.';
        } elseif ($data['description'] === '') {
            $error = 'Description is required.';
        }

        if ($error === '') {
            try {
                if ($service->update($id, $data)) {
                    $success = 'Service updated.';
                    $svcRow  = $service->getById($id);
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>Edit Service</h1>
    <p>Update the service details.</p>
    <a class="btn btn-ghost" href="<?php echo $admin_url; ?>services/">Back</a>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

<div class="card">
    <form method="POST" action="">
        <?php echo Security::csrfInput(); ?>
        <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input class="form-control" type="text" name="title" required value="<?php echo htmlspecialchars($svcRow['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Description <span class="req">*</span></label>
            <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($svcRow['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Icon (Font Awesome class)</label>
            <input class="form-control" type="text" name="icon_class" placeholder="fa-university" value="<?php echo htmlspecialchars($svcRow['icon_class'] ?? ''); ?>">
            <p class="form-hint">Optional. Font Awesome icon class, e.g. fa-university, fa-passport, fa-book-open.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars((string) ($svcRow['sort_order'] ?? 0)); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Active</label>
            <select class="form-control" name="is_active">
                <option value="1" <?php echo (($svcRow['is_active'] ?? 1) == 1) ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo (($svcRow['is_active'] ?? 1) == 0) ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>services/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

