<?php
// Bootstrap first — provides autoloading + CONNECTMYUNI_BASE_URL.
require_once __DIR__ . '/../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Repositories\AdminUserRepository;

// Ensure the session is active before reading $_SESSION below. bootstrap.php
// does not start sessions, so without this the "already logged in" redirect
// never fires — an authenticated admin visiting this page would see the login
// form again instead of being sent to the dashboard.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['admin_user_id'])) {
    header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
                } else {
            try {
                $repo = new AdminUserRepository();
                $user = $repo->verifyCredentials($username, $password);

                if ($user) {
                    $repo->updateLastLogin($user['id']);

                    ConnectMyUni\Middleware\AuthMiddleware::login(
                        $user['id'],
                        $user['username'],
                        $user['role']
                    );

                    $redirect = $_SESSION['redirect_after_login'] ?? CONNECTMYUNI_BASE_URL . 'frontend/admin/index.php';
                    unset($_SESSION['redirect_after_login']);

                    header('Location: ' . $redirect);
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (\Throwable $e) {
                error_log('Login verification failed: ' . $e->getMessage());
                $error = 'We could not process your login request. Please try again later.';
            }
        }
    }
}

$csrfToken = Security::generateCsrfToken();
$baseUrl   = CONNECTMYUNI_BASE_URL;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Connect MyUni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a56db 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background: linear-gradient(135deg, #1a56db 0%, #1340a8 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header i { font-size: 48px; margin-bottom: 15px; }
        .login-header h2 { margin: 0; font-size: 24px; font-weight: 600; }
        .login-header p { margin: 8px 0 0; opacity: 0.9; font-size: 14px; }
        .login-body { padding: 40px 30px; }
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .form-floating { margin-bottom: 20px; }
        .form-floating label { font-size: 14px; color: #666; }
        .form-control {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 15px;
        }
        .form-control:focus {
            border-color: #1a56db;
            box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1a56db 0%, #1340a8 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 86, 219, 0.3);
            background: linear-gradient(135deg, #1340a8 0%, #0f2d91 100%);
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .login-footer a {
            color: #1a56db;
            text-decoration: none;
            font-size: 14px;
        }
        .login-footer a:hover { text-decoration: underline; }
        @media (max-width: 768px) {
            .login-container { max-width: 100%; }
            .login-body { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-university"></i>
            <h2>Connect MyUni</h2>
            <p>Admin Panel Login</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username"
                           placeholder="Username" required autofocus>
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="login-footer">
                <a href="<?php echo $baseUrl; ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

