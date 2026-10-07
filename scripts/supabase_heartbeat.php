<?php
declare(strict_types=1);

/**
 * Connect MyUni — Supabase PostgreSQL Heartbeat Runner
 * Prevents Supabase project from pausing due to inactivity.
 */

require_once __DIR__ . '/../backend/bootstrap.php';
use ConnectMyUni\Database;

$t0 = microtime(true);
echo "[" . date('Y-m-d H:i:s') . "] Starting Supabase Heartbeat...\n";

try {
    $pdo = Database::getConnection();
    
    // 1. Simple liveness query
    $stmt = $pdo->query("SELECT NOW() as db_time, version() as pg_version");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 2. Query table counts to keep table cache warm
    $stats = [];
    $tables = ['events', 'hero_slides', 'services', 'universities', 'testimonials', 'countries'];
    foreach ($tables as $table) {
        try {
            $c = $pdo->query("SELECT count(*) FROM {$table}")->fetchColumn();
            $stats[$table] = (int)$c;
        } catch (\Throwable $t) {
            // Table might not exist or be accessible
        }
    }
    
    $duration = round((microtime(true) - $t0) * 1000, 2);
    echo "? Heartbeat Successful in {$duration}ms\n";
    echo "  DB Timestamp: " . ($info['db_time'] ?? 'N/A') . "\n";
    echo "  Table Counts: " . json_encode($stats) . "\n";
    echo "? Supabase database is active and awake.\n";
    exit(0);
} catch (\Throwable $e) {
    echo "? Heartbeat Error: " . $e->getMessage() . "\n";
    exit(1);
}
