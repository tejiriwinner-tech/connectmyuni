<?php

/**
 * Connect MyUni — Event Repository
 *
 * Handles all database operations for events.
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Repositories;

use ConnectMyUni\Database;
use PDO;

/**
 * Class EventRepository
 */
class EventRepository
{
    /**
     * Get all published events
     */
    public function getAllPublished(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM events 
            WHERE status = 'published' 
            ORDER BY event_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get event by ID
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();

        return $event ?: null;
    }

    /**
     * Get event by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM events WHERE slug = ?");
        $stmt->execute([$slug]);
        $event = $stmt->fetch();

        return $event ?: null;
    }

    /**
     * Get events by category
     */
    public function getByCategory(string $category): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM events 
            WHERE status = 'published' AND category = ? 
            ORDER BY event_date DESC
        ");
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }

    /**
     * Get all events (admin - includes drafts/latest first)
     */
    public function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM events ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming events
     */
    public function getUpcoming(int $limit = 6): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM events 
            WHERE status = 'published' AND event_date >= CURRENT_DATE
            ORDER BY event_date ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Create new event
     */
    public function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO events (
                title, slug, category, event_date, event_time, description,
                details, image_path, registration_link, location, duration,
                capacity, requirements, contact_info, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['category'],
            $data['event_date'],
            $data['event_time'] ?? null,
            $data['description'],
            $data['details'] ?? null,
            $data['image_path'] ?? null,
            $data['registration_link'] ?? null,
            $data['location'] ?? null,
            $data['duration'] ?? null,
            $data['capacity'] ?? null,
            $data['requirements'] ?? null,
            $data['contact_info'] ?? null,
            $data['status'] ?? 'published',
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Update event
     */
    public function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE events SET
                title = ?, slug = ?, category = ?, event_date = ?, event_time = ?,
                description = ?, details = ?, image_path = ?, registration_link = ?,
                location = ?, duration = ?, capacity = ?, requirements = ?,
                contact_info = ?, status = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['category'],
            $data['event_date'],
            $data['event_time'] ?? null,
            $data['description'],
            $data['details'] ?? null,
            $data['image_path'] ?? null,
            $data['registration_link'] ?? null,
            $data['location'] ?? null,
            $data['duration'] ?? null,
            $data['capacity'] ?? null,
            $data['requirements'] ?? null,
            $data['contact_info'] ?? null,
            $data['status'] ?? 'published',
            $id,
        ]);
    }

    /**
     * Delete event
     */
    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Increment view count
     */
    public function incrementViews(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE events SET views_count = views_count + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Get recent events for admin dashboard
     */
    public function getRecent(int $limit = 5): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM events 
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Count events by category
     */
    public function countByCategory(string $category): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM events 
            WHERE status = 'published' AND category = ?
        ");
        $stmt->execute([$category]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get total event count
     */
    public function countAll(): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE status = 'published'");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
