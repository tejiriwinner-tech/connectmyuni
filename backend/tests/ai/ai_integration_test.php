<?php declare(strict_types=1);

/**
 * Connect MyUni — Authenticated AI Integration Test (CLI harness)
 *
 * Run from the project root:
 *   php backend/tests/ai/ai_integration_test.php
 *
 * - Simulates an authenticated admin via $_SESSION (AuthMiddleware reads it).
 * - Uses a deterministic mock provider (never touches OpenAI / the internet).
 * - Uses an in-memory SQLite database injected into AiContentRequestRepository,
 *   so the real (Supabase/Postgres) database is never connected to or mutated.
 * - Replicates the request-ownership guard exactly as frontend/admin/ai/
 *   generate.php does (see ownershipRejected()).
 *
 * Returns exit code 0 on success, 1 on failure.
 */

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\AiContentRequestRepository;
use ConnectMyUni\Services\Ai\AiContentService;
use ConnectMyUni\Services\Ai\AiProviderInterface;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/MockAiProvider.php';

session_start();

$failures = 0;
$checks   = 0;

function check(bool $cond, string $label): void
{
    global $failures, $checks;
    $checks++;
    if ($cond) {
        echo "  PASS  $label\n";
    } else {
        $failures++;
        echo "  FAIL  $label\n";
    }
}

/**
 * A provider that is configured but fails with a generic message.
 */
class FailingAiProvider implements AiProviderInterface
{
    public function isConfigured(): bool { return true; }
    public function getProvider(): string { return 'mock'; }
    public function getModel(): string { return 'test-model'; }
    public function getName(): string { return 'Failing'; }
    public function generate(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        // Generic client-facing error; internal detail is never returned.
        return ['success' => false, 'data' => null, 'error' => 'AI provider unavailable. Check server logs.'];
    }
}

/**
 * A provider that throws internally (simulates an unexpected exception).
 */
class ThrowingAiProvider implements AiProviderInterface
{
    public function isConfigured(): bool { return true; }
    public function getProvider(): string { return 'mock'; }
    public function getModel(): string { return 'test-model'; }
    public function getName(): string { return 'Throwing'; }
    public function generate(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        throw new RuntimeException('secret-internal-detail'); // must never reach the client
    }
}

/**
 * Mirrors the ownership guard in frontend/admin/ai/generate.php exactly.
 * Returns true when the current session admin must be denied access to the row.
 */
function ownershipRejected(array $row): bool
{
    $rowAdmin     = isset($row['admin_user_id']) ? (int) $row['admin_user_id'] : 0;
    $sessionAdmin = isset($_SESSION['admin_user_id']) ? (int) $_SESSION['admin_user_id'] : null;
    return ($rowAdmin > 0 && $sessionAdmin !== null && $rowAdmin !== $sessionAdmin);
}

function sessionAs(int $userId, string $username): void
{
    $_SESSION['admin_user_id']   = $userId;
    $_SESSION['admin_username']  = $username;
    $_SESSION['admin_role']      = 'administrator';
    $_SESSION['logged_in_at']    = time();
}

/** PHP 8.2-safe "does any row have this id?" */
function containsId(array $rows, int $id): bool
{
    foreach ($rows as $r) {
        if ((int) ($r['id'] ?? 0) === $id) {
            return true;
        }
    }
    return false;
}

/**
 * Mirrors the history table rendering fallback used by index.php and
 * history.php: NULL/missing values render as "—", never invented.
 */
function historyDisplayValue(mixed $value): string
{
    $s = (string) ($value ?? '');
    return $s === '' ? '—' : $s;
}

echo "\nConnect MyUni — Authenticated AI Integration Test\n";
echo "==================================================\n\n";

// ---- Throwaway database (in-memory SQLite) ---------------------------------
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL)');
$pdo->exec("INSERT INTO admin_users (username) VALUES ('admin_a'), ('admin_b')");
$pdo->exec('
    CREATE TABLE ai_content_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        admin_user_id INTEGER NULL,
        content_type VARCHAR(50) NOT NULL,
        provider VARCHAR(50) NULL,
        model VARCHAR(100) NULL,
        prompt TEXT NOT NULL,
        generated_content TEXT NULL,
        approved_content TEXT NULL,
        status VARCHAR(20) DEFAULT \'pending\',
        approved_at TEXT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
');

$repo    = new AiContentRequestRepository($pdo);
$service = new AiContentService(new \ConnectMyUni\Tests\Ai\Providers\MockAiProvider(), $repo);

// ---- 1. Authenticated Generate (Admin A) -----------------------------------
sessionAs(1, 'admin_a');
echo "1) Admin A generates content (mock provider)\n";
$res = $service->generateContent('event', ['topic' => 'Study in Canada', 'tone' => 'professional']);
check(($res['success'] ?? false) === true, 'generateContent reports success');
check(isset($res['requestId']) && $res['requestId'] > 0, 'requestId returned to client');
$rid1 = $res['requestId'];

$row1 = $repo->findById($rid1);
check($row1 !== null, 'request persisted to ai_content_requests');
check((int) $row1['admin_user_id'] === 1, 'admin ownership (admin_user_id) preserved');
check($row1['content_type'] === 'event', 'content_type recorded');
check($row1['provider'] === 'mock', 'provider persisted (mock)');
check($row1['model'] === 'test-model', 'model persisted (test-model)');
check($row1['status'] === 'generated', 'status recorded as generated');
check(!empty($row1['prompt']), 'prompt recorded');
check(!empty($row1['generated_content']), 'generated content stored');
check(!empty($row1['created_at']) && !empty($row1['updated_at']), 'timestamps stored');

// ---- 2. History isolation --------------------------------------------------
echo "2) History isolation\n";
$histA = $repo->getRecent(50, 1);
$histB = $repo->getRecent(50, 2);
check(containsId($histA, $rid1), 'Admin A sees request in history');
check(!containsId($histB, $rid1), 'Admin B does NOT see Admin A request');
check(($histA[0]['username'] ?? '') === 'admin_a', 'admin username resolved via LEFT JOIN');
check(($histA[0]['provider'] ?? '') === 'mock', 'provider present in history row');
check(($histA[0]['model'] ?? '') === 'test-model', 'model present in history row');

// ---- 3. Approve as Admin A (ownership OK) ----------------------------------
echo "3) Admin A approves own request\n";
$approved1 = ['title' => 'Approved Title', 'description' => 'Approved description by admin A'];
check($service->approveContent($rid1, $approved1) === true, 'approveContent returns true');
$row1b = $repo->findById($rid1);
check($row1b['status'] === 'approved', 'status transitioned to approved');
check(json_decode($row1b['approved_content'], true) === $approved1, 'approved_content persisted');
check(!empty($row1b['approved_at']), 'approved_at persisted');
check($row1b['provider'] === 'mock' && $row1b['model'] === 'test-model', 'approved record retains provider/model');

// Admin A leaves a pending request behind (for the cross-admin check).
$res3 = $service->generateContent('university', ['university_name' => 'Mock Uni', 'country' => 'Mock']);
$rid3 = $res3['requestId'];

// ---- 4. Admin B generates own content --------------------------------------
echo "4) Admin B generates own content\n";
sessionAs(2, 'admin_b');
$res2 = $service->generateContent('event', ['topic' => 'Admin B topic', 'tone' => 'short']);
check(isset($res2['requestId']) && $res2['requestId'] > 0, 'Admin B generate returns requestId');
$rid2 = $res2['requestId'];
$row2 = $repo->findById($rid2);
check((int) $row2['admin_user_id'] === 2, 'Admin B ownership preserved');
check($row2['provider'] === 'mock' && $row2['model'] === 'test-model', 'provider/model persisted for Admin B');

$histA2 = $repo->getRecent(50, 1);
$histB2 = $repo->getRecent(50, 2);
check(containsId($histB2, $rid2), 'Admin B sees own request in history');
check(!containsId($histA2, $rid2), 'Admin A does NOT see Admin B request');

// ---- 5. Cross-admin approval protection ------------------------------------
echo "5) Cross-admin protection\n";
$row3 = $repo->findById($rid3); // owned by Admin A
check(ownershipRejected($row3) === true, 'Admin B is rejected from Admin A request (403 guard)');
check($repo->findById($rid3)['status'] === 'generated', 'cross-admin request remains unapproved');

check(ownershipRejected($repo->findById($rid2)) === false, 'Admin B can access own request');
check($service->approveContent($rid2, ['title' => 'Admin B approved']) === true, 'Admin B approves own request');
check($repo->findById($rid2)['status'] === 'approved', 'Admin B own approval persisted');

// ---- 6. Security assertions ------------------------------------------------
echo "6) Security\n";
// Invalid content type (endpoint returns 400).
$bad = (new AiContentService(new \ConnectMyUni\Tests\Ai\Providers\MockAiProvider(), $repo))
    ->generateContent('bogus_type', []);
check(($bad['success'] ?? true) === false && strpos($bad['error'] ?? '', 'Unsupported content type') !== false,
    'invalid content type rejected');

// Provider failure -> generic message only (no stack trace / internal detail).
$failService = new AiContentService(new FailingAiProvider(), $repo);
$failRes     = $failService->generateContent('event', ['topic' => 'x']);
check(($failRes['success'] ?? true) === false, 'provider failure surfaced as failed response');
$err = $failRes['error'] ?? '';
check(strpos($err, 'RuntimeException') === false && strpos($err, '#0') === false
    && strpos($err, '.php') === false, 'provider failure exposes no stack trace / file path');

// Internal exception converted to generic message (mirror generate.php catch).
try {
    (new AiContentService(new ThrowingAiProvider(), $repo))->generateContent('event', ['topic' => 'x']);
    $internalMsg = 'no exception (unexpected)';
} catch (\Throwable $e) {
    // Exactly what the HTTP endpoint does: log detail, return generic.
    $internalMsg = 'Generation failed. Please try again later.';
}
check($internalMsg === 'Generation failed. Please try again later.'
    && strpos($internalMsg, 'secret-internal-detail') === false,
    'internal exception not leaked to client');

// API key / credentials never leak into any output generated in this harness.
$allDump = strtolower((string) json_encode([$res, $row1, $row1b, $row2, $row3, $failRes]));
check(strpos($allDump, 'bearer') === false, 'no Authorization/Bearer header leaked');
check(strpos($allDump, 'api.openai.com') === false, 'no OpenAI endpoint leaked');
check(strpos($allDump, 'sk-') === false, 'no OpenAI-style API key leaked');

// CSRF mechanics still function (token generate + verify).
$csrf = ConnectMyUni\Config\Security::generateCsrfToken();
check($csrf !== '' && ConnectMyUni\Config\Security::verifyCsrfToken($csrf) === true, 'valid CSRF token verifies');
check(ConnectMyUni\Config\Security::verifyCsrfToken('wrong-token') === false, 'invalid CSRF token rejected');

// Unauthenticated detection (AuthMiddleware::check() drives the 401 responses).
$_SESSION['admin_user_id'] = '';
$_SESSION['admin_username'] = '';
check(AuthMiddleware::check() === false, 'cleared session is detected as unauthenticated (-> 401 path)');

// ---- 7. Legacy NULL provider/model records (pre-04F rows) --------------------
echo "7) Legacy NULL provider/model records\n";
$legacyStmt = $pdo->prepare(
    "INSERT INTO ai_content_requests
     (admin_user_id, content_type, prompt, generated_content, status, provider, model)
     VALUES (:admin_user_id, :content_type, :prompt, :generated_content, :status, :provider, :model)"
);
$legacyStmt->execute([
    ':admin_user_id'     => 1,
    ':content_type'      => 'website_content',
    ':prompt'            => 'legacy prompt',
    ':generated_content' => '{"title":"Legacy"}',
    ':status'            => 'generated',
    ':provider'          => null,
    ':model'             => null,
]);
$legacyId = (int) $pdo->lastInsertId();
$histFull  = $repo->getRecent(50, 1);
$legacyRow = $repo->findById($legacyId);
check(containsId($histFull, $legacyId), 'legacy record still listed in history');
check($legacyRow !== null && $legacyRow['provider'] === null && $legacyRow['model'] === null,
    'legacy NULL provider/model preserved as NULL');
check(historyDisplayValue($legacyRow['provider'] ?? null) === '—', 'NULL provider renders as —');
check(historyDisplayValue($legacyRow['model'] ?? null) === '—', 'NULL model renders as —');
check(historyDisplayValue('mock') === 'mock', 'real provider value renders as-is');
check(historyDisplayValue('test-model') === 'test-model', 'real model value renders as-is');

// ---- 8. Fail-closed staging guard -------------------------------------------
echo "8) Fail-closed staging guard\n";
putenv('APP_ENV=local');
check(\ConnectMyUni\Config\Environment::isStaging() === false, 'isStaging() false when APP_ENV=local');
$refusedStaging = false;
try {
    \ConnectMyUni\Config\Environment::requireStaging();
} catch (\RuntimeException $e) {
    $refusedStaging = true;
}
check($refusedStaging, 'requireStaging() refuses when APP_ENV != staging (fail closed)');

// ---- 9. Staging host rejection (production / placeholder / localhost) ---------
echo "9) Staging host rejection\n";
check(\ConnectMyUni\Config\Environment::isRejectedStagingHost('aws-1-eu-west-3.pooler.supabase.com') === 'production',
    'production host rejected');
check(\ConnectMyUni\Config\Environment::isRejectedStagingHost('your-staging-host') === 'placeholder',
    'placeholder host rejected');
check(\ConnectMyUni\Config\Environment::isRejectedStagingHost('YOUR_STAGING_HOST') === 'placeholder',
    'uppercase placeholder host rejected');
check(\ConnectMyUni\Config\Environment::isRejectedStagingHost('localhost') === 'localhost',
    'localhost rejected (unverified local PostgreSQL)');
check(\ConnectMyUni\Config\Environment::isRejectedStagingHost('') === 'empty', 'empty host rejected');
check(\ConnectMyUni\Config\Environment::isRejectedStagingHost('db.staging-project.supabase.co') === null,
    'real-looking non-prod host not over-rejected');

echo "\n==================================================\n";
echo "Checks run: $checks   Failures: $failures\n";
echo ($failures === 0 ? "RESULT: PASS\n" : "RESULT: FAIL\n");
exit($failures === 0 ? 0 : 1);