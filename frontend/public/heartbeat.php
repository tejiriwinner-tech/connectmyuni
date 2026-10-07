<?php
declare(strict_types=1);

/**
 * Connect MyUni — Web Heartbeat Endpoint
 * Used by cron services / ping bots to keep Supabase PostgreSQL active.
 */

require_once __DIR__ . '/../../backend/bootstrap.php';
use ConnectMyUni\Database;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$t0 = microtime(true);

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT NOW() as db_time");
    $time = $stmt->fetchColumn();
    $elapsed = round((microtime(true) - $t0) * 1000, 2);

    echo json_encode([
        'status' => 'healthy',
        'database' => 'connected',
        'timestamp' => $time,
        'response_ms' => $elapsed,
        'message' => 'Supabase is alive and active.'
    ], JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'database' => 'disconnected',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
