<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';
use ConnectMyuni\Config\Security;
use ConnectMyuni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();
$csrfToken = Security::generateCsrfToken();
?>
<?php include __DIR__ . '/../components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Add Testimonial</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3>Add New Testimonial</h3>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="">
            <?php echo Security::csrfInput(); ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="mb-3">
                <label class="form-label">Student Name <span class="req">*</span></label>
                <input type="text" class="form-control" name="student_name" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">University</label>
                <input type="text" class="form-control" name="university">
            </div>

            <div class="mb-3">
                <label class="form-label">Country</label>
                <input type="text" class="form-control" name="country">
            </div>

            <div class="mb-3">
                <label class="form-label">Testimonial Text <span class="req">*</span></label>
                <textarea class="form-control" name="testimonial_text" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Rating <span class="req">*</span></label>
                <select class="form-control" name="rating">
                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Image (optional)</label>
                <input type="file" class="form-control" name="image">
                <small class="text-muted">Allowed: JPEG, PNG, WebP. Max size: 2 MB.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" name="sort_order" value="0">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" name="submit">Add Testimonial</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $service = new \ConnectMyuni\Services\TestimonialService();
        $upload = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = (new \ConnectMyuni\Helpers\MediaService())->store($_FILES['image'] ?? [], 'testimonials');
            if ($upload['error']) {
                $error = $upload['error'];
            } else {
                $data['avatar_path'] = 'testimonials/' . $upload['key'];
            }
        }
        $data = [
            'student_name' => $_POST['student_name'] ?? '',
            'university' => $_POST['university'] ?? null,
            'country' => $_POST['country'] ?? null,
            'testimonial_text' => $_POST['testimonial_text'] ?? '',
            'rating' => ($_POST['rating'] ?? 5),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'sort_order' => ($_POST['sort_order'] ?? 0),
        ];
        if (isset($upload['error'])) {
            // keep error set
        } else {
            $r = $service->create($data);
            if ($r) {
                $success = 'Testimonial added successfully. <a href="index.php">Back to testimonial management</a>.';
            } else {
                $error = 'Database error while saving testimonial.';
            }
        }
    }
}