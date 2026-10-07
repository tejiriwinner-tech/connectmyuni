<?php
/**
 * Migration Runner for Settings Table
 * Run this script to add the settings table for social media links
 */

echo "\n========================================\n";
echo "Connect MyUni - Settings Table Migration\n";
echo "========================================\n\n";

require_once __DIR__ . '/../../bootstrap.php';

echo "[INFO] Connecting to database...\n";

try {
    $pdo = \ConnectMyUni\Database::getConnection();
    echo "[OK] Database connected\n\n";
} catch (\Throwable $e) {
    echo "[ERROR] Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[INFO] Creating settings table...\n";

try {
    // Read the migration SQL file
    $migrationSql = file_get_contents(__DIR__ . '/../migrations/004_add_settings_table.sql');

    if ($migrationSql === false) {
        throw new Exception("Could not read migration file");
    }

    // Execute the migration
    $pdo->exec($migrationSql);

    echo "[OK] Settings table created successfully\n";
    echo "[OK] Social media links configuration added\n";
    echo "[OK] General website settings added\n";

} catch (Exception $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[OK] Migration complete\n";
echo "========================================\n";
