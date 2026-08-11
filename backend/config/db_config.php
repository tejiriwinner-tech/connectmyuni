<?php

declare(strict_types=1);

/**
 * Connect MyUni — Database Configuration Array
 *
 * Canonical database configuration loaded by Database.php.
 *
 * Reads Supabase PostgreSQL environment variables in priority order:
 *   SUPABASE_DB_* (new) -> DB_* (legacy) -> defaults.
 *
 * No credentials are stored here. All values come from environment
 * configuration via the project's env() helper (defined in app.php),
 * falling back to getenv() for CLI/standalone usage.
 *
 * @package ConnectMyUni
 */

// Resolve the environment loader. Prefer the project env() helper
// (reads .env from the project root). Fall back to getenv() so the
// config is still usable from CLI scripts that do not load app.php.
if (function_exists('ConnectMyUni\\Config\\env')) {
    $env = function (string $key, mixed $default = null): mixed {
        return \ConnectMyUni\Config\env($key, $default);
    };
} elseif (function_exists('env')) {
    $env = function (string $key, mixed $default = null): mixed {
        return env($key, $default);
    };
} else {
    $env = function (string $key, mixed $default = null): mixed {
        $value = getenv($key);
        return ($value === false) ? $default : $value;
    };
}

return [

    // Supabase PostgreSQL connection (SUPABASE_DB_* takes precedence,
    // legacy DB_* values retained for backward compatibility).
    'host'     => $env('SUPABASE_DB_HOST', $env('DB_HOST', '127.0.0.1')),
    'port'     => (int) $env('SUPABASE_DB_PORT', $env('DB_PORT', 5432)),
    'dbname'   => $env(
        'SUPABASE_DB_DATABASE',
        $env('SUPABASE_DB_NAME', $env('DB_NAME', 'postgres'))
    ),
    'username' => $env(
        'SUPABASE_DB_USERNAME',
        $env('SUPABASE_DB_USER', $env('DB_USER', 'postgres'))
    ),
    'password' => $env('SUPABASE_DB_PASSWORD', $env('DB_PASSWORD', '')),

    // SSL mode required by Supabase.
    'sslmode'  => $env('SUPABASE_DB_SSLMODE', 'require'),

    // PostgreSQL charset is database-configured; kept for reference.
    'charset'  => 'utf8',

    // PDO options shared by all connections.
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];