<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;
AuthMiddleware::requireAuth();

require_once __DIR__ . '/../services/ServiceService.php';
use ConnectMyUni\Services\ServiceService;

$serviceService = new ServiceService();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/Security.php';
    use ConnectMyUni\Config\Security;
    
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $iconClass = trim($_POST['icon_class'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($title)) {
            $error = 'Service title is required.';
        } else {
            $serviceService->create([
                'title' => $title,
                'description' => $description,
                'icon_class' => $iconClass,
                'sort_order' => $sortOrder,
                'is_active' => $isActive,
            ]);
            
            header('Location: index.php?saved=1');
            exit;
        }
    }
}

$page_title = 'Add Service';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Add Service</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding: 30px;">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo ConnectMyUni\Config\Security::generateCsrfToken(); ?>">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Service Title <span style="color: var(--danger);">*</span></label>
                <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Icon Class (Font Awesome)</label>
                <input type="text" name="icon_class" class="form-control" placeholder="fas fa-star" value="<?php echo htmlspecialchars($_POST['icon_class'] ?? ''); ?>">
                <small style="color: var(--txt-muted);">Example: fas fa-star, fas fa-graduation-cap</small>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?php echo htmlspecialchars($_POST['sort_order'] ?? 0); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 35px;">
                            <input type="checkbox" name="is_active" class="form-check-input" style="width: 18px; height: 18px;" checked>
                            <span>Active</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Service
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</main>
<?php include '../footer.php'; ?>
