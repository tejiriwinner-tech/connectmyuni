<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;
AuthMiddleware::requireAuth();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$uniId = (int) $_GET['id'];

require_once __DIR__ . '/../services/UniversityService.php';
use ConnectMyUni\Services\UniversityService;
$universityService = new UniversityService();
$university = $universityService->getById($uniId);

if (!$university) {
    header('Location: index.php?error=notfound');
    exit;
}

require_once __DIR__ . '/../services/CountryService.php';
use ConnectMyUni\Services\CountryService;
$countryService = new CountryService();
$countries = $countryService->getAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/Security.php';
    use ConnectMyUni\Config\Security;
    
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $countryId = (int)($_POST['country_id'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $websiteUrl = trim($_POST['website_url'] ?? '');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        
        if (empty($name) || empty($countryId)) {
            $error = 'University name and country are required.';
        } else {
            $data = [
                'country_id' => $countryId,
                'name' => $name,
                'location' => $location,
                'description' => $description,
                'website_url' => $websiteUrl,
                'is_featured' => $isFeatured,
                'sort_order' => $sortOrder,
            ];
            
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                $uploadDir = __DIR__ . '/../../uploads/universities/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $_FILES['logo']['name']);
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $fileName)) {
                    $data['logo_path'] = '/uploads/universities/' . $fileName;
                    if (!empty($university['logo_path']) && file_exists(__DIR__ . '/../' . $university['logo_path'])) {
                        @unlink(__DIR__ . '/../' . $university['logo_path']);
                    }
                }
            }
            
            $universityService->update($uniId, $data);
            header('Location: index.php?saved=1');
            exit;
        }
    }
}

$page_title = 'Edit University';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Edit University</h1>
    <p>Update university details</p>
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
                <label class="form-label">University Name <span style="color: var(--danger);">*</span></label>
                <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($university['name']); ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Country <span style="color: var(--danger);">*</span></label>
                <select name="country_id" class="form-control" required>
                    <option value="">Select Country</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?php echo $country['id']; ?>" <?php echo $university['country_id'] == $country['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($country['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($university['location'] ?? ''); ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($university['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Website URL</label>
                        <input type="url" name="website_url" class="form-control" value="<?php echo htmlspecialchars($university['website_url'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?php echo $university['sort_order']; ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Current Logo</label>
                <?php if (!empty($university['logo_path']) && file_exists(__DIR__ . '/../' . $university['logo_path'])): ?>
                    <img src="<?php echo $university['logo_path']; ?>" alt="" style="max-width: 150px; border-radius: 8px; margin-bottom: 10px;">
                <?php else: ?>
                    <p style="color: var(--txt-muted);">No logo uploaded</p>
                <?php endif; ?>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Change Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_featured" class="form-check-input" style="width: 18px; height: 18px;" <?php echo $university['is_featured'] ? 'checked' : ''; ?>>
                    <span>Featured University</span>
                </label>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update University
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
