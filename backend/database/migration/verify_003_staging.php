<?php declare(strict_types=1);

/**
 * Connect MyUni — Read-only Staging Verification for Migration 003
 *
 * Checks whether ai_content_requests.provider / .model exist in the STAGING
 * database, using PostgreSQL metadata only. It NEVER modifies data, never
 * applies migration 003, and never runs DDL.
 *
 * Fail-closed: refuses unless APP_ENV=staging and the host is not the
 * production host / not a placeholder.
 *
 * Exit codes:
 *   0 = provider + model exist and are nullable
 *   3 = table exists but provider/model are absent
 *   2 = ai_content_requests table does not exist (base schema not applied)
 *   other non-zero = refused / connection error
 */

require_once __DIR__ . '/../../bootstrap.php';

use ConnectMyUni\Config\Environment;

$fail = static function (string $message): int {
    fwrite(STDERR, "[REFUSED] " . $message . "\n");
    return 1;
};

try {
    Environment::requireStaging();
} catch (\RuntimeException $e) {
    exit($fail($e->getMessage()));
}

$host   = (string) getenv('SUPABASE_DB_HOST');
$reason = Environment::isRejectedStagingHost($host);
if ($reason !== null) {
    exit($fail('Configured staging host is rejected (' . $reason . '); refusing to proceed.'));
}

$pdo   = ConnectMyUni\Database::getConnection();
$db    = (string) $pdo->query('SELECT current_database()')->fetchColumn();
$host  = (string) getenv('SUPABASE_DB_HOST');

echo "====================================\n";
echo "TARGET DATABASE: STAGING\n";
echo "HOST: " . $host . "\n";
echo "DATABASE: " . $db . "\n";
echo "PRODUCTION: NOT TARGETED\n";
echo "====================================\n";

// 1) Does the base table exist anywhere visible to the current role?
$tableCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'ai_content_requests'"
)->fetchColumn();
$regClass   = $pdo->query("SELECT to_regclass('ai_content_requests')")->fetchColumn();

echo "ai_content_requests tables (information_schema): " . $tableCount . "\n";
echo "to_regclass('ai_content_requests'): " . var_export($regClass, true) . "\n";

if ($tableCount === 0 || $regClass === null) {
    fwrite(
        STDERR,
        "[STATUS] ai_content_requests table DOES NOT EXIST in staging. Migration 003 is an "
        . "ALTER-only migration and requires the base table (from the base schema / migration "
        . "001). Apply the base ConnectMyUni schema to staging first; migration 003 cannot add "
        . "columns to a missing table. No columns were applied; production was not touched.\n"
    );
    exit(2);
}

// 2) Check provider/model columns.
$cols = $pdo->query(
    "SELECT column_name, data_type, character_maximum_length, is_nullable
       FROM information_schema.columns
      WHERE table_name = 'ai_content_requests'
        AND column_name IN ('provider','model')
      ORDER BY column_name"
)->fetchAll(PDO::FETCH_ASSOC);

$have = [];
foreach ($cols as $c) {
    $have[$c['column_name']] = $c;
}
$provider = $have['provider'] ?? null;
$model    = $have['model'] ?? null;

if (!$provider && !$model) {
    fwrite(STDERR, "[STATUS] ai_content_requests exists but provider and model columns are both ABSENT.\n");
    exit(3);
}

if ($provider) {
    echo "provider | data_type=" . $provider['data_type']
        . " | max_len=" . var_export($provider['character_maximum_length'], true)
        . " | nullable=" . $provider['is_nullable'] . "\n";
}
if ($model) {
    echo "model    | data_type=" . $model['data_type']
        . " | max_len=" . var_export($model['character_maximum_length'], true)
        . " | nullable=" . $model['is_nullable'] . "\n";
}

$ok = $provider && $model
    && $provider['is_nullable'] === 'YES'
    && $model['is_nullable'] === 'YES';

if (!$ok) {
    fwrite(STDERR, "[ERROR] provider/model columns present but not both nullable; aborting.\n");
    exit(4);
}

echo "[OK] ai_content_requests.provider + .model exist and are nullable (STAGING).\n";
exit(0);