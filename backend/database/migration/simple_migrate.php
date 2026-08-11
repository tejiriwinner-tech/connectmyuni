<?php
declare(strict_types=1);

echo "\n========================================\n";
echo "Connect MyUni - Simple Migration\n";
echo "========================================\n\n";

$config = require __DIR__ . '/../../config/database.php';

echo "[INFO] Connecting to database...\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    echo "[OK] Database connected\n\n";
} catch (PDOException $e) {
    echo "[ERROR] Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[INFO] Checking events.json...\n";
$eventsFile = __DIR__ . '/../data/events.json';
if (!file_exists($eventsFile)) {
    echo "[WARN] events.json not found\n";
    exit(0);
}

$events = json_decode(file_get_contents($eventsFile), true);
if (empty($events)) {
    echo "[WARN] events.json is empty\n";
    exit(0);
}

echo "[OK] Found " . count($events) . " events\n";

$migrated = 0;
$skipped = 0;

foreach ($events as $event) {
    $eventId = $event['eventId'] ?? $event['id'] ?? null;
    if (!$eventId) {
        continue;
    }
    
    $title = $event['title'] ?? 'untitled';
    $date = $event['date'] ?? date('Y-m-d');
    
    $stmt = $pdo->prepare("SELECT id FROM events WHERE event_id = ?");
    $stmt->execute([$eventId]);
    if ($stmt->fetch()) {
        $skipped++;
        continue;
    }
    
    try {
        $slug = strtolower(str_replace([' ', ',', '.', '!', '?'], '-', $title)) . '-' . $date;
        $slug = preg_replace('/-+/', '-', $slug);
        
        $stmt = $pdo->prepare("
            INSERT INTO events (event_id, title, slug, category, event_date, description, image_path, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $eventId,
            $title,
            $slug,
            $event['category'] ?? 'announcement',
            $date,
            $event['description'] ?? '',
            $event['image'] ?? 'asset/image1.png',
            'published'
        ]);
        
        $migrated++;
    } catch (Exception $e) {
        echo "[ERROR] Failed: " . $e->getMessage() . "\n";
    }
}

echo "\n[OK] Migration complete\n";
echo "      Migrated: $migrated\n";
echo "      Skipped: $skipped\n";
echo "========================================\n";
