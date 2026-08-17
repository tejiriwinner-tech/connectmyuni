<?php declare(strict_types=1);

/**
 * Connect MyUni — Staging-only Base Schema Initializer (FAIL-CLOSED)
 *
 * Initializes the staging PostgreSQL/Supabase database with the base
 * ConnectMyUni schema from:
 *
 *   backend/database/schema/connectmyuni_initial_schema.sql
 *
 * (NOT the MySQL-oriented migrations/001_create_database_schema.sql.)
 *
 * Safety:
 *   - Refuses unless APP_ENV=staging (Environment::requireStaging()).
 *   - Refuses production / placeholder / localhost hosts.
 *   - Refuses missing process-environment staging credentials.
 *   - Rejects obvious MySQL constructs in the schema before running.
 *   - Runs entirely inside a transaction so any statement failure (e.g. a
 *     non-idempotent CREATE TRIGGER on an already-initialized DB) rolls back
 *     everything — no partial schema, no duplicates.
 *   - Never touches production, never runs AI, never runs migration 003.
 *
 * Usage (staging shell only):
 *   php backend/database/migration/initialize_staging_schema.php
 *
 * Exit codes:
 *   0 = schema applied + required base tables/columns verified
 *   2 = schema executed but a required table (e.g. ai_content_requests) missing
 *   1 = refused / safety check failure / connection error / rollback
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

// 2) Staging credentials MUST come from the process environment (never .env).
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

// 3) Reject production / placeholder / localhost hosts before connecting.
$host = (string) getenv('SUPABASE_DB_HOST');
$hostReason = Environment::isRejectedStagingHost($host);
if ($hostReason !== null) {
    exit($refuse(
        'Configured staging host is rejected (' . $hostReason . '); refusing to proceed. '
        . 'A real, separately provisioned staging database is required.'
    ));
}

// 4) Reject placeholder username/password tokens.
$placeholderUserPass = ['your_username','your-username','your_password','your-password','change_me','replace_me'];
foreach (['SUPABASE_DB_USERNAME' => 'username', 'SUPABASE_DB_PASSWORD' => 'password'] as $envKey => $label) {
    $val = strtolower(trim((string) getenv($envKey)));
    if (in_array($val, $placeholderUserPass, true) || str_contains($val, 'your_') || str_contains($val, 'your-')) {
        exit($refuse("Staging database $label is a placeholder; refusing to proceed."));
    }
}

// 5) Locate + read the PostgreSQL base schema.
$schemaFile = __DIR__ . '/../schema/connectmyuni_initial_schema.sql';
if (!is_file($schemaFile)) {
    fwrite(STDERR, "[ERROR] Base schema file not found: $schemaFile\n");
    exit(1);
}
$sql = file_get_contents($schemaFile);
if (!is_string($sql) || trim($sql) === '') {
    fwrite(STDERR, "[ERROR] Base schema file is empty.\n");
    exit(1);
}

// 6) Static safety check: reject obvious MySQL-specific constructs.
$mysqlMarkers = ['AUTO_INCREMENT', 'ENGINE=', 'CREATE DATABASE', 'USE connect_myuni', 'CHARSET=utf8mb4'];
foreach ($mysqlMarkers as $marker) {
    if (stripos($sql, $marker) !== false) {
        exit($refuse("Schema contains MySQL-specific construct '$marker'; refusing to apply as PostgreSQL schema."));
    }
}

echo "====================================\n";
echo "TARGET DATABASE: STAGING\n";
echo "TARGET HOST: " . $host . "\n";
echo "PRODUCTION DATABASE: NOT TARGETED\n";
echo "ACTION: INITIALIZE BASE POSTGRESQL SCHEMA\n";
echo "====================================\n";

// 7) Connect + apply inside a transaction (fail-closed on any error).
try {
    $pdo = ConnectMyUni\Database::getConnection();
} catch (\Throwable $e) {
    error_log('[AI init] Connection failed: ' . $e->getMessage());
    fwrite(STDERR, "[ERROR] Database connection failed. Check server logs for details.\n");
    exit(1);
}

try {
    $pdo->beginTransaction();
    $pdo->exec($sql);
    $pdo->commit();
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[AI init] Schema application failed: ' . $e->getMessage());
    fwrite(STDERR, "[ERROR] Base schema application failed; rolled back. Check server logs for details.\n");
    exit(1);
}

echo "[OK] Base PostgreSQL schema applied to staging (transactional).\n";

// 8) Verify required base tables exist.
$expectedTables = ['admin_users', 'countries', 'universities', 'services', 'events', 'ai_content_requests'];
$missing = [];
foreach ($expectedTables as $table) {
    $reg = $pdo->query("SELECT to_regclass('" . $table . "')")->fetchColumn();
    if ($reg === null) {
        $missing[] = $table;
    }
}
if ($missing !== []) {
    fwrite(STDERR, '[ERROR] Required base tables missing: ' . implode(', ', $missing) . ".\n");
    exit(2);
}

// 9) Verify ai_content_requests column set.
$aiCols = array_column(
    $pdo->query(
        "SELECT column_name FROM information_schema.columns WHERE table_name = 'ai_content_requests'"
    )->fetchAll(PDO::FETCH_ASSOC),
    'column_name'
);
$requiredAiCols = [
    'id', 'admin_user_id', 'content_type', 'prompt', 'generated_content',
    'approved_content', 'status', 'approved_at', 'created_at', 'updated_at',
];
$missingAi = array_diff($requiredAiCols, $aiCols);
if ($missingAi !== []) {
    fwrite(STDERR, '[ERROR] ai_content_requests missing columns: ' . implode(', ', $missingAi) . ".\n");
    exit(2);
}

echo "[OK] Required base tables present; ai_content_requests has the required columns.\n";
echo "Run: php backend/database/migration/verify_base_schema_staging.php\n";
exit(0);