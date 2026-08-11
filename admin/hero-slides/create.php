<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;
AuthMiddleware::requireAuth();

require_once __DIR__ . '/../services/HeroSlideService.php';
use ConnectMyUni\Services\HeroSlideService;

$slideService = new HeroSlideService();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/Security.php';
    use ConnectMyUni\Config\Security;
    
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $ctaText = trim($_POST['cta_text'] ?? '');
        $ctaUrl = trim($_POST['cta_url'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($title)) {
            $error = 'Slide title is required.';
        } else {
            $imagePath = null;
            $mobileImagePath = null;
            
            $uploadDir = __DIR__ . '/../../uploads/hero/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $fileName = 'desktop_' . time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $_FILES['image']['name']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                    $imagePath = '/uploads/hero/' . $fileName;
                }
            }
            
            if (isset($_FILES['mobile_image']) && $_FILES['mobile_image']['error'] === 0) {
                $fileName = 'mobile_' . time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $_FILES['mobile_image']['name']);
                if (move_uploaded_file($_FILES['mobile_image']['tmp_name'], $uploadDir . $fileName)) {
                    $mobileImagePath = '/uploads/hero/' . $fileName;
                }
            }
            
            if (!$imagePath) {
                $error = 'Desktop image is required.';
            } else {
                $slideService->create([
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'cta_text' => $ctaText,
                    'cta_url' => $ctaUrl,
                    'image_path' => $imagePath,
                    'mobile_image_path' => $mobileImagePath,
                    'sort_order' => $sortOrder,
                    'is_active' => $isActive,
                ]);
                
                header('Location: index.php?saved=1');
                exit;
            }
        }
    }
}

$page_title = 'Add Hero Slide';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Add Hero Slide</h1>
    <p>Create a new homepage carousel slide</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding: 30px;">
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo ConnectMyUni\Config\Security::generateCsrfToken(); ?>">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Heading <span style="color: var(--danger);">*</span></label>
                <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Subtitle</label>
                <textarea name="subtitle" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['subtitle'] ?? ''); ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Button Text</label>
                        <input type="text" name="cta_text" class="form-control" value="<?php echo htmlspecialchars($_POST['cta_text'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Button URL</label>
                        <input type="url" name="cta_url" class="form-control" value="<?php echo htmlspecialchars($_POST['cta_url'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Desktop Image <span style="color: var(--danger);">*</span></label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
                <small style="color: var(--txt-muted);">Recommended: 1920x800px, max 2MB</small>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Mobile Image (Optional)</label>
                <input type="file" name="mobile_image" class="form-control" accept="image/*">
                <small style="color: var(--txt-muted);">Recommended: 750x1000px</small>
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
                    <i class="fas fa-save"></i> Create Slide
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
