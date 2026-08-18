<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';
use ConnectMyuni\Config\Security;
use ConnectMyuni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

$id = (int) ($_GET['id'] ?? 0);
$service = new \ConnectMyuni\Services\TestimonialService();
$testimonial = $service->findById($id);

if (!$testimonial) {
    echo '<div class="alert alert-danger">Testimonial not found.</div>';
    include __DIR__ . '/components/footer.php';
    exit;
}
?>
<?php include __DIR__ . '/components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Delete Testimonial</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>Delete Testimonial #<?php echo $testimonial['id']; ?></h3>
    </div>
    <div class="card-body">
        <p>Are you sure you want to delete this testimonial?</p>
        <p>
            <strong>Student:</strong> <?php echo htmlspecialchars($testimonial['student_name'] ?? ''); ?><br>
            <strong>University:</strong> <?php echo htmlspecialchars($testimonial['student_university'] ?? ''); ?><br>
            <strong>Rating:</strong> <?php echo number_format((float)($testimonial['rating'] ?? 5), 1, '.', ''); ?><br>
            <strong>Featured:</strong> <?php echo ($testimonial['is_featured'] ?? false) ? 'Yes' : 'No'; ?>
        </p>
        <form method="POST" action="">
            <?php echo Security::csrfInput(); ?>
            <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
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
        $service = new \ConnectMyuni\Services\TestimonialService();
        $result = $service->delete($_POST['id']);
        if ($result['deleted']) {
            $success = 'Testimonial deleted successfully.';
            echo '<meta http-equiv="refresh" content="2;url=' . $base_url . 'frontend/admin/testimonials/" />';
            echo '<div class="alert alert-success" style="margin-top: 1rem;">' . htmlspecialchars($success) . ' Redirecting back to testimonial management...</div>';
        } else {
            $error = 'Failed to delete testimonial. Please try again.';
        }
    }
}