<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\CountryService;
use ConnectMyUni\Services\UniversityService;
use ConnectMyUni\Services\MediaService;
use ConnectMyUni\Helpers\MediaResolver;

AuthMiddleware::requireAuth();
$page_title = 'Edit University';
include __DIR__ . '/../components/admin-header.php';

$service   = new UniversityService();
$countries = (new CountryService())->getAll();
$id        = (int) ($_GET['id'] ?? 0);
$uni       = null;
if ($id > 0) {
    $uni = $service->getById($id);
}
if (!$uni) {
    echo '<div class="card"><p class="form-hint" style="padding:18px">University not found.</p></div>';
    include __DIR__ . '/../components/footer.php';
    exit;
}

$error   = '';
$success = '';

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

        if (!empty($_FILES['logo']['name'])) {
            $upload = (new MediaService())->store($_FILES['logo'], 'universities');
            if ($upload['key'] !== null) {
                $data['logo_path'] = $upload['key'];
            } elseif ($_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = $upload['error'];
            }
        } else {
            $data['logo_path'] = $uni['logo_path'] ?? null;
        }

        if ($error === '') {
            try {
                if ($service->update($id, $data)) {
                    $success = 'University updated.';
                    $uni = $service->getById($id);
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>Edit University</h1>
    <p>Update the university. The existing logo is kept unless a new one is uploaded.</p>
    <a class="btn btn-ghost" href="<?php echo $admin_url; ?>universities/">Back</a>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

<div class="card">
    <?php if (($uni['logo_path'] ?? '') !== ''): ?>
        <img src="<?php echo htmlspecialchars(MediaResolver::url($uni['logo_path'])); ?>" alt="" style="max-width:140px;max-height:90px;object-fit:contain;border-radius:8px;margin-bottom:16px;display:block">
    <?php endif; ?>
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
            <input class="form-control" type="text" name="name" required value="<?php echo htmlspecialchars($uni['name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Country <span class="req">*</span></label>
            <select class="form-control" name="country_id" required>
                <option value="">-- Select country --</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?php echo (int) $c['id']; ?>" <?php echo ((int) ($uni['country_id'] ?? 0)) === (int) $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name'] ?? ''); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Slug (optional)</label>
            <input class="form-control" type="text" name="slug" value="<?php echo htmlspecialchars($uni['slug'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Location</label>
            <input class="form-control" type="text" name="location" value="<?php echo htmlspecialchars($uni['location'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($uni['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Website URL</label>
            <input class="form-control" type="url" name="website_url" value="<?php echo htmlspecialchars($uni['website_url'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Logo (JPEG / PNG / WEBP, max 5 MB)</label>
            <input class="form-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
            <p class="form-hint">Leave empty to keep the current logo.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Featured</label>
            <select class="form-control" name="is_featured">
                <option value="0" <?php echo (($uni['is_featured'] ?? 0) == 0) ? 'selected' : ''; ?>>No</option>
                <option value="1" <?php echo (($uni['is_featured'] ?? 0) == 1) ? 'selected' : ''; ?>>Yes</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars((string) ($uni['sort_order'] ?? 0)); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>universities/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

