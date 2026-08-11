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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/Security.php';
    use ConnectMyUni\Config\Security;
    
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        header('Location: index.php?error=invalid_token');
        exit;
    }
    
    if (!empty($university['logo_path']) && file_exists(__DIR__ . '/../' . $university['logo_path'])) {
        @unlink(__DIR__ . '/../' . $university['logo_path']);
    }
    
    $universityService->delete($uniId);
    header('Location: index.php?deleted=1');
    exit;
}

$page_title = 'Delete University';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Delete University</h1>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card" style="border-color: var(--danger);">
            <div class="card-body" style="padding: 30px; text-align: center;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: var(--danger); margin-bottom: 20px;"></i>
                <h3>Are you sure?</h3>
                <p style="color: var(--txt-muted); margin: 20px 0;">
                    Delete <strong><?php echo htmlspecialchars($university['name']); ?></strong>? This cannot be undone.
                </p>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo ConnectMyUni\Config\Security::generateCsrfToken(); ?>">
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</main>
<?php include '../footer.php'; ?>
