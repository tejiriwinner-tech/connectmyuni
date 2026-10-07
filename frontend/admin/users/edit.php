<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\AdminUserRepository;
use ConnectMyUni\Config\Security;

AuthMiddleware::requireAuth();

$currentUser = AuthMiddleware::user();
// Role check
if (($currentUser['role'] ?? '') !== 'admin') {
    $_SESSION['admin_flash_error'] = 'Access denied: Only administrators can edit user details.';
    header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/users/');
    exit;
}

$repo = new AdminUserRepository();
$id = (int) ($_GET['id'] ?? 0);
$userToEdit = $repo->findById($id);

if (!$userToEdit) {
    $_SESSION['admin_flash_error'] = 'User not found.';
    header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/users/');
    exit;
}

$error = '';
$email = $userToEdit['email'];
$role = $userToEdit['role'];
$isActive = (bool) $userToEdit['is_active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'admin');
        $isActive = isset($_POST['is_active']) ? (bool) $_POST['is_active'] : false;
        $newPassword = $_POST['new_password'] ?? '';

        $allowedRoles = ['admin', 'editor', 'viewer'];

        // Self-demotion safeguard: Do not allow current user to demote or deactivate themselves
        if ($id === (int) AuthMiddleware::userId()) {
            if ($role !== 'admin') {
                $error = 'You cannot demote your own administrator account.';
            }
            if (!$isActive) {
                $error = 'You cannot deactivate your own account.';
            }
        }

        if (empty($error)) {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (!in_array($role, $allowedRoles, true)) {
                $error = 'Invalid role specified.';
            } else {
                // Check email collision
                $existingByEmail = $repo->findByEmail($email);
                if ($existingByEmail && (int)$existingByEmail['id'] !== $id) {
                    $error = 'Another user with this email address already exists.';
                } else {
                    $updatePayload = [
                        'email' => $email,
                        'role' => $role,
                        'is_active' => $isActive,
                    ];
                    if (!empty($newPassword)) {
                        if (strlen($newPassword) < 8) {
                            $error = 'New password must be at least 8 characters long.';
                        } else {
                            $updatePayload['password'] = $newPassword;
                        }
                    }

                    if (empty($error)) {
                        try {
                            $repo->updateUser($id, $updatePayload);
                            $_SESSION['admin_flash'] = "User '{$userToEdit['username']}' updated successfully.";
                            header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/users/');
                            exit;
                        } catch (\Throwable $e) {
                            error_log('[Edit User] Error: ' . $e->getMessage());
                            $error = 'An error occurred while saving user details.';
                        }
                    }
                }
            }
        }
    }
}

$page_title = 'Edit User';
$csrfToken = Security::generateCsrfToken();
include __DIR__ . '/../components/admin-header.php';
?>

<div class="page-heading">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
        <a href="<?php echo $admin_url; ?>users/" class="btn btn-ghost" style="padding:4px 8px;font-size:0.85rem;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 style="margin:0;">Edit User: <?php echo htmlspecialchars($userToEdit['username']); ?></h1>
    </div>
    <p>Modify role permissions, account status, or reset credentials</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="margin-bottom:20px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width:680px;">
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

        <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($userToEdit['username']); ?>" disabled style="opacity:0.6;cursor:not-allowed;">
            <small style="color:var(--txt-muted);font-size:0.75rem;margin-top:4px;display:block;">Usernames cannot be changed.</small>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email Address <span class="req">*</span></label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="role">User Role <span class="req">*</span></label>
            <select id="role" name="role" class="form-control" required <?php echo ($id === (int)AuthMiddleware::userId()) ? 'disabled' : ''; ?>>
                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrator (Full control, settings & user management)</option>
                <option value="editor" <?php echo $role === 'editor' ? 'selected' : ''; ?>>Editor (Create and update events, universities & media)</option>
                <option value="viewer" <?php echo $role === 'viewer' ? 'selected' : ''; ?>>Viewer (Read-only dashboard access)</option>
            </select>
            <?php if ($id === (int)AuthMiddleware::userId()): ?>
                <input type="hidden" name="role" value="admin">
                <small style="color:var(--txt-muted);font-size:0.75rem;margin-top:4px;display:block;">You cannot change your own role.</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo $isActive ? 'checked' : ''; ?> <?php echo ($id === (int)AuthMiddleware::userId()) ? 'disabled' : ''; ?> style="width:18px;height:18px;accent-color:var(--accent);">
                <span>Account is Active</span>
            </label>
            <?php if ($id === (int)AuthMiddleware::userId()): ?>
                <input type="hidden" name="is_active" value="1">
                <small style="color:var(--txt-muted);font-size:0.75rem;display:block;margin-top:2px;">You cannot deactivate your own account.</small>
            <?php endif; ?>
        </div>

        <div style="border-top:1px solid var(--border);padding-top:18px;margin-top:20px;">
            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:8px;color:var(--txt);">Reset Password</h4>
            <p style="font-size:0.8rem;color:var(--txt-muted);margin-bottom:12px;">Leave blank to keep the current password unchanged.</p>
            <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password (optional)">
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:24px;">
            <a href="<?php echo $admin_url; ?>users/" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>

</main>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
