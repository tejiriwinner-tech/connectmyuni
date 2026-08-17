<?php declare(strict_types=1);

/**
 * Connect MyUni — Environment Safety Helpers
 *
 * Fail-closed guard for staging-only operations. Normal production
 * application code must NOT depend on these helpers; only explicitly
 * staging-scoped maintenance and verification operations call
 * requireStaging().
 *
 * Follows the existing configuration architecture (env() helper defined in
 * app.php) — no second configuration system is introduced.
 *
 * @package ConnectMyUni
 */

namespace ConnectMyUni\Config;

/**
 * Environment safety utilities (mirrors Security.php class conventions).
 */
final class Environment
{
    /**
     * True only when APP_ENV is explicitly "staging".
     */
    public static function isStaging(): bool
    {
        return env('APP_ENV', '') === 'staging';
    }

    /**
     * Fail-closed: refuse to continue a staging-only operation unless
     * APP_ENV is explicitly "staging".
     *
     * Throws instead of silently proceeding so a misconfigured environment
     * can never be mistaken for staging (which would risk targeting the
     * production database or production AI credentials).
     *
     * @throws \RuntimeException when the environment is not explicitly staging.
     */
    public static function requireStaging(): void
    {
        if (!self::isStaging()) {
            throw new \RuntimeException(
                'Staging-only operation refused: APP_ENV is not set to "staging". '
                . 'This operation must never run against the production environment.'
            );
        }
    }

    /** Production Supabase host that must never be targeted as staging. */
    private const PRODUCTION_HOST = 'aws-1-eu-west-3.pooler.supabase.com';

    /** Placeholder tokens that must never be used as a real staging host. */
    private const PLACEHOLDER_HOSTS = [
        'your_staging_host', 'your-staging-host', 'your_staging', 'your-staging',
        'your_host', 'your-host', 'your_hostname', 'your-hostname',
        'your_database', 'your-database',
        'your_username', 'your-username', 'your_password', 'your-password',
        'change_me', 'change-me', 'replace_me', 'replace-me',
        'example.com', 'example.org', 'db', 'database', 'host', 'hostname',
    ];

    /**
     * Fail-closed host validation for a staging database target.
     *
     * Returns a non-null reason string when the host MUST be rejected, or null
     * when it is not an obvious placeholder/production/localhost value.
     *
     * Reasons: 'empty' | 'production' | 'placeholder' | 'localhost'
     */
    public static function isRejectedStagingHost(string $host): ?string
    {
        $v = strtolower(trim($host));
        if ($v === '') {
            return 'empty';
        }
        if (stripos($host, self::PRODUCTION_HOST) !== false) {
            return 'production';
        }
        if (in_array($v, self::PLACEHOLDER_HOSTS, true)) {
            return 'placeholder';
        }
        if (str_contains($v, 'your_') || str_contains($v, 'your-')) {
            return 'placeholder';
        }
        if (preg_match('/[<>\s]/', $host)) {
            return 'placeholder';
        }
        if ($v === 'localhost' || $v === '127.0.0.1') {
            return 'localhost';
        }
        return null;
    }
}
