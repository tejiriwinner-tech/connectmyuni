<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\AdminUserRepository;
use ConnectMyUni\Config\Security;

AuthMiddleware::requireAuth();

$currentUser = AuthMiddleware::user();
// Role check
if (($currentUser['role'] ?? '') !== 'admin') {
    $_SESSION['admin_flash_error'] = 'Access denied: Only administrators can create new users.';
    header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/users/');
    exit;
}

$repo = new AdminUserRepository();
$error = '';
$username = '';
$email = '';
$role = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = trim($_POST['role'] ?? 'admin');

        $allowedRoles = ['admin', 'editor', 'viewer'];

        if (empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $username)) {
            $error = 'Username must be 3-30 characters (letters, numbers, dots, dashes, underscores).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif (!in_array($role, $allowedRoles, true)) {
            $error = 'Invalid role specified.';
        } elseif ($repo->findByUsername($username)) {
            $error = 'A user with this username already exists.';
        } elseif ($repo->findByEmail($email)) {
            $error = 'A user with this email address already exists.';
        } else {
            try {
                $newId = $repo->create($username, $email, $password, $role);
                if ($newId > 0) {
                    $_SESSION['admin_flash'] = "User '{$username}' created successfully.";
                    header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/users/');
                    exit;
                } else {
                    $error = 'Failed to create user. Please check database logs.';
                }
            } catch (\Throwable $e) {
                error_log('[Create User] Error: ' . $e->getMessage());
                $error = 'An unexpected error occurred while creating the user.';
            }
        }
    }
}

$page_title = 'Create User';
$csrfToken = Security::generateCsrfToken();
include __DIR__ . '/../components/admin-header.php';
?>

<div class="page-heading">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
        <a href="<?php echo $admin_url; ?>users/" class="btn btn-ghost" style="padding:4px 8px;font-size:0.85rem;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 style="margin:0;">Add New Admin User</h1>
    </div>
    <p>Create a new administrative or editorial account for your team</p>
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
            <label class="form-label" for="username">Username <span class="req">*</span></label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required placeholder="e.g. sarah_editor" autocomplete="off">
            <small style="color:var(--txt-muted);font-size:0.75rem;margin-top:4px;display:block;">Unique alphanumeric handle for login.</small>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email Address <span class="req">*</span></label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required placeholder="sarah@connectmyuni.net">
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Initial Password <span class="req">*</span></label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="Minimum 8 characters">
            <small style="color:var(--txt-muted);font-size:0.75rem;margin-top:4px;display:block;">Must be at least 8 characters. The user can update it anytime from their dashboard.</small>
        </div>

        <div class="form-group">
            <label class="form-label" for="role">User Role <span class="req">*</span></label>
            <select id="role" name="role" class="form-control" required>
                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrator (Full control, settings & user management)</option>
                <option value="editor" <?php echo $role === 'editor' ? 'selected' : ''; ?>>Editor (Create and update events, universities & media)</option>
                <option value="viewer" <?php echo $role === 'viewer' ? 'selected' : ''; ?>>Viewer (Read-only dashboard access)</option>
            </select>
        </div>

        <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:24px;">
            <a href="<?php echo $admin_url; ?>users/" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check me-1"></i> Create Account
            </button>
        </div>
    </form>
</div>

</main>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
