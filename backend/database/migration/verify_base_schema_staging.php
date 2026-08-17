<?php declare(strict_types=1);

/**
 * Connect MyUni — Read-only Staging Base Schema Verifier (FAIL-CLOSED)
 *
 * Reports whether the required ConnectMyUni base tables and the
 * ai_content_requests columns exist in the STAGING database. It never
 * modifies data, never runs DDL, never runs migration 003, never calls AI.
 *
 * Fail-closed: refuses unless APP_ENV=staging and the host is not the
 * production host / not a placeholder.
 *
 * Exit codes:
 *   0 = BASE SCHEMA STATUS: PASS
 *   1 = refused / connection error
 *   2 = one or more required tables or columns missing
 */

require_once __DIR__ . '/../../bootstrap.php';

use ConnectMyUni\Config\Environment;

$refuse = static function (string $message): int {
    fwrite(STDERR, "[REFUSED] " . $message . "\n");
    return 1;
};

try {
    Environment::requireStaging();
} catch (\RuntimeException $e) {
    exit($refuse($e->getMessage()));
}

$host = (string) getenv('SUPABASE_DB_HOST');
$hostReason = Environment::isRejectedStagingHost($host);
if ($hostReason !== null) {
    exit($refuse('Configured staging host is rejected (' . $hostReason . '); refusing to proceed.'));
}

try {
    $pdo = ConnectMyUni\Database::getConnection();
} catch (\Throwable $e) {
    error_log('[AI verify] Connection failed: ' . $e->getMessage());
    fwrite(STDERR, "[ERROR] Database connection failed. Check server logs for details.\n");
    exit(1);
}

echo "====================================\n";
echo "TARGET DATABASE: STAGING\n";
echo "HOST: " . $host . "\n";
echo "PRODUCTION: NOT TARGETED\n";
echo "====================================\n";
echo "STAGING BASE SCHEMA VERIFICATION\n";

$expectedTables = ['admin_users', 'countries', 'universities', 'services', 'events', 'ai_content_requests'];
$missingTables = [];
foreach ($expectedTables as $table) {
    $reg = $pdo->query("SELECT to_regclass('" . $table . "')")->fetchColumn();
    $present = $reg !== null;
    echo $table . ": " . ($present ? 'PRESENT' : 'MISSING') . "\n";
    if (!$present) {
        $missingTables[] = $table;
    }
}

echo "\nai_content_requests columns:\n";
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
$missingCols = [];
foreach ($requiredAiCols as $col) {
    $present = in_array($col, $aiCols, true);
    echo $col . ": " . ($present ? 'PRESENT' : 'MISSING') . "\n";
    if (!$present) {
        $missingCols[] = $col;
    }
}

if ($missingTables !== [] || $missingCols !== []) {
    fwrite(STDERR, '[STATUS] Base schema verification FAILED. Missing tables: '
        . implode(', ', $missingTables) . '. Missing ai_content_requests columns: '
        . implode(', ', $missingCols) . ".\n");
    echo "BASE SCHEMA STATUS: FAIL\n";
    exit(2);
}

echo "\nBASE SCHEMA STATUS: PASS\n";
exit(0);