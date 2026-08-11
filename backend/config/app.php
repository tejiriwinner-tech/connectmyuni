<?php

/**
 * Connect MyUni — Application Configuration
 *
 * Centralized app settings that do not contain sensitive data.
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Config;

/**
 * Helper function to get environment variables
 *
 * Resolution order:
 *   1. Real process environment (getenv) — supports CLI/container-injected
 *      variables and takes precedence over the project .env file.
 *   2. Values parsed from the project .env file (cached in-process).
 *   3. The supplied default.
 */
if (!function_exists('ConnectMyUni\Config\env')) {
    function env(string $key, mixed $default = null): mixed {
        // Real environment first (never cached, so putenv()/setenv()/export
        // all work immediately from CLI, cron, containers, and unit tests).
        $real = getenv($key);
        if ($real !== false) {
            return $real;
        }

        static $envVars = null;

        if ($envVars === null) {
            $envFile = dirname(__DIR__, 2) . '/.env';
            $envVars = [];

            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) {
                        continue;
                    }

                    $parts = explode('=', $line, 2);
                    if (count($parts) === 2) {
                        $envVars[trim($parts[0])] = trim($parts[1]);
                    }
                }
            }
        }

        return $envVars[$key] ?? $default;
    }
}

/**
 * Get application configuration
 */
return [

    // Application
    'name'    => 'Connect MyUni',
    'env'     => env('APP_ENV', 'local'),
    'debug'   => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
    'url'     => 'http://localhost/ConnectMyUni',
    'base_url' => env('APP_BASE_URL', '/ConnectMyUni/'),

    // Session
    'session' => [
        'name'     => env('SESSION_NAME', 'connectmyuni_session'),
        'lifetime' => (int) env('SESSION_LIFETIME', 1440),
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    // Paths
    'paths' => [
        'root'         => dirname(__DIR__, 2),
        'uploads'      => dirname(__DIR__, 2) . '/storage/uploads',
        'cache'        => dirname(__DIR__, 2) . '/storage/cache',
        'json_data'    => dirname(__DIR__, 2) . '/data',
        'legacy_cache' => dirname(__DIR__, 2) . '/includes/data',
    ],

    // Security
    'security' => [
        'csrf_token_name' => 'csrf_token',
        'csrf_token_lifetime' => 1800, // 30 minutes
        'max_upload_size' => 5 * 1024 * 1024, // 5MB
        'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],

    // Pagination
    'pagination' => [
        'per_page' => 12,
    ],

    // Cache
    'cache' => [
        'events_ttl' => 3600, // 1 hour
    ],

];
