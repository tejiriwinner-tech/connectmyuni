<?php
declare(strict_types=1);

/**
 * Connect MyUni — JSON to MySQL Migration Tool
 *
 * Migrates existing JSON data to MySQL database.
 * Safe to run multiple times (idempotent).
 *
 * Usage: php database/migration/migrate_json_to_mysql.php
 *
 * @package ConnectMyUni
 */

if (php_sapi_name() === 'cli') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/Database.php';

use ConnectMyUni\Database;

class MigrationLogger
{
    private array $logs = [];
    private int $successCount = 0;
    private int $errorCount = 0;

    public function success(string $message): void
    {
        $this->successCount++;
        echo "[OK] $message`n";
    }

    public function error(string $message): void
    {
        $this->errorCount++;
        echo "[ERROR] $message`n";
    }

    public function warning(string $message): void
    {
        echo "[WARN] $message`n";
    }

    public function info(string $message): void
    {
        echo "[INFO] $message`n";
    }

    public function summary(): void
    {
        echo "`n========================================`n";
        echo "Migration Summary`n";
        echo "========================================`n";
        echo "Successful: {$this->successCount}`n";
        echo "Errors: {$this->errorCount}`n";
        echo "========================================`n";
    }
}

function runMigration(): void
{
    $logger = new MigrationLogger();

    echo "`n========================================`n";
    echo "Connect MyUni - JSON to MySQL Migration`n";
    echo "========================================`n`n";

    $logger->info("Testing database connection...");
    try {
        $pdo = Database::getConnection();
        $logger->success("Database connection established");
    } catch (Exception $e) {
        $logger->error("Database connection failed: " . $e->getMessage());
        $logger->summary();
        exit(1);
    }

    $logger->info("Checking database tables...");
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $requiredTables = ['events', 'event_registrations'];
        $missingTables = array_diff($requiredTables, $tables);

        if (!empty($missingTables)) {
            $logger->error("Missing tables: " . implode(', ', $missingTables));
            $logger->warning("Run database/migrations/001_create_database_schema.sql first");
            $logger->summary();
            exit(1);
        }

        $logger->success("All required tables exist");
    } catch (Exception $e) {
        $logger->error("Failed to check tables: " . $e->getMessage());
        $logger->summary();
        exit(1);
    }

    $logger->info("Migrating events from JSON...");
    migrateEvents($pdo, $logger);

    $logger->info("Migrating registrations from JSON...");
    migrateRegistrations($pdo, $logger);

    $logger->summary();
}

function migrateEvents(\PDO $pdo, MigrationLogger $logger): void
{
    $possiblePaths = [
        __DIR__ . '/../../data/events.json',
        __DIR__ . '/../../includes/data/events.json',
    ];

    $eventsFile = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $eventsFile = $path;
            break;
        }
    }

    if (!$eventsFile) {
        $logger->warning("events.json not found - skipping");
        return;
    }

    $logger->info("Found events.json at: $eventsFile");

    $jsonContent = @file_get_contents($eventsFile);
    if ($jsonContent === false) {
        $logger->error("Failed to read events.json");
        return;
    }

    $events = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $logger->error("Invalid JSON: " . json_last_error_msg());
        return;
    }

    if (empty($events)) {
        $logger->warning("events.json is empty - no events to migrate");
        return;
    }

    $migrated = 0;
    $skipped = 0;
    $errors = 0;

    foreach ($events as $event) {
        $eventId = $event['eventId'] ?? $event['id'] ?? null;
        if (!$eventId) {
            $logger->error("Event missing ID - skipping");
            $errors++;
            continue;
        }

        $title = $event['title'] ?? 'untitled';
        $date = $event['date'] ?? date('Y-m-d');
        $slug = strtolower(str_replace([' ', ',', '.', '!', '?'], '-', $title)) . '-' . $date;
        $slug = preg_replace('/-+/', '-', $slug);

        $stmt = $pdo->prepare("SELECT id FROM events WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $skipped++;
            continue;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO events (
                    title, slug, category, event_date, description, details,
                    image_path, registration_link, duration, capacity,
                    requirements, contact_info, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $title,
                $slug,
                $event['category'] ?? 'announcement',
                $date,
                $event['description'] ?? '',
                $event['details'] ?? '',
                $event['image'] ?? 'asset/image1.png',
                $event['registration_link'] ?? null,
                $event['duration'] ?? null,
                $event['capacity'] ?? null,
                $event['requirements'] ?? null,
                $event['contact_info'] ?? null,
                'published',
            ]);

            $migrated++;
        } catch (Exception $e) {
            $logger->error("Failed to migrate event '$title': " . $e->getMessage());
            $errors++;
        }
    }

    $logger->success("Events migrated: $migrated | Skipped: $skipped | Errors: $errors");
}

function migrateRegistrations(\PDO $pdo, MigrationLogger $logger): void
{
    $registrationsFile = __DIR__ . '/../../data/registrations.json';

    if (!file_exists($registrationsFile)) {
        $logger->warning("registrations.json not found - skipping");
        return;
    }

    $jsonContent = @file_get_contents($registrationsFile);
    if ($jsonContent === false) {
        $logger->error("Failed to read registrations.json");
        return;
    }

    $registrations = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $logger->error("Invalid JSON: " . json_last_error_msg());
        return;
    }

    if (empty($registrations)) {
        $logger->warning("registrations.json is empty - no registrations to migrate");
        return;
    }

    $migrated = 0;
    $skipped = 0;
    $errors = 0;

    foreach ($registrations as $registration) {
        $createdAt = $registration['registrationDate'] ?? date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("SELECT id FROM event_registrations WHERE email = ? AND created_at = ?");
        $stmt->execute([
            $registration['email'] ?? '',
            $createdAt
        ]);
        if ($stmt->fetch()) {
            $skipped++;
            continue;
        }

        $eventId = $registration['eventId'] ?? null;
        $dbEventId = 1;

        if ($eventId) {
            $stmt = $pdo->prepare("SELECT id FROM events WHERE slug LIKE ? LIMIT 1");
            $stmt->execute(["%$eventId%"]);
            $dbEventId = $stmt->fetchColumn() ?: 1;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO event_registrations (
                    event_id, full_name, email, phone, field_of_study,
                    event_location, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $dbEventId,
                $registration['fullName'] ?? 'Unknown',
                $registration['email'] ?? '',
                $registration['phone'] ?? '',
                $registration['fieldOfStudy'] ?? '',
                $registration['eventLocation'] ?? '',
                $createdAt,
            ]);

            $migrated++;
        } catch (Exception $e) {
            $logger->error("Failed to migrate registration: " . $e->getMessage());
            $errors++;
        }
    }

    $logger->success("Registrations migrated: $migrated | Skipped: $skipped | Errors: $errors");
}

runMigration();

