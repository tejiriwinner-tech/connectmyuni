<?php declare(strict_types=1);

/**
 * Connect MyUni — Staging-only Migration 003 Applier (FAIL-CLOSED)
 *
 * Applies backend/database/migrations/003_add_ai_content_requests_provider_model.sql
 * to an explicitly identified STAGING database.
 *
 * Refuses to run unless ALL of the following hold:
 *   1. APP_ENV is explicitly "staging".
 *   2. Every staging database credential is supplied as a PROCESS environment
 *      variable (getenv) — never read from .env, which points to production.
 *   3. The configured host is NOT the known production Supabase host.
 *
 * Run from a staging shell only, e.g.:
 *   $env:APP_ENV='staging'
 *   $env:SUPABASE_DB_HOST='<staging db host>'
 *   $env:SUPABASE_DB_PORT='5432'
 *   $env:SUPABASE_DB_DATABASE='<staging db name>'
 *   $env:SUPABASE_DB_USERNAME='<staging user>'
 *   $env:SUPABASE_DB_PASSWORD='<staging secret>'
 *   php backend/database/migration/migrate_003_staging.php
 *
 * Never run with production .env values. No secrets are printed here.
 */

require_once __DIR__ . '/../../bootstrap.php';

use ConnectMyUni\Config\Environment;

$refuse = static function (string $message): int {
    fwrite(STDERR, "[REFUSED] " . $message . "\n");
    return 1;
};

// 1) Fail-closed APP_ENV guard.
try {
    Environment::requireStaging();
} catch (\RuntimeException $e) {
    exit($refuse($e->getMessage()));
}

// 2) Staging DB credentials MUST come from the process environment (an
//    explicitly staging-specific source). Fallback to .env would be production.
$required = [
    'SUPABASE_DB_HOST',
    'SUPABASE_DB_PORT',
    'SUPABASE_DB_DATABASE',
    'SUPABASE_DB_USERNAME',
    'SUPABASE_DB_PASSWORD',
];
foreach ($required as $key) {
    $value = getenv($key);
    if ($value === false || trim($value) === '') {
        exit($refuse("Staging database credential '${key}' is not set as a process environment variable."));
    }
}

// 3) Fail-closed staging host validation (production, placeholders, localhost)
//    runs BEFORE any connection attempt, so a placeholder can never reach DNS/PDO.
$configuredHost = (string) getenv('SUPABASE_DB_HOST');
$reason = Environment::isRejectedStagingHost($configuredHost);
if ($reason !== null) {
    exit($refuse(
        'Configured staging host is rejected (' . $reason . '); refusing to proceed. '
        . 'A real, separately provisioned staging database is required.'
    ));
}

// 4) Reject placeholder username/password tokens before connecting.
$placeholderUserPass = ['your_username','your-username','your_password','your-password','change_me','replace_me'];
foreach (['SUPABASE_DB_USERNAME' => 'username', 'SUPABASE_DB_PASSWORD' => 'password'] as $envKey => $label) {
    $val = strtolower(trim((string) getenv($envKey)));
    if (in_array($val, $placeholderUserPass, true) || str_contains($val, 'your_') || str_contains($val, 'your-')) {
        exit($refuse("Staging database $label is a placeholder; refusing to proceed."));
    }
}

echo "====================================\n";
echo "TARGET DATABASE: STAGING\n";
echo "PRODUCTION DATABASE: NOT TARGETED\n";
echo "MIGRATION: 003\n";
echo "ACTION: APPLY\n";
echo "====================================\n";

// Connect using the staging process-environment configuration.
$pdo   = ConnectMyUni\Database::getConnection();
$host  = (string) getenv('SUPABASE_DB_HOST');
$db    = (string) getenv('SUPABASE_DB_DATABASE');
echo "TARGET: " . $host . " / " . $db . "\n";
echo "PRODUCTION: NOT TARGETED\n";

$migration = __DIR__ . '/../migrations/003_add_ai_content_requests_provider_model.sql';
if (!is_file($migration)) {
    fwrite(STDERR, "[ERROR] Migration file not found: $migration\n");
    exit(1);
}

$sql = file_get_contents($migration);
if (!is_string($sql) || trim($sql) === '') {
    fwrite(STDERR, "[ERROR] Migration 003 is empty.\n");
    exit(1);
}

$pdo->exec($sql);
echo "[OK] Migration 003 applied to staging.\n";

// Post-apply verification: first confirm the base table exists. Migration 003
// is ALTER-only, so if the table is missing the ALTER was a silent no-op and
// no columns (nor table) were actually created.
$tableCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'ai_content_requests'"
)->fetchColumn();
if ($tableCount === 0) {
    fwrite(
        STDERR,
        "[ERROR] ai_content_requests table does not exist in staging. Migration 003 is an "
        . "ALTER-only migration and requires the base table (from the base schema / migration 001). "
        . "Apply the base ConnectMyUni schema to staging first; migration 003 added nothing. "
        . "Production was not touched.\n"
    );
    exit(1);
}

// Verify the columns exist via PostgreSQL metadata.
$stmt = $pdo->query(
    "SELECT column_name, is_nullable, data_type
       FROM information_schema.columns
      WHERE table_name = 'ai_content_requests'
        AND column_name IN ('provider','model')
      ORDER BY column_name"
);
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

$have = [];
foreach ($cols as $col) {
    $have[$col['column_name']] = $col;
}

if (isset($have['provider']) && $have['provider']['is_nullable'] === 'YES'
    && isset($have['model']) && $have['model']['is_nullable'] === 'YES'
) {
    echo "[OK] provider + model columns exist on ai_content_requests (nullable).\n";
    exit(0);
}

fwrite(STDERR, "[ERROR] ai_content_requests exists but provider/model columns were not verified on staging.\n");
exit(1);