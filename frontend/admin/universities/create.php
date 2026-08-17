<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\CountryService;
use ConnectMyUni\Services\UniversityService;
use ConnectMyUni\Services\MediaService;

AuthMiddleware::requireAuth();
$page_title = 'New University';
include __DIR__ . '/../components/admin-header.php';

$error     = '';
$success   = '';
$service   = new UniversityService();
$countries = [];
try {
    $countries = (new CountryService())->getAll();
} catch (\Throwable $e) {
    $error = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'country_id'  => (int) ($_POST['country_id'] ?? 0),
            'slug'        => trim($_POST['slug'] ?? ''),
            'location'    => trim($_POST['location'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'website_url' => trim($_POST['website_url'] ?? ''),
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ];

        if (empty($data['name']) || $data['country_id'] < 1) {
            $error = 'Name and country are required.';
        }

        // Optional university logo upload.
        if (!empty($_FILES['logo']['name'])) {
            $upload = (new MediaService())->store($_FILES['logo'], 'universities');
            if ($upload['key'] !== null) {
                $data['logo_path'] = $upload['key'];
            } elseif ($_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = $upload['error'];
            }
        }

        if ($error === '') {
            try {
                $service->create($data);
                $success = 'University created. <a href="' . $admin_url . 'universities/">Back to list</a>';
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>New University</h1>
    <p>Add a partner university. The optional logo is stored under storage/uploads/universities/.</p>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<div class="card">
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo Security::csrfInput(); ?>
        <?php
        $aiContentType  = 'university';
        $aiFieldMap     = [
            'name'        => 'name',
            'description' => 'description',
            'location'    => 'location',
        ];
        $aiButtonLabel  = 'Generate University Content with AI';
        include __DIR__ . '/../components/ai-content-generator.php';
        ?>
        <div class="form-group">
            <label class="form-label">Name <span class="req">*</span></label>
            <input class="form-control" type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Country <span class="req">*</span></label>
            <select class="form-control" name="country_id" required>
                <option value="">-- Select country --</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?php echo (int) $c['id']; ?>" <?php echo ((int) ($_POST['country_id'] ?? 0)) === (int) $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name'] ?? ''); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Slug (optional â€” auto-generated)</label>
            <input class="form-control" type="text" name="slug" value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Location</label>
            <input class="form-control" type="text" name="location" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Website URL</label>
            <input class="form-control" type="url" name="website_url" value="<?php echo htmlspecialchars($_POST['website_url'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Logo (JPEG / PNG / WEBP, max 5 MB)</label>
            <input class="form-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
            <p class="form-hint">Optional. Saved as a storage-relative key in logo_path.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Featured</label>
            <select class="form-control" name="is_featured">
                <option value="0" <?php echo (($_POST['is_featured'] ?? 0) == 0) ? 'selected' : ''; ?>>No</option>
                <option value="1" <?php echo (($_POST['is_featured'] ?? 0) == 1) ? 'selected' : ''; ?>>Yes</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars((string) ($_POST['sort_order'] ?? 0)); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Create University</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>universities/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

