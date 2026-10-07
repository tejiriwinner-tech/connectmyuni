<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\HeroSlideService;
use ConnectMyUni\Services\MediaService;
use ConnectMyUni\Helpers\MediaResolver;

AuthMiddleware::requireAuth();
$page_title = 'Edit Hero Slide';
include __DIR__ . '/../components/admin-header.php';

$service = new HeroSlideService();
$id      = (int) ($_GET['id'] ?? 0);
$slide   = null;
if ($id > 0) {
    $slide = $service->getById($id);
}
if (!$slide) {
    echo '<div class="card"><p class="form-hint" style="padding:18px">Hero slide not found.</p></div>';
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

        if (!empty($_FILES['image']['name'])) {
            $upload = (new MediaService())->store($_FILES['image'], 'hero');
            if ($upload['key'] !== null) {
                $data['image_path'] = $upload['key'];
            } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = $upload['error'];
            }
        } elseif (!empty($_POST['ai_image_path'])) {
            $data['image_path'] = trim((string) $_POST['ai_image_path']);
        } else {
            $data['image_path'] = $slide['image_path'] ?? null;
        }

        if (!empty($_FILES['mobile_image']['name'])) {
            $upload = (new MediaService())->store($_FILES['mobile_image'], 'hero');
            if ($upload['key'] !== null) {
                $data['mobile_image_path'] = $upload['key'];
            } elseif ($_FILES['mobile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = $upload['error'];
            }
        } else {
            $data['mobile_image_path'] = $slide['mobile_image_path'] ?? null;
        }

        if ($error === '') {
            try {
                if ($service->update($id, $data)) {
                    $success = 'Hero slide updated.';
                    $slide = $service->getById($id);
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>Edit Hero Slide</h1>
    <p>Update the slide. Existing images are kept unless new ones are uploaded.</p>
    <a class="btn btn-ghost" href="<?php echo $admin_url; ?>hero-slides/">Back</a>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

<div class="card">
    <?php $d = MediaResolver::url($slide['image_path'] ?? ''); if ($d !== ''): ?>
        <img src="<?php echo htmlspecialchars($d); ?>" alt="" style="max-width:320px;max-height:120px;object-fit:cover;border-radius:8px;margin-bottom:16px;display:block">
    <?php endif; ?>
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
            'cta_url'     => 'cta_url',
        ];
        $aiButtonLabel  = 'Generate Slide Copy with AI';
        include __DIR__ . '/../components/ai-content-generator.php';
        ?>
        <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input class="form-control" type="text" name="title" required value="<?php echo htmlspecialchars($slide['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Subtitle</label>
            <input class="form-control" type="text" name="subtitle" value="<?php echo htmlspecialchars($slide['subtitle'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">CTA Text</label>
            <input class="form-control" type="text" name="cta_text" value="<?php echo htmlspecialchars($slide['cta_text'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">CTA URL</label>
            <input class="form-control" type="url" name="cta_url" value="<?php echo htmlspecialchars($slide['cta_url'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Desktop Image (optional — leave empty to keep)</label>
            <?php
            $aiImageCategory = 'hero';
            $aiImageButtonLabel = 'Generate Hero Banner with AI';
            include __DIR__ . '/../components/ai-image-generator.php';
            ?>
            <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="form-group">
            <label class="form-label">Mobile Image (optional)</label>
            <input class="form-control" type="file" name="mobile_image" accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars((string) ($slide['sort_order'] ?? 0)); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Active</label>
            <select class="form-control" name="is_active">
                <option value="1" <?php echo (($slide['is_active'] ?? 1) == 1) ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo (($slide['is_active'] ?? 1) == 0) ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>hero-slides/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

