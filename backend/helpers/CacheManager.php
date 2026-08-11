<?php

namespace ConnectMyUni;

/**
 * Cache Manager for Connect MyUni
 * Handles caching of data sources
 *
 * @deprecated Will be replaced by database queries in Stage 03+
 */

class CacheManager
{
    private static string $cacheDir = __DIR__ . '/data/.cache';
    private static int $cacheDuration = 3600; // 1 hour

    /**
     * Get cached data or load from source
     */
    public static function getEvents(): array
    {
        // Try both possible paths (due to historical bug)
        $possiblePaths = [
            dirname(__DIR__, 2) . '/data/events.json',
            __DIR__ . '/data/events.json',
        ];

        $sourceFile = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $sourceFile = $path;
                break;
            }
        }

        if (!$sourceFile) {
            return [];
        }

        $cacheFile = self::$cacheDir . '/events.cache.php';

        // Return cached data if valid
        if (self::isCacheValid($cacheFile, $sourceFile)) {
            return unserialize(file_get_contents($cacheFile));
        }

        // Load from source and cache it
        $data = json_decode(file_get_contents($sourceFile), true) ?? [];
        self::saveCache($cacheFile, $data);
        return $data;
    }

    /**
     * Get single event by ID
     */
    public static function getEventById(string $eventId): ?array
    {
        $events = self::getEvents();
        foreach ($events as $event) {
            // Support both 'id' and 'eventId' keys for compatibility
            $id = $event['id'] ?? $event['eventId'] ?? null;
            if ($id === $eventId) {
                return $event;
            }
        }
        return null;
    }

    /**
     * Clear cache for events
     */
    public static function clearEventsCache(): void
    {
        $cacheFile = self::$cacheDir . '/events.cache.php';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Check if cache is still valid
     */
    private static function isCacheValid(string $cacheFile, string $sourceFile): bool
    {
        if (!file_exists($cacheFile) || !file_exists($sourceFile)) {
            return false;
        }

        $cacheTime = filemtime($cacheFile);
        $sourceTime = filemtime($sourceFile);

        return (time() - $cacheTime) < self::$cacheDuration && $cacheTime >= $sourceTime;
    }

    /**
     * Save data to cache
     */
    private static function saveCache(string $cacheFile, array $data): void
    {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }

        file_put_contents($cacheFile, serialize($data));
    }
}
