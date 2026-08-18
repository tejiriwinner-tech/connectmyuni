<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\ServiceService;

AuthMiddleware::requireAuth();
$page_title = 'New Service';
include __DIR__ . '/../components/admin-header.php';

$error   = '';
$success = '';
$service = new ServiceService();

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
                $service->create($data);
                $success = 'Service created. <a href="' . $admin_url . 'services/">Back to list</a>';
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>New Service</h1>
    <p>Create a service. Active services appear on the public Services page in sort order.</p>
</div>

<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<div class="card">
    <form method="POST" action="">
        <?php echo Security::csrfInput(); ?>
        <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input class="form-control" type="text" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Description <span class="req">*</span></label>
            <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Icon (Font Awesome class)</label>
            <input class="form-control" type="text" name="icon_class" placeholder="fa-university" value="<?php echo htmlspecialchars($_POST['icon_class'] ?? ''); ?>">
            <p class="form-hint">Optional. Font Awesome icon class, e.g. fa-university, fa-passport, fa-book-open.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars((string) ($_POST['sort_order'] ?? 0)); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Active</label>
            <select class="form-control" name="is_active">
                <option value="1" <?php echo (($_POST['is_active'] ?? 1) == 1) ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo (($_POST['is_active'] ?? 1) == 0) ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create Service</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>services/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

