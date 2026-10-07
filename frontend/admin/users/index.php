<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\AdminUserRepository;
use ConnectMyUni\Config\Security;

AuthMiddleware::requireAuth();

$currentUser = AuthMiddleware::user();
$repo = new AdminUserRepository();

// Handle quick status toggle or delete if admin
$flash = $_SESSION['admin_flash'] ?? '';
$flashError = $_SESSION['admin_flash_error'] ?? '';
unset($_SESSION['admin_flash'], $_SESSION['admin_flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['admin_flash_error'] = 'Invalid security token.';
        header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/users/');
        exit;
    }

    // Role check: Only admin can manage users
    if (($currentUser['role'] ?? '') !== 'admin') {
        $_SESSION['admin_flash_error'] = 'Access denied: Only administrators can manage users.';
        header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/users/');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $targetId = (int) ($_POST['user_id'] ?? 0);

    if ($targetId === (int) AuthMiddleware::userId()) {
        $_SESSION['admin_flash_error'] = 'You cannot modify your own status or delete yourself.';
        header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/users/');
        exit;
    }

    if ($action === 'toggle_status') {
        $targetUser = $repo->findById($targetId);
        if ($targetUser) {
            $newStatus = empty($targetUser['is_active']);
            $repo->updateStatus($targetId, $newStatus);
            $_SESSION['admin_flash'] = 'User status updated successfully.';
        }
    } elseif ($action === 'delete') {
        $repo->deleteUser($targetId);
        $_SESSION['admin_flash'] = 'User deleted successfully.';
    }

    header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/users/');
    exit;
}

$users = $repo->getAll();
$page_title = 'Manage Users';
$csrfToken = Security::generateCsrfToken();
include __DIR__ . '/../components/admin-header.php';
?>

<div class="page-heading" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
    <div>
        <h1>Manage Users</h1>
        <p>Control administrator accounts, team members, and access privileges</p>
    </div>
    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
        <a class="btn btn-primary" href="<?php echo $admin_url; ?>users/create.php">
            <i class="fas fa-user-plus me-1"></i> Add New User
        </a>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
    <div class="alert alert-success" style="margin-bottom:20px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:#4ade80;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flash); ?>
    </div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div class="alert alert-danger" style="margin-bottom:20px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($flashError); ?>
    </div>
<?php endif; ?>

<div class="card" style="padding:0;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:50px;">User</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th style="width:160px;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:30px;color:var(--txt-muted);">
                            No users found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div style="width:34px;height:34px;border-radius:50%;background:rgba(76,142,247,0.18);color:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;">
                                    <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                </div>
                            </td>
                            <td>
                                <strong style="color:var(--txt);"><?php echo htmlspecialchars($u['username']); ?></strong>
                                <?php if ((int)$u['id'] === (int)AuthMiddleware::userId()): ?>
                                    <span style="font-size:0.7rem;background:rgba(255,255,255,0.08);padding:2px 6px;border-radius:4px;color:var(--txt-muted);margin-left:4px;">You</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--txt-muted);">
                                <?php echo htmlspecialchars($u['email']); ?>
                            </td>
                            <td>
                                <?php
                                $role = strtolower((string)($u['role'] ?? 'admin'));
                                $roleBadge = [
                                    'admin' => ['bg' => 'rgba(76,142,247,0.15)', 'fg' => '#60a5fa', 'label' => 'Admin'],
                                    'editor' => ['bg' => 'rgba(34,197,94,0.15)', 'fg' => '#4ade80', 'label' => 'Editor'],
                                    'viewer' => ['bg' => 'rgba(168,85,247,0.15)', 'fg' => '#c084fc', 'label' => 'Viewer'],
                                ][$role] ?? ['bg' => 'rgba(136,146,164,0.15)', 'fg' => '#94a3b8', 'label' => ucfirst($role)];
                                ?>
                                <span style="display:inline-block;padding:3px 9px;border-radius:6px;font-size:0.75rem;font-weight:600;background:<?php echo $roleBadge['bg']; ?>;color:<?php echo $roleBadge['fg']; ?>;">
                                    <?php echo $roleBadge['label']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($u['is_active'])): ?>
                                    <span style="display:inline-flex;align-items:center;gap:6px;color:#4ade80;font-size:0.8rem;font-weight:600;">
                                        <i class="fas fa-circle" style="font-size:0.45rem;"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span style="display:inline-flex;align-items:center;gap:6px;color:#f87171;font-size:0.8rem;font-weight:600;">
                                        <i class="fas fa-circle" style="font-size:0.45rem;"></i> Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.8rem;color:var(--txt-muted);">
                                <?php echo !empty($u['last_login_at']) ? date('M d, Y H:i', strtotime($u['last_login_at'])) : '<span style="opacity:0.5;">Never</span>'; ?>
                            </td>
                            <td style="font-size:0.8rem;color:var(--txt-muted);">
                                <?php echo !empty($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : '—'; ?>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex;align-items:center;gap:8px;">
                                    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                                        <a href="<?php echo $admin_url; ?>users/edit.php?id=<?php echo (int)$u['id']; ?>" class="btn btn-ghost" style="padding:4px 10px;font-size:0.8rem;" title="Edit User">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        <?php if ((int)$u['id'] !== (int)AuthMiddleware::userId()): ?>
                                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Toggle active status for this user?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                <button type="submit" class="btn btn-ghost" style="padding:4px 10px;font-size:0.8rem;color:var(--warning);" title="<?php echo !empty($u['is_active']) ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas <?php echo !empty($u['is_active']) ? 'fa-ban' : 'fa-check'; ?>"></i>
                                                </button>
                                            </form>

                                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                <button type="submit" class="btn btn-ghost" style="padding:4px 10px;font-size:0.8rem;color:var(--danger);" title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="font-size:0.75rem;color:var(--txt-muted);">Read-only</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
