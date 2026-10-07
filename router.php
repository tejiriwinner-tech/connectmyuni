<?php
// Simple router for PHP built-in server to mimic .htaccess behavior

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly
if (preg_match('/\.(?:css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
    $file = __DIR__ . $uri;
    if (file_exists($file)) {
        return false; // Let PHP serve the static file
    }
}

// SEO Crawlability Routes
if ($uri === '/robots.txt') {
    if (file_exists(__DIR__ . '/robots.txt')) {
        header('Content-Type: text/plain; charset=utf-8');
        readfile(__DIR__ . '/robots.txt');
        return true;
    }
}

if ($uri === '/sitemap.xml' || $uri === '/sitemap') {
    if (file_exists(__DIR__ . '/sitemap.php')) {
        include __DIR__ . '/sitemap.php';
        return true;
    }
}

if ($uri === '/heartbeat' || $uri === '/api/heartbeat') {
    if (file_exists(__DIR__ . '/frontend/public/heartbeat.php')) {
        include __DIR__ . '/frontend/public/heartbeat.php';
        return true;
    }
}

// Route admin requests to frontend/admin/ (support /admin, /admin/, /frontend/admin, /frontend/admin/)
if (strpos($uri, '/frontend/admin') === 0) {
    $sub = substr($uri, 15);
    $adminFile = __DIR__ . '/frontend/admin' . $sub;
    if (file_exists($adminFile)) {
        if (is_dir($adminFile) && substr($uri, -1) !== '/') {
            header('Location: ' . $uri . '/');
            exit;
        }
        if (is_dir($adminFile)) {
            $adminFile .= '/index.php';
        }
        if (file_exists($adminFile)) {
            include $adminFile;
            return true;
        }
    }
}

if (strpos($uri, '/admin') === 0) {
    $adminFile = __DIR__ . '/frontend/admin' . substr($uri, 6);
    if (file_exists($adminFile)) {
        if (is_dir($adminFile) && substr($uri, -1) !== '/') {
            header('Location: ' . $uri . '/');
            exit;
        }
        if (is_dir($adminFile)) {
            $adminFile .= '/index.php';
        }
        if (file_exists($adminFile)) {
            include $adminFile;
            return true;
        }
    }
}

// Route frontend/public requests
if (strpos($uri, '/frontend/public') === 0) {
    $sub = substr($uri, 16);
    $publicFile = __DIR__ . '/frontend/public' . $sub;
    if (file_exists($publicFile)) {
        if (is_dir($publicFile) && substr($uri, -1) !== '/') {
            header('Location: ' . $uri . '/');
            exit;
        }
        if (is_dir($publicFile)) {
            $publicFile .= '/index.php';
        }
        if (file_exists($publicFile)) {
            include $publicFile;
            return true;
        }
    }
}

// Route all other requests to frontend/public/
$publicFile = __DIR__ . '/frontend/public' . $uri;
if (file_exists($publicFile)) {
    if (is_dir($publicFile) && substr($uri, -1) !== '/') {
        header('Location: ' . $uri . '/');
        exit;
    }
    if (is_dir($publicFile)) {
        $publicFile .= '/index.php';
    }
    if (file_exists($publicFile)) {
        include $publicFile;
        return true;
    }
}

// 404 - Not Found
http_response_code(404);
echo "<h1>Not Found</h1>";
echo "<p>The requested resource " . htmlspecialchars($uri) . " was not found on this server.</p>";
return true;
