<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$countryId = (int) $_GET['id'];

require_once __DIR__ . '/../services/CountryService.php';
use ConnectMyUni\Services\CountryService;

$countryService = new CountryService();
$country = $countryService->getById($countryId);

if (!$country) {
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
    
    $countryService->delete($countryId);
    header('Location: index.php?deleted=1');
    exit;
}

$page_title = 'Delete Country';
include 'admin-header.php';
?>

<div class="page-heading">
    <h1>Delete Country</h1>
    <p>This action cannot be undone</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card" style="border-color: var(--danger);">
            <div class="card-body" style="padding: 30px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: var(--danger);"></i>
                </div>
                
                <h3 style="text-align: center; margin-bottom: 15px;">Are you sure?</h3>
                <p style="text-align: center; color: var(--txt-muted); margin-bottom: 30px;">
                    You are about to delete <strong>"<?php echo htmlspecialchars($country['name']); ?>"</strong>.<br>
                    This action cannot be undone.
                </p>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo ConnectMyUni\Config\Security::generateCsrfToken(); ?>">
                    
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Yes, Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</main>
<?php include '../footer.php'; ?>
