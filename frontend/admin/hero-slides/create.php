<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\HeroSlideService;
use ConnectMyUni\Services\MediaService;

AuthMiddleware::requireAuth();
$page_title = 'New Hero Slide';
include __DIR__ . '/../components/admin-header.php';

$error   = '';
$success = '';
$service = new HeroSlideService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $data = [
            'title'      => trim($_POST['title'] ?? ''),
            'subtitle'   => trim($_POST['subtitle'] ?? ''),
            'cta_text'   => trim($_POST['cta_text'] ?? ''),
            'cta_url'    => trim($_POST['cta_url'] ?? ''),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active'  => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if (empty($data['title'])) {
            $error = 'Title is required.';
        }

        // Desktop image is required (image_path is NOT NULL in the schema).
        if (empty($_FILES['image']['name'])) {
            $error = 'A desktop image is required.';
        } else {
            $upload = (new MediaService())->store($_FILES['image'], 'hero');
            if ($upload['key'] !== null) {
                $data['image_path'] = $upload['key'];
            } else {
                $error = $upload['error'];
            }
        }

        // Optional mobile image.
        if ($error === '' && !empty($_FILES['mobile_image']['name'])) {
            $upload = (new MediaService())->store($_FILES['mobile_image'], 'hero');
            if ($upload['key'] !== null) {
                $data['mobile_image_path'] = $upload['key'];
            } else {
                $error = $upload['error'];
            }
        }

        if ($error === '') {
            try {
                $service->create($data);
                $success = 'Hero slide created. <a href="' . $admin_url . 'hero-slides/">Back to list</a>';
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>New Hero Slide</h1>
    <p>Create a homepage hero slide. Desktop image is required; both images are stored under storage/uploads/hero/.</p>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<div class="card">
    <form method="POST" action="" enctype="multipart/form-data">
        <?php echo Security::csrfInput(); ?>
        <?php
        $aiContentType  = 'hero_slide';
        $aiFieldMap     = [
            'headline'    => 'title',
            'title'       => 'title',
            'subtitle'    => 'subtitle',
            'destination' => 'subtitle',
            'cta_text'    => 'cta_text',
        ];
        $aiButtonLabel  = 'Generate Slide Copy with AI';
        include __DIR__ . '/../components/ai-content-generator.php';
        ?>
        <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input class="form-control" type="text" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Subtitle</label>
            <input class="form-control" type="text" name="subtitle" value="<?php echo htmlspecialchars($_POST['subtitle'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">CTA Text</label>
            <input class="form-control" type="text" name="cta_text" value="<?php echo htmlspecialchars($_POST['cta_text'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">CTA URL</label>
            <input class="form-control" type="url" name="cta_url" value="<?php echo htmlspecialchars($_POST['cta_url'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Desktop Image (JPEG / PNG / WEBP, max 5 MB) <span class="req">*</span></label>
            <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
            <p class="form-hint">Saved as a storage-relative key in image_path.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Mobile Image (optional)</label>
            <input class="form-control" type="file" name="mobile_image" accept="image/jpeg,image/png,image/webp">
            <p class="form-hint">Saved in mobile_image_path.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars((string) ($_POST['sort_order'] ?? 0)); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Active</label>
            <select class="form-control" name="is_active">
                <option value="1" <?php echo (($_POST['is_active'] ?? 1) == 1) ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo (($_POST['is_active'] ?? 1) == 0) ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create Slide</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>hero-slides/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

