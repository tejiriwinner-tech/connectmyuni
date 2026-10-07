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
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $baseUrl; ?>frontend/assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?php echo $baseUrl; ?>frontend/assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0c1a30 0%, #1a365d 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .login-header {
            background: #ffffff;
            color: #0f172a;
            padding: 34px 28px 22px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }
        .login-logo-wrap {
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-logo-img {
            max-height: 80px;
            max-width: 220px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.05));
        }
        .login-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .login-header p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 13.5px;
            font-weight: 500;
        }
        .login-body { padding: 32px 28px 36px; }
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .form-floating { margin-bottom: 20px; }
        .form-floating label { font-size: 14px; color: #666; }
        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: #0052cc;
            box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.12);
        }
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #0052cc 0%, #003d99 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(0, 82, 204, 0.25);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 82, 204, 0.35);
            background: linear-gradient(135deg, #0047b3 0%, #002d73 100%);
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }
        .login-footer a {
            color: #0052cc;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        .login-footer a:hover {
            color: #003d99;
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .login-container { max-width: 100%; }
            .login-body { padding: 26px 20px; }
            .login-header { padding: 28px 20px 18px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo-wrap">
                <img src="<?php echo $baseUrl; ?>frontend/assets/images/logo.png" alt="Connect MyUni Logo" class="login-logo-img">
            </div>
            <h2>Admin Sign In</h2>
            <p>Connect MyUni Management Portal</p>
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

