<?php

/**
 * Connect MyUni — Local Development Router
 *
 * Router script for PHP's built-in development server:
 *
 *   php -S 127.0.0.1:8000 router.php
 *
 * This mirrors the Apache .htaccess behaviour without requiring Apache/XAMPP:
 *   - Public pages (index.php, about.php, ...) are served from frontend/public/.
 *   - Existing files under frontend/ and storage/uploads/ are served directly.
 *   - Backend internals, .env, and storage (except uploads) are denied.
 *
 * The router serves files itself (instead of `return false`) so that the
 * optional "/ConnectMyUni" base prefix is correctly mapped to disk paths
 * under PHP's built-in server.
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

$uri      = $_SERVER['REQUEST_URI'] ?? '/';
$path     = parse_url($uri, PHP_URL_PATH) ?: '/';
$rootDir  = __DIR__;

// Normalise: strip the leading "/ConnectMyUni" prefix when present so the
// same APP_BASE_URL works on both the built-in server and Apache.
$basePrefix = '/ConnectMyUni';
if ($path === $basePrefix || str_starts_with($path, $basePrefix . '/')) {
    $path = substr($path, strlen($basePrefix));
    if ($path === '') {
        $path = '/';
    }
}

// ────────────────────────────────────────────────────────────
// Deny access to sensitive paths.
// ────────────────────────────────────────────────────────────
$deniedPrefixes = [
    '/backend/',
    '/storage/logs/',
    '/storage/cache/',
    '/.git/',
    '/.env',
    '/docs/',
];

foreach ($deniedPrefixes as $prefix) {
    if (str_starts_with($path, $prefix)) {
        http_response_code(404);
        echo '404 Not Found';
        return true;
    }
}

// ────────────────────────────────────────────────────────────
// Serve real files directly (assets, admin pages, uploads).
// ────────────────────────────────────────────────────────────
$realFile = $rootDir . $path;
if (is_file($realFile)) {
    $ext = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));

    // Execute PHP pages in-place (__DIR__-relative includes keep working).
    if ($ext === 'php') {
        require $realFile;
        return true;
    }

    // Serve static assets with a correct Content-Type.
    $mimeTypes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'webp'  => 'image/webp',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
        'json'  => 'application/json',
        'txt'   => 'text/plain',
    ];

    header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
    header('X-Content-Type-Options: nosniff');
    readfile($realFile);
    return true;
}

// ────────────────────────────────────────────────────────────
// Route public pages into frontend/public/.
// ────────────────────────────────────────────────────────────
$publicDir = $rootDir . '/frontend/public';

if ($path === '/' || $path === '/index.php') {
    require $publicDir . '/index.php';
    return true;
}

$page = basename($path);
if (preg_match('/^[A-Za-z0-9_-]+\.php$/', $page)) {
    $candidate = $publicDir . '/' . $page;
    if (is_file($candidate)) {
        require $candidate;
        return true;
    }
}

http_response_code(404);
echo '404 Not Found';
return true;