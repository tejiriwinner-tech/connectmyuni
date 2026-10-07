<?php declare(strict_types=1);

require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\Ai\AiImageService;

header('Content-Type: application/json');

// Authentication required
if (!AuthMiddleware::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Parse body
$raw = (string) (file_get_contents('php://input') ?: '');
$payload = $raw !== '' ? (json_decode($raw, true) ?? []) : [];

// CSRF check
$csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if ($csrfToken === '') {
    $csrfToken = (string) ($payload['csrf_token'] ?? '');
}
if ($csrfToken === '' || !Security::verifyCsrfToken($csrfToken)) {
    http_response_code(415);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
    exit;
}

$service = new AiImageService();
$category = trim((string) ($payload['category'] ?? 'events'));

// Option 1: Picked from High-Resolution Real Education Stock Photos
$photoKey = trim((string) ($payload['photo_key'] ?? ''));
if ($photoKey !== '') {
    $result = $service->fetchStockPhoto($photoKey, $category);
    echo json_encode($result);
    exit;
}

// Option 2: Generated with AI Photoreal
$prompt = trim((string) ($payload['prompt'] ?? ''));
if ($prompt === '') {
    echo json_encode(['success' => false, 'error' => 'Please provide an event topic or select a photo.']);
    exit;
}

$result = $service->generateFlyer($prompt, $category);
echo json_encode($result);
