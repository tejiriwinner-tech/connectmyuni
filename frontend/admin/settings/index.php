<?php
// Bootstrap backend layer
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Database;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\SettingsRepository;
use ConnectMyUni\Config\Security;

AuthMiddleware::requireAuth();

// Get PDO connection safely
$pdo = null;
try {
    $pdo = Database::getConnection();
} catch (\Throwable $e) {
    error_log("Database connection failed in settings: " . $e->getMessage());
}

$settingsTableExists = false;
$settingsRepo = null;
if ($pdo) {
    try {
        $checkTable = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'settings')");
        if ($checkTable) {
            $settingsTableExists = (bool) $checkTable->fetchColumn();
        }
    } catch (\Throwable $e) {
        $settingsTableExists = false;
    }

    if ($settingsTableExists) {
        $settingsRepo = new SettingsRepository($pdo);
    }
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $settingsTableExists && $settingsRepo) {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error_message = "Invalid security token. Please try again.";
    } else {
        $social_facebook = trim($_POST['social_facebook'] ?? '#');
        $social_twitter = trim($_POST['social_twitter'] ?? '#');
        $social_instagram = trim($_POST['social_instagram'] ?? '#');
        $social_linkedin = trim($_POST['social_linkedin'] ?? '#');
        $social_youtube = trim($_POST['social_youtube'] ?? '#');

        $site_title = trim($_POST['site_title'] ?? 'Connect MyUni');
        $site_description = trim($_POST['site_description'] ?? 'Your Gateway to Global Education');
        $contact_email = trim($_POST['contact_email'] ?? 'info@connectmyuni.net');
        $contact_phone = trim($_POST['contact_phone'] ?? '+234 800 000 0000');
        $contact_address = trim($_POST['contact_address'] ?? 'Lagos & Abuja, Nigeria');
        $seo_meta_keywords = trim($_POST['seo_meta_keywords'] ?? 'study abroad nigeria, education consultancy lagos, study in uk, study in canada, study in usa');

        // Persist to settings table
        $settingsRepo->updateSetting('social_facebook', $social_facebook, 'url', 'Facebook social media link');
        $settingsRepo->updateSetting('social_twitter', $social_twitter, 'url', 'Twitter social media link');
        $settingsRepo->updateSetting('social_instagram', $social_instagram, 'url', 'Instagram social media link');
        $settingsRepo->updateSetting('social_linkedin', $social_linkedin, 'url', 'LinkedIn social media link');
        $settingsRepo->updateSetting('social_youtube', $social_youtube, 'url', 'YouTube channel link');

        $settingsRepo->updateSetting('site_title', $site_title, 'text', 'Website title');
        $settingsRepo->updateSetting('site_description', $site_description, 'text', 'Website description');
        $settingsRepo->updateSetting('contact_email', $contact_email, 'text', 'Official contact email');
        $settingsRepo->updateSetting('contact_phone', $contact_phone, 'text', 'Official contact telephone');
        $settingsRepo->updateSetting('contact_address', $contact_address, 'text', 'Physical office address');
        $settingsRepo->updateSetting('seo_meta_keywords', $seo_meta_keywords, 'text', 'Default SEO keywords');

        $success_message = "Settings updated successfully!";
    }
}

// Get current settings
if ($settingsTableExists && $settingsRepo) {
    $settings = $settingsRepo->getAllPublicSettings();
    $social_facebook = $settings['social_facebook'] ?? '#';
    $social_twitter = $settings['social_twitter'] ?? '#';
    $social_instagram = $settings['social_instagram'] ?? '#';
    $social_linkedin = $settings['social_linkedin'] ?? '#';
    $social_youtube = $settings['social_youtube'] ?? '#';

    $site_title = $settings['site_title'] ?? 'Connect MyUni';
    $site_description = $settings['site_description'] ?? 'Your Gateway to Global Education';
    $contact_email = $settings['contact_email'] ?? 'info@connectmyuni.net';
    $contact_phone = $settings['contact_phone'] ?? '+234 800 000 0000';
    $contact_address = $settings['contact_address'] ?? 'Lagos & Abuja, Nigeria';
    $seo_meta_keywords = $settings['seo_meta_keywords'] ?? 'study abroad nigeria, education consultancy lagos, study in uk, study in canada, study in usa';
} else {
    $social_facebook = '#';
    $social_twitter = '#';
    $social_instagram = '#';
    $social_linkedin = '#';
    $social_youtube = '#';

    $site_title = 'Connect MyUni';
    $site_description = 'Your Gateway to Global Education';
    $contact_email = 'info@connectmyuni.net';
    $contact_phone = '+234 800 000 0000';
    $contact_address = 'Lagos & Abuja, Nigeria';
    $seo_meta_keywords = 'study abroad nigeria, education consultancy lagos, study in uk, study in canada, study in usa';
}

$page_title = 'Website Settings';
$csrfToken = Security::generateCsrfToken();
include __DIR__ . '/../components/admin-header.php';
?>

<div class="page-heading">
    <h1>Website Settings</h1>
    <p>Manage your website configuration, branding, contact channels, and SEO metadata</p>
</div>

<?php if (!$settingsTableExists): ?>
    <div class="alert alert-danger" style="margin-bottom: 24px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 14px 18px; border-radius: 8px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-exclamation-triangle"></i>
        Settings table not found in the database. Operating with default values.
    </div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success" style="margin-bottom: 24px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #4ade80; padding: 14px 18px; border-radius: 8px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" style="margin-bottom: 24px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 14px 18px; border-radius: 8px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

    <!-- General Branding Settings -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 class="section-label" style="margin-bottom: 18px; color: var(--txt);">
            <i class="fas fa-globe" style="color: var(--accent);"></i> General & Branding
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="form-row">
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Website Title</label>
                <input type="text" name="site_title" class="form-control" value="<?php echo htmlspecialchars($site_title); ?>" placeholder="Connect MyUni" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Website Description & Tagline</label>
                <textarea name="site_description" class="form-control" rows="3" placeholder="Your Gateway to Global Education" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>><?php echo htmlspecialchars($site_description); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Contact & Location Info -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 class="section-label" style="margin-bottom: 18px; color: var(--txt);">
            <i class="fas fa-envelope-open-text" style="color: var(--warning);"></i> Contact Information
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="form-row">
            <div class="form-group">
                <label class="form-label">Official Contact Email</label>
                <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($contact_email); ?>" placeholder="info@connectmyuni.net" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label class="form-label">Contact Telephone</label>
                <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($contact_phone); ?>" placeholder="+234 800 000 0000" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Office Address</label>
                <input type="text" name="contact_address" class="form-control" value="<?php echo htmlspecialchars($contact_address); ?>" placeholder="Lagos & Abuja, Nigeria" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            </div>
        </div>
    </div>

    <!-- SEO & Metadata -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 class="section-label" style="margin-bottom: 18px; color: var(--txt);">
            <i class="fas fa-magnifying-glass-chart" style="color: var(--success);"></i> SEO & Search Optimization
        </h3>
        <div class="form-group">
            <label class="form-label">Default SEO Meta Keywords (Comma-separated)</label>
            <textarea name="seo_meta_keywords" class="form-control" rows="2" placeholder="study abroad, overseas education..." <?php echo !$settingsTableExists ? 'disabled' : ''; ?>><?php echo htmlspecialchars($seo_meta_keywords); ?></textarea>
            <small style="color:var(--txt-muted);font-size:0.75rem;margin-top:4px;display:block;">Keywords indexed in the public header tag for search engine crawlers.</small>
        </div>
    </div>

    <!-- Social Media Settings -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 class="section-label" style="margin-bottom: 18px; color: var(--txt);">
            <i class="fas fa-share-nodes" style="color: var(--accent);"></i> Social Media Channels
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="form-row">
            <div class="form-group">
                <label class="form-label"><i class="fab fa-facebook-f me-2" style="color: #3b82f6;"></i>Facebook URL</label>
                <input type="url" name="social_facebook" class="form-control" value="<?php echo htmlspecialchars($social_facebook); ?>" placeholder="https://facebook.com/yourpage" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-twitter me-2" style="color: #38bdf8;"></i>Twitter / X URL</label>
                <input type="url" name="social_twitter" class="form-control" value="<?php echo htmlspecialchars($social_twitter); ?>" placeholder="https://twitter.com/yourhandle" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-instagram me-2" style="color: #ec4899;"></i>Instagram URL</label>
                <input type="url" name="social_instagram" class="form-control" value="<?php echo htmlspecialchars($social_instagram); ?>" placeholder="https://instagram.com/yourhandle" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-linkedin-in me-2" style="color: #0284c7;"></i>LinkedIn URL</label>
                <input type="url" name="social_linkedin" class="form-control" value="<?php echo htmlspecialchars($social_linkedin); ?>" placeholder="https://linkedin.com/company/yourcompany" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label"><i class="fab fa-youtube me-2" style="color: #ef4444;"></i>YouTube Channel URL</label>
                <input type="url" name="social_youtube" class="form-control" value="<?php echo htmlspecialchars($social_youtube); ?>" placeholder="https://youtube.com/@connectmyuni" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            </div>
        </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:36px;">
        <button type="submit" class="btn btn-primary" <?php echo !$settingsTableExists ? 'disabled' : ''; ?>>
            <i class="fas fa-save me-1"></i> Save All Settings
        </button>
    </div>
</form>

</main>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
