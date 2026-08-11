<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

require_once __DIR__ . '/../services/CountryService.php';
use ConnectMyUni\Services\CountryService;

$countryService = new CountryService();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/Security.php';
    use ConnectMyUni\Config\Security;
    
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $flagEmoji = trim($_POST['flag_emoji'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        
        if (empty($name)) {
            $error = 'Country name is required.';
        } else {
            $countryId = $countryService->create([
                'name' => $name,
                'slug' => $slug,
                'flag_emoji' => $flagEmoji,
                'description' => $description,
                'is_featured' => $isFeatured,
                'sort_order' => $sortOrder,
            ]);
            
            header('Location: index.php?saved=1');
            exit;
        }
    }
}

$page_title = 'Add Country';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Add Country</h1>
    <p>Create a new country entry</p>
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
                <label class="form-label">Country Name <span style="color: var(--danger);">*</span></label>
                <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">URL Slug</label>
                <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>">
                <small style="color: var(--txt-muted);">Leave empty to auto-generate from name</small>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Flag Emoji</label>
                <input type="text" name="flag_emoji" class="form-control" value="<?php echo htmlspecialchars($_POST['flag_emoji'] ?? ''); ?>" maxlength="2">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
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
                            <input type="checkbox" name="is_featured" class="form-check-input" style="width: 18px; height: 18px;" <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                            <span>Featured Country</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Country
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
