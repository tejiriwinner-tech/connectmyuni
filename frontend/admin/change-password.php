<?php
// Bootstrap first — provides autoloading + CONNECTMYUNI_BASE_URL.
require_once __DIR__ . '/../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\AdminUserRepository;
use ConnectMyUni\Config\Security;

AuthMiddleware::requireAuth();

$user = AuthMiddleware::user();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $error = 'Password must contain both letters and numbers.';
        } else {
            try {
                $repo = new AdminUserRepository();
                $dbUser = $repo->findById(AuthMiddleware::userId());

                if (!$dbUser || !Security::verifyPassword($currentPassword, $dbUser['password_hash'])) {
                    $error = 'Current password is incorrect.';
                } else {
                    $newHash = Security::hashPassword($newPassword);
                    $repo->updatePassword(AuthMiddleware::userId(), $newHash);

                    $success = 'Password changed successfully. Please use your new password for future logins.';
                }
            } catch (\Throwable $e) {
                error_log('Change password failed: ' . $e->getMessage());
                $error = 'Could not update your password right now. Please try again later.';
            }
        }
    }
}

$page_title = 'Change Password';
$csrfToken = Security::generateCsrfToken();
include __DIR__ . '/components/admin-header.php';
?>

<div class="page-heading">
    <h1>Change Password</h1>
    <p>Update your account password securely</p>
</div>

<div class="row justify-content-center" style="max-width: 620px; margin: 0 auto;">
    <div class="col-12">
        <div class="card" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 28px; box-shadow: 0 8px 24px rgba(0,0,0,0.25);">
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 20px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #4ade80; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="color: var(--txt); font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; display: block;">
                        Current Password <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="password" name="current_password" class="form-control" style="background: var(--surface2); border: 1px solid var(--border); color: var(--txt); padding: 10px 14px; border-radius: 8px; width: 100%;" required>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="color: var(--txt); font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; display: block;">
                        New Password <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="password" name="new_password" class="form-control" style="background: var(--surface2); border: 1px solid var(--border); color: var(--txt); padding: 10px 14px; border-radius: 8px; width: 100%;" required minlength="8">
                    <small style="color: var(--txt-muted); display: block; margin-top: 6px; font-size: 0.78rem;">At least 8 characters, must contain letters and numbers</small>
                </div>

                <div class="form-group" style="margin-bottom: 26px;">
                    <label class="form-label" style="color: var(--txt); font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; display: block;">
                        Confirm New Password <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="password" name="confirm_password" class="form-control" style="background: var(--surface2); border: 1px solid var(--border); color: var(--txt); padding: 10px 14px; border-radius: 8px; width: 100%;" required minlength="8">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px 20px; font-weight: 700; font-size: 0.92rem; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
