<?php declare(strict_types=1);

require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\Ai\AiContentService;

use ConnectMyUni\Repositories\AiContentRequestRepository;

header('Content-Type: application/json');

// Authentication: AI endpoints require an authenticated admin session.
if (!AuthMiddleware::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Parse + size-limit the JSON body.
$raw = (string) (file_get_contents('php://input') ?: '');
if (strlen($raw) > 200000) {
    http_response_code(413);
    echo json_encode(['success' => false, 'error' => 'Request payload too large.']);
    exit;
}
$payload = $raw !== '' ? (json_decode($raw, true) ?? []) : [];

// Strict CSRF check. The UI sends the token in the X-CSRF-Token header
// (see ai-generator-core.php fetchJson()); falling back to the JSON body
// keeps the endpoint robust for any client that posts it in the payload.
$csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if ($csrfToken === '') {
    $csrfToken = (string) ($payload['csrf_token'] ?? '');
}
if ($csrfToken === '' || !Security::verifyCsrfToken($csrfToken)) {
    http_response_code(415);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
    exit;
}

$service   = new AiContentService(AiContentService::resolveProvider());
$admin_url = $admin_url ?? (defined('CONNECTMYUNI_BASE_URL') ? CONNECTMYUNI_BASE_URL . 'frontend/admin/' : '/');

// ---- Approval / history-fetch path (client supplies a requestId) ----
$requestId = isset($payload['requestId']) ? (int) $payload['requestId'] : 0;
if ($requestId > 0) {
    $approvedContent = array_key_exists('approvedContent', $payload) ? $payload['approvedContent'] : null;
    if (is_string($approvedContent)) {
        $approvedContent = json_decode($approvedContent, true);
    }
    if ($approvedContent !== null && !is_array($approvedContent)) {
        $approvedContent = null;
    }

    try {
        $repo = new AiContentRequestRepository();
        $row  = $repo->findById($requestId);
    } catch (\Throwable $e) {
        error_log('[AI generate] DB unavailable: ' . $e->getMessage());
        http_response_code(503);
        echo json_encode(['success' => false, 'error' => 'AI history is temporarily unavailable. Check server logs.']);
        exit;
    }

    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Request not found']);
        exit;
    }

    // Ownership guard: when both the originating admin and the session admin are
    // known, refuse cross-user access (defence in depth).
    $rowAdmin     = isset($row['admin_user_id']) ? (int) $row['admin_user_id'] : 0;
    $sessionAdmin = null;
    if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['admin_user_id'])) {
        $sessionAdmin = (int) $_SESSION['admin_user_id'];
    }
    if ($rowAdmin > 0 && $sessionAdmin !== null && $rowAdmin !== $sessionAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not authorised to access this request']);
        exit;
    }

    if ($approvedContent !== null) {
        // Approve: records approval history only; never writes to CRUD tables.
        try {
            $service->approveContent($requestId, $approvedContent);
        } catch (\Throwable $e) {
            error_log('[AI generate] Approve failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not record approval. Check server logs.']);
            exit;
        }
        echo json_encode(['success' => true, 'requestId' => $requestId, 'status' => 'approved']);
        exit;
    }

    // Fetch stored generated content (copy-to-clipboard of history rows).
    $content = $row['generated_content'] ?? null;
    $decoded = is_string($content) ? json_decode($content, true) : $content;
    if (is_string($content) && json_last_error() !== JSON_ERROR_NONE) {
        $decoded = $content; // raw-text fallback
    }
    echo json_encode([
        'success'   => true,
        'requestId' => $requestId,
        'status'    => $row['status'] ?? 'unknown',
        'data'      => $decoded,
    ]);
    exit;
}

// ---- Normal generation path ----
$contentType = isset($payload['content_type']) ? trim((string) $payload['content_type']) : '';
if ($contentType === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'content_type is required']);
    exit;
}

// Enforce the server-side allowlist (mirrors AiContentService::$fieldSchemas).
if (!in_array($contentType, AiContentService::allowedContentTypes(), true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unsupported content type: ' . $contentType]);
    exit;
}

// Build params, stripping control keys.
$params = [];
foreach ($payload as $k => $v) {
    if ($k === 'content_type' || $k === 'csrf_token') {
        continue;
    }
    if (is_string($v)) {
        $params[$k] = trim($v);
    } elseif (is_array($v)) {
        $params[$k] = $v;
    }
}

// Provider-config check is env-only (no DB): degrades gracefully even without a
// database connection.
if (!$service->isConfigured()) {
    echo json_encode([
        'success'    => false,
        'error'      => 'AI provider is not configured. Set AI_API_KEY in the server .env file.',
        'configured' => false,
    ]);
    exit;
}

try {
    $result = $service->generateContent($contentType, $params);
} catch (\Throwable $e) {
    error_log('[AI generate] Unhandled error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Generation failed. Please try again later.']);
    exit;
}

if (!empty($result['success']) && !empty($result['requestId'])) {
    $result['approve_url'] = $admin_url . 'ai/generate.php';
}

echo json_encode($result);
