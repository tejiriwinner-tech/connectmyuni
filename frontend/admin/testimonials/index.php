<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';
use ConnectMyuni\Config\Security;
use ConnectMyuni\Middleware\AuthMiddleware;

AuthMiddleware::requireAuth();

$service = new \ConnectMyuni\Services\TestimonialService();
$testimonials = $service->getAll();
$activeCount = $service->countActive();
$totalCount = $service->countAll();
$ratingCounts = $service->countByRating();
$csrfToken = Security::generateCsrfToken();
?>
<?php include __DIR__ . '/components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Testimonial Management</h1>
    <p>Manage student testimonials for the ConnectMyUni website.</p>
</div>

<div class="card">
    <div class="card-header">
        <h3>Testimonial Overview</h3>
    </div>
    <div class="card-body">
        <p>Total testimonials: <strong><?php echo $totalCount; ?></strong> | Active: <strong><?php echo $activeCount; ?></strong></p>

        <div class="row">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-header">Ratings Distribution</div>
                    <div class="card-body">
                        <p><?php echo $ratingCounts[0]['cnt'] ?? 0; ?> - <span style="color:#dc3545;">1 Star</span></p>
                        <p><?php echo $ratingCounts[1]['cnt'] ?? 0; ?> - <span style="color:#fd7e14;">2 Stars</span></p>
                        <p><?php echo $ratingCounts[2]['cnt'] ?? 0; ?> - <span style="color:#ffc107;">3 Stars</span></p>
                        <p><?php echo $ratingCounts[3]['cnt'] ?? 0; ?> - <span style="color:#17a2b8;">4 Stars</span></p>
                        <p><?php echo $ratingCounts[4]['cnt'] ?? 0; ?> - <span style="color:#28a745;">5 Stars</span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-header">Status Summary</div>
                    <div class="card-body">
                        <p>Active (featured): <strong><?php echo $activeCount; ?></strong></p>
                        <p>Inactive: <strong><?php echo ($totalCount - $activeCount); ?></strong></p>
                        <p>Total: <strong><?php echo $totalCount; ?></strong></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-header">Actions</div>
                    <div class="card-body">
                        <a href="create.php" class="btn btn-primary w-100 mb-2">Add Testimonial</a>
                        <a href="edit.php" class="btn btn-secondary w-100 mb-2">Edit Testimonial</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3>Testimonial List</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>University</th>
                        <th>Country</th>
                        <th>Rating</th>
                        <th>Featured</th>
                        <th>Sort Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($testimonials as $i => $t): ?>
                    <tr>
                        <td><?php echo ($i + 1); ?></td>
                        <td>
                            <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($t['student_name'] ?? ''); ?>" readonly>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($t['student_university'] ?? ''); ?></span>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalCount; ?></div>
                    <div class="stat-label">Total Testimonials</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $activeCount; ?></div>
                    <div class="stat-label">Active Testimonials</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalCount - $activeCount; ?></div>
                    <div class="stat-label">Inactive Testimonials</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $ratingCounts[5] ?? 0; ?></div>
                    <div class="stat-label">5-Star Testimonials</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>All Testimonials</h3>
        <a href="create.php" class="btn btn-primary btn-sm">Add New Testimonial</a>
    </div>
    <div class="card-body">
        <?php if (empty($testimonials)): ?>
            <div class="alert alert-info">No testimonials found. <a href="create.php">Create your first testimonial</a>.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>University</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($testimonials as $testimonial): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($testimonial['student_name'] ?? ''); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($testimonial['student_title'] ?? ''); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($testimonial['university_name'] ?? ''); ?></td>
                                <td>
                                    <?php
                                    $rating = (int) ($testimonial['rating'] ?? 5);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-muted"></i>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($testimonial['is_active'])): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($testimonial['is_featured'])): ?>
                                        <span class="badge bg-warning">Featured</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3>Toggle Featured Status</h3>
    </div>
    <div class="card-body">
        <p>Change the featured status of testimonials to highlight them on the public section.</p>
        <p class="text-muted small">Featured testimonials appear in the featured section of the public page.</p>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>