<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';
use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

$id = (int) ($_GET['id'] ?? 0);
$service = new \ConnectMyUni\Services\GalleryService();
$image = $service->findById($id);

if (!$image) {
    echo '<div class="alert alert-danger">Gallery image not found.</div>';
    include __DIR__ . '/components/footer.php';
    exit;
}
?>
<?php include __DIR__ . '/components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Delete Gallery Image</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>Delete Gallery Image #<?php echo $image['id']; ?></h3>
    </div>
    <div class="card-body">
        <p>Are you sure you want to delete this gallery image?</p>
        <p>
            <strong>Title:</strong> <?php echo htmlspecialchars($image['title'] ?? ''); ?><br>
            <strong>Category:</strong> <?php echo htmlspecialchars($image['category'] ?? ''); ?><br>
            <strong>Status:</strong> <?php echo $image['is_active'] ? 'Active' : 'Inactive'; ?>
        </p>
        <form method="POST" action="">
            <?php echo Security::csrfInput(); ?>
            <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
            <button type="submit" class="btn btn-danger" name="confirm">Yes, Delete</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    if (!isset($_POST['id']) || !$_POST['id']) {
        $error = 'Invalid request.';
    } else {
        $service = new \ConnectMyUni\Services\GalleryService();
        $result = $service->deleteImage($_POST['id']);
        if ($result['deleted']) {
            $success = 'Gallery image deleted successfully.';
            echo '<meta http-equiv="refresh" content="2;url=' . $base_url . 'frontend/admin/gallery/" />';
            echo '<div class="alert alert-success" style="margin-top: 1rem;">' . htmlspecialchars($success) . ' Redirecting back to gallery management...</div>';
        } else {
            $error = 'Failed to delete gallery image. Please try again.';
        }
    }
}