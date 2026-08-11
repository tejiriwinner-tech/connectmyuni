<?php

/**
 * Connect MyUni — Service Repository
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Repositories;

use ConnectMyUni\Database;
use PDO;

/**
 * Class ServiceRepository
 */
class ServiceRepository
{
    /**
     * Get all active services
     */
    public function getAllActive(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC, title ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get all services (including inactive)
     */
    public function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, title ASC");
        return $stmt->fetchAll();
    }

    /**
     * Find service by ID
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch();
        return $service ?: null;
    }

    /**
     * Find service by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM services WHERE slug = ?");
        $stmt->execute([$slug]);
        $service = $stmt->fetch();
        return $service ?: null;
    }

    /**
     * Create service
     */
    public function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO services (title, slug, description, icon_class, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['description'] ?? null,
            $data['icon_class'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Update service
     */
    public function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE services 
            SET title = ?, slug = ?, description = ?, icon_class = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['description'] ?? null,
            $data['icon_class'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            $id,
        ]);
    }

    /**
     * Delete service
     */
    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
