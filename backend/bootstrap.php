<?php

declare(strict_types=1);

/**
 * Connect MyUni — Backend Bootstrap
 *
 * Single entry point used by every frontend/admin/public page before any
 * backend class is used. It:
 *
 *  1. Loads application configuration (config/app.php, which also defines
 *     the ConnectMyUni\Config\env() helper used by db_config.php).
 *  2. Defines the canonical CONNECTMYUNI_BASE_URL constant.
 *  3. Registers the backend class autoloader (spl_autoload_register).
 *
 * The bootstrap is idempotent: it is safe to require from multiple entry
 * points in the same request.
 *
 * @package ConnectMyUni
 */

// Idempotency guard — return the previously loaded config if already run.
if (defined('CONNECTMYUNI_BOOTSTRAPPED')) {
    return $GLOBALS['connectmyuni_app_config'] ?? [];
}

// 1. Application configuration (returns array; defines env() helper).
$appConfig = require_once __DIR__ . '/config/app.php';

if (!is_array($appConfig)) {
    $appConfig = [];
}

// 2. Canonical base URL (single source of truth for path-aware output).
if (!defined('CONNECTMYUNI_BASE_URL')) {
    $baseUrl = $appConfig['base_url'] ?? '/';
    $trimmed = trim($baseUrl, '/');
    $baseUrl = $trimmed !== '' ? '/' . $trimmed . '/' : '/';
    define('CONNECTMYUNI_BASE_URL', $baseUrl);
}

// 3. Backend class autoloader.
//
// Prefix map covers the layered backend namespaces. Exact class map covers
// the two root-namespace classes whose files live outside the matching
// directory (Database in config/, CacheManager in helpers/).
spl_autoload_register(static function (string $class): void {
    $backendDir = __DIR__;

    $prefixes = [
        'ConnectMyUni\\Config\\'       => $backendDir . '/config',
        'ConnectMyUni\\Middleware\\'   => $backendDir . '/middleware',
        'ConnectMyUni\\Repositories\\' => $backendDir . '/repositories',
        'ConnectMyUni\\Services\\'     => $backendDir . '/services',
        'ConnectMyUni\\Helpers\\'      => $backendDir . '/helpers',
    ];

    $classMap = [
        'ConnectMyUni\\Database'     => $backendDir . '/config/Database.php',
        'ConnectMyUni\\CacheManager' => $backendDir . '/helpers/CacheManager.php',
    ];

    if (isset($classMap[$class])) {
        if (is_file($classMap[$class])) {
            require_once $classMap[$class];
        }
        return;
    }

    foreach ($prefixes as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $path = $dir . '/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($path)) {
                require_once $path;
            }
            return;
        }
    }
});

// Mark bootstrap complete and expose config for callers.
$GLOBALS['connectmyuni_app_config'] = $appConfig;
define('CONNECTMYUNI_BOOTSTRAPPED', true);

return $appConfig;