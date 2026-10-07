<?php
// Bootstrap backend layer (autoloading + configuration + BASE_URL).
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;

// Require authentication for all admin pages
AuthMiddleware::requireAuth();

// Refresh session to prevent timeout
AuthMiddleware::refreshSession();

$base_url   = CONNECTMYUNI_BASE_URL;
$admin_url  = CONNECTMYUNI_BASE_URL . 'frontend/admin/';
$style_version = @filemtime(__DIR__ . '/../style.css') ?: time();
$current_page = basename($_SERVER['PHP_SELF']);
$currentUser = AuthMiddleware::user();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' – Admin' : 'Admin · Connect MyUni'; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $base_url; ?>frontend/assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?php echo $base_url; ?>frontend/assets/images/logo.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom Unique UI/UX Theme -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/theme-core.css?v=<?php echo $style_version; ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/theme-components.css?v=<?php echo $style_version; ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>frontend/assets/css/theme-pages.css?v=<?php echo $style_version; ?>">
    <style>
        /* TOKENS */
        :root {
            --sidebar-w: 240px;
            --topbar-h: 60px;

            --bg: #0f1117;
            --surface: #181c25;
            --surface2: #1e2333;
            --border: rgba(255, 255, 255, 0.07);
            --primary: #0052CC;
            --accent: #4C8EF7;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --txt: #e8eaf0;
            --txt-muted: #8892a4;
            --radius: 10px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--txt);
            display: flex;
            min-height: 100vh;
            font-size: 14px;
            overflow-x: hidden;
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       SIDEBAR
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .admin-sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 200;
            transition: transform 0.3s ease;
        }

        .sidebar-logo {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .sidebar-logo:hover {
            text-decoration: none;
        }

        .sidebar-logo .logo-brand-img {
            width: 38px;
            height: 38px;
            object-fit: contain;
            background: #ffffff;
            border-radius: 8px;
            padding: 3px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        .sidebar-logo .logo-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #fff;
            flex-shrink: 0;
        }

        .sidebar-logo .logo-text {
            line-height: 1.25;
        }

        .sidebar-logo .logo-text strong {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--txt);
            letter-spacing: 0.3px;
        }

        .sidebar-logo .logo-text span {
            font-size: 0.72rem;
            color: var(--txt-muted);
        }

        /* Nav groups */
        .nav-group {
            padding: 20px 12px 0;
        }

        .nav-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--txt-muted);
            padding: 0 8px;
            margin-bottom: 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 8px;
            color: var(--txt-muted);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.18s ease;
            margin-bottom: 2px;
            cursor: pointer;
        }

        .nav-item i {
            width: 18px;
            text-align: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .nav-item:hover {
            background: var(--surface2);
            color: var(--txt);
        }

        .nav-item.active {
            background: rgba(0, 82, 204, 0.18);
            color: var(--accent);
        }

        .nav-item.active i {
            color: var(--accent);
        }

        .nav-badge {
            margin-left: auto;
            background: var(--primary);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px 12px;
            border-top: 1px solid var(--border);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            transition: background 0.18s;
        }

        .sidebar-user:hover {
            background: var(--surface2);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: #fff;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-info strong {
            display: block;
            font-size: 0.8rem;
            color: var(--txt);
            font-weight: 600;
        }

        .user-info span {
            font-size: 0.7rem;
            color: var(--txt-muted);
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       TOPBAR
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .admin-topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--topbar-h);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 100;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
            color: var(--txt-muted);
        }

        .topbar-breadcrumb .current {
            color: var(--txt);
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-time {
            font-family: 'DM Mono', monospace;
            font-size: 0.8rem;
            color: var(--txt-muted);
            background: var(--surface2);
            padding: 5px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .topbar-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--txt-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.18s;
        }

        .topbar-btn:hover {
            color: var(--txt);
            background: rgba(255, 255, 255, 0.08);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
            }

            50% {
                box-shadow: 0 0 0 6px rgba(34, 197, 94, 0.05);
            }
        }

        /* Hamburger for mobile */
        .sidebar-toggle {
            display: none;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--txt);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       MAIN CONTENT WRAPPER
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .admin-main {
            margin-left: var(--sidebar-w);
            margin-top: var(--topbar-h);
            flex: 1;
            min-height: calc(100vh - var(--topbar-h));
            background: var(--bg);
            padding: 32px 28px;
        }

        /* Page title */
        .page-heading {
            margin-bottom: 28px;
        }

        .page-heading h1 {
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--txt);
            letter-spacing: -0.3px;
        }

        .page-heading p {
            font-size: 0.85rem;
            color: var(--txt-muted);
            margin-top: 4px;
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       CARDS / SURFACES
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       FORM ELEMENTS
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--txt-muted);
            margin-bottom: 7px;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .form-label .req {
            color: var(--danger);
            margin-left: 2px;
        }

        .form-control {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            color: var(--txt);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(76, 142, 247, 0.12);
        }

        .form-control::placeholder {
            color: rgba(136, 146, 164, 0.5);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 110px;
            line-height: 1.6;
        }

        select.form-control {
            cursor: pointer;
        }

        .form-hint {
            font-size: 0.76rem;
            color: var(--txt-muted);
            margin-top: 5px;
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       BUTTONS
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 20px;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: #0041a8;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(0, 82, 204, 0.35);
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--txt-muted);
        }

        .btn-ghost:hover {
            background: var(--surface2);
            color: var(--txt);
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       ALERTS
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .alert i {
            margin-top: 1px;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #4ade80;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       DIVIDER
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 28px 0;
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       STAT CARDS (dashboard)
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: border-color 0.2s, transform 0.2s;
        }

        .stat-card:hover {
            border-color: rgba(76, 142, 247, 0.3);
            transform: translateY(-2px);
        }

        .stat-card .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .stat-card .stat-val {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--txt);
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: 0.78rem;
            color: var(--txt-muted);
            font-weight: 500;
        }

        .stat-card .stat-change {
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-card .stat-change.up {
            color: var(--success);
        }

        .stat-card .stat-change.muted {
            color: var(--txt-muted);
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       ACTION BOXES (dashboard)
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        }

        .action-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }

        .action-box:hover {
            border-color: rgba(76, 142, 247, 0.35);
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.25);
        }

        .action-box .box-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .action-box h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--txt);
        }

        .action-box p {
            font-size: 0.82rem;
            color: var(--txt-muted);
            line-height: 1.6;
            flex: 1;
        }

        .action-box .box-link {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 4px;
        }

        .action-box.disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .action-box.disabled:hover {
            transform: none;
            box-shadow: none;
            border-color: var(--border);
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       SECTION LABEL
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        .section-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--txt-muted);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       RESPONSIVE
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-topbar {
                left: 0;
            }

            .admin-main {
                margin-left: 0;
                padding: 20px 16px;
            }

            .sidebar-toggle {
                display: flex;
            }

            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stat-grid {
                grid-template-columns: 1fr 1fr;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }
        }

        /* â”€â”€ Admin tables (used by CRUD modules) â”€â”€ */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        .admin-table th {
            text-align: left;
            padding: 10px 14px;
            color: var(--txt-muted);
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid var(--border);
        }
        .admin-table td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .admin-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }
    </style>
</head>

<body>

    <!-- ── Sidebar ── -->
    <aside class="admin-sidebar" id="sidebar">
        <a href="<?php echo $admin_url; ?>index.php" class="sidebar-logo">
            <img src="<?php echo $base_url; ?>frontend/assets/images/logo.png" alt="Connect MyUni" class="logo-brand-img">
            <div class="logo-text">
                <strong>Connect MyUni</strong>
                <span>Admin Console</span>
            </div>
        </a>

        <div class="nav-group">
            <div class="nav-label">Main</div>
            <a class="nav-item <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>index.php">
                <i class="fas fa-squares-four fa-fw"></i> Dashboard
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-label">Content</div>
            <a class="nav-item <?php echo ($current_page === 'index.php' || strpos($_SERVER['REQUEST_URI'], 'events/') !== false) ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>events/">
                <i class="fas fa-calendar fa-fw"></i> Events
            </a>
            <a class="nav-item <?php echo strpos($_SERVER['REQUEST_URI'], 'countries') !== false ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>countries/">
                <i class="fas fa-globe fa-fw"></i> Countries
            </a>
            <a class="nav-item <?php echo strpos($_SERVER['REQUEST_URI'], 'universities') !== false ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>universities/">
                <i class="fas fa-university fa-fw"></i> Universities
            </a>
            <a class="nav-item <?php echo strpos($_SERVER['REQUEST_URI'], 'services') !== false ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>services/">
                <i class="fas fa-concierge-bell fa-fw"></i> Services
            </a>
            <a class="nav-item <?php echo strpos($_SERVER['REQUEST_URI'], 'hero-slides') !== false ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>hero-slides/">
                <i class="fas fa-images fa-fw"></i> Hero Slides
            </a>
            <a class="nav-item <?php echo strpos($_SERVER['REQUEST_URI'], 'gallery') !== false ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>gallery/">
                <i class="fas fa-photo-film fa-fw"></i> Gallery
            </a>
            <a class="nav-item <?php echo $current_page === 'contact-messages.php' ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>contact-messages.php">
                <i class="fas fa-envelope fa-fw"></i> Messages
                <?php
                $msgRepo = new \ConnectMyUni\Repositories\ContactMessageRepository();
                $newMsgs = $msgRepo->countByStatus('new');
                if ($newMsgs > 0): ?>
                    <span class="nav-badge"><?php echo $newMsgs; ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-item <?php echo strpos($_SERVER['REQUEST_URI'], 'settings') !== false ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>settings/">
                <i class="fas fa-cogs fa-fw"></i> Settings
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-label">Management</div>
            <a class="nav-item <?php echo strpos($_SERVER['REQUEST_URI'], 'analytics') !== false ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>analytics/">
                <i class="fas fa-chart-line fa-fw"></i> Analytics
            </a>
            <a class="nav-item <?php echo strpos($_SERVER['REQUEST_URI'], 'users') !== false ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>users/">
                <i class="fas fa-users fa-fw"></i> Manage Users
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-label">AI Studio</div>
            <a class="nav-item <?php echo ($current_page === 'ai/' || strpos($_SERVER['REQUEST_URI'], '/ai/') !== false) ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>ai/">
                <i class="fas fa-wand-magic-sparkles fa-fw"></i> AI Generator
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-label">Account</div>
            <a class="nav-item <?php echo $current_page === 'change-password.php' ? 'active' : ''; ?>" href="<?php echo $admin_url; ?>change-password.php">
                <i class="fas fa-key fa-fw"></i> Change Password
            </a>
            <a class="nav-item" href="<?php echo $base_url; ?>index.php" target="_blank">
                <i class="fas fa-globe fa-fw"></i> View Website
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['username'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="user-info">
                    <strong><?php echo htmlspecialchars($currentUser['username'] ?? 'Admin'); ?></strong>
                    <span><?php echo ucfirst($currentUser['role'] ?? 'Administrator'); ?></span>
                </div>
                <a href="<?php echo $admin_url; ?>logout.php" 
                   style="margin-left:auto;color:var(--txt-muted);font-size:0.85rem;" 
                   title="Logout"
                   onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- â”€â”€ Topbar â”€â”€ -->
    <div class="admin-topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-breadcrumb">
                <span>Admin</span>
                <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
                <span class="current">
                    <?php
                    $titles = ['index.php' => 'Dashboard'];
                    echo $titles[$current_page] ?? 'Page';
                    ?>
                </span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="status-dot" title="System online"></div>
            <div class="topbar-time" id="admin-clock">--:--:--</div>
            <a href="<?php echo $base_url; ?>index.php" class="topbar-btn" target="_blank" title="View site">
                <i class="fas fa-arrow-up-right-from-square"></i>
            </a>
        </div>
    </div>

    <main class="admin-main">

        <script>
            (function() {
                function tick() {
                    const t = new Date();
                    document.getElementById('admin-clock').textContent =
                        t.toLocaleTimeString('en-GB', {
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        });
                }
                tick();
                setInterval(tick, 1000);
            })();
        </script>
