<?php declare(strict_types=1);

require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\Ai\AiContentService;


ob_start();
header('Content-Type: application/json');

// Authentication: AI endpoints require an authenticated admin session.
if (!AuthMiddleware::check()) {
    if (ob_get_length()) ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Parse + size-limit the JSON body.
$raw = (string) (file_get_contents('php://input') ?: '');
if (strlen($raw) > 200000) {
    if (ob_get_length()) ob_clean();
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
    if (ob_get_length()) ob_clean();
    http_response_code(415);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
    exit;
}

// The UI posts the text to improve under `existing_content` (see
// improve() in ai-generator-core.php); accept `existing` as an alias.
// It can be a string or a JSON object/array.
$existingRaw = $payload['existing_content'] ?? ($payload['existing'] ?? '');
if (is_array($existingRaw)) {
    $existing = (string) json_encode($existingRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
    $existing = (string) $existingRaw;
}
$instruction = isset($payload['instruction']) ? (string) $payload['instruction'] : '';

// Context may arrive as a plain string or as an object carrying content_type
// (the UI sends { content_type: "..." }). Derive and validate content type.
$context     = '';
$contentType = '';
if (isset($payload['context'])) {
    if (is_array($payload['context'])) {
        $contentType = (string) ($payload['context']['content_type'] ?? '');
        $context     = (string) json_encode($payload['context']);
    } else {
        $context    = (string) $payload['context'];
        $decodedCtx = json_decode($context, true);
        if (is_array($decodedCtx)) {
            $contentType = (string) ($decodedCtx['content_type'] ?? '');
        }
    }
}
if ($contentType !== '' && !in_array($contentType, AiContentService::allowedContentTypes(), true)) {
    if (ob_get_length()) ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unsupported content type: ' . $contentType]);
    exit;
}

if ($existing === '' || $instruction === '') {
    if (ob_get_length()) ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'existing and instruction are required']);
    exit;
}

try {
    $service = new AiContentService(AiContentService::resolveProvider());
    $result  = $service->improveContent($existing, $instruction, $context);
} catch (\Throwable $e) {
    error_log('[AI improve] Unhandled error: ' . $e->getMessage());
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Improvement failed. Please try again later.']);
    exit;
}

if (ob_get_length()) ob_clean();
echo json_encode($result);
