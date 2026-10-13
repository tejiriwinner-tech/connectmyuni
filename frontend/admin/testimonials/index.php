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
<?php include __DIR__ . '/../components/admin-header.php'; ?>

<div class="page-heading">
    <h1>Testimonial Management</h1>
    <p>Manage student testimonials for the ConnectMyUni website.</p>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <h3>Testimonial Overview</h3>
    </div>
    <div class="card-body">
        <p style="margin-bottom:16px;">Total testimonials: <strong><?php echo $totalCount; ?></strong> | Active: <strong><?php echo $activeCount; ?></strong></p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;">
            <div class="card" style="padding:14px;background:var(--surface2);">
                <div style="font-weight:700;font-size:0.85rem;margin-bottom:8px;color:var(--txt);">Ratings Distribution</div>
                <div style="font-size:0.82rem;color:var(--txt-muted);display:flex;flex-direction:column;gap:4px;">
                    <div><?php echo $ratingCounts[0]['cnt'] ?? 0; ?> &times; <span style="color:#dc3545;">1 Star</span></div>
                    <div><?php echo $ratingCounts[1]['cnt'] ?? 0; ?> &times; <span style="color:#fd7e14;">2 Stars</span></div>
                    <div><?php echo $ratingCounts[2]['cnt'] ?? 0; ?> &times; <span style="color:#ffc107;">3 Stars</span></div>
                    <div><?php echo $ratingCounts[3]['cnt'] ?? 0; ?> &times; <span style="color:#17a2b8;">4 Stars</span></div>
                    <div><?php echo $ratingCounts[4]['cnt'] ?? 0; ?> &times; <span style="color:#28a745;">5 Stars</span></div>
                </div>
            </div>
            <div class="card" style="padding:14px;background:var(--surface2);">
                <div style="font-weight:700;font-size:0.85rem;margin-bottom:8px;color:var(--txt);">Status Summary</div>
                <div style="font-size:0.82rem;color:var(--txt-muted);display:flex;flex-direction:column;gap:6px;">
                    <div>Active (featured): <strong style="color:var(--success);"><?php echo $activeCount; ?></strong></div>
                    <div>Inactive: <strong style="color:var(--txt-muted);"><?php echo ($totalCount - $activeCount); ?></strong></div>
                    <div>Total logged: <strong style="color:var(--txt);"><?php echo $totalCount; ?></strong></div>
                </div>
            </div>
            <div class="card" style="padding:14px;background:var(--surface2);display:flex;flex-direction:column;justify-content:center;gap:10px;">
                <div style="font-weight:700;font-size:0.85rem;color:var(--txt);">Actions</div>
                <a href="create.php" class="btn btn-primary" style="justify-content:center;">+ Add Testimonial</a>
            </div>
        </div>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden;margin-bottom:20px;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>All Testimonials</h3>
        <a href="create.php" class="btn btn-primary btn-sm">+ Add New</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($testimonials)): ?>
            <div class="alert alert-info" style="margin:20px;">No testimonials found. <a href="create.php">Create your first testimonial</a>.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table table-hover">
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