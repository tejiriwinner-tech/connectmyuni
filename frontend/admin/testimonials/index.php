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
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($t['student_country'] ?? ''); ?></span>
                        </td>
                        <td>
                            <?php $r = ($t['rating'] ?? 5); ?>
                            <span class="badge bg-<?php echo ($r >= 4) ? 'success' : (($r <= 2) ? 'danger' : 'warning'); ?>">
                                <?php echo number_format((float)$r, 1, '.', ''); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo ($t['is_featured'] ?? false) ? 'success' : 'secondary'; ?>">
                                <?php echo ($t['is_featured'] ?? false) ? 'Yes' : 'No'; ?>
                            </span>
                        </td>
                        <td><?php echo (int)($t['sort_order'] ?? 0); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="edit.php?id=<?php echo $t['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                <a href="delete.php?id=<?php echo $t['id']; ?>" class="btn btn-outline-danger">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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

<?php include __DIR__ . '/components/footer.php'; ?>