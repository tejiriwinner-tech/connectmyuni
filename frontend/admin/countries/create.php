<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\CountryService;

AuthMiddleware::requireAuth();
$page_title = 'New Country';
include __DIR__ . '/../components/admin-header.php';

$error   = '';
$success = '';
$service = new CountryService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'flag_emoji'  => strtoupper(trim($_POST['flag_emoji'] ?? '')),
            'description' => trim($_POST['description'] ?? ''),
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ];

        if ($data['name'] === '') {
            $error = 'Name is required.';
        } elseif ($data['flag_emoji'] !== '' && (strlen($data['flag_emoji']) !== 2 || !ctype_alpha($data['flag_emoji']))) {
            $error = 'Flag must be a two-letter country code (e.g. GB, US, CA).';
        }

        if ($error === '') {
            try {
                $service->create($data);
                $success = 'Country created. <a href="' . $admin_url . 'countries/">Back to list</a>';
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<div class="page-heading">
    <h1>New Country</h1>
    <p>Create a country. The flag is stored as a two-letter country code (e.g. GB, US, CA, AU, MY, PH).</p>
</div>

<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<div class="card">
    <form method="POST" action="">
        <?php echo Security::csrfInput(); ?>
        <div class="form-group">
            <label class="form-label">Name <span class="req">*</span></label>
            <input class="form-control" type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Flag (two-letter country code)</label>
            <input class="form-control" type="text" name="flag_emoji" maxlength="2" placeholder="GB" value="<?php echo htmlspecialchars($_POST['flag_emoji'] ?? ''); ?>">
            <p class="form-hint">Optional. ISO 3166-1 alpha-2 code, e.g. GB, US, CA, AU, MY, PH. Rendered as a flag emoji.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input class="form-control" type="number" name="sort_order" value="<?php echo htmlspecialchars((string) ($_POST['sort_order'] ?? 0)); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Featured</label>
            <select class="form-control" name="is_featured">
                <option value="1" <?php echo (($_POST['is_featured'] ?? 0) == 1) ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo (($_POST['is_featured'] ?? 0) == 0) ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create Country</button>
        <a class="btn btn-ghost" href="<?php echo $admin_url; ?>countries/">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

