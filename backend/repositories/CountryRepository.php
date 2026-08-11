<?php

/**
 * Connect MyUni — Country Repository
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Repositories;

use ConnectMyUni\Database;
use PDO;

/**
 * Class CountryRepository
 */
class CountryRepository
{
    /**
     * Get all countries
     */
    public function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM countries ORDER BY sort_order ASC, name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get featured countries
     */
    public function getFeatured(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM countries WHERE is_featured = TRUE ORDER BY sort_order ASC, name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find country by ID
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM countries WHERE id = ?");
        $stmt->execute([$id]);
        $country = $stmt->fetch();
        return $country ?: null;
    }

    /**
     * Find country by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM countries WHERE slug = ?");
        $stmt->execute([$slug]);
        $country = $stmt->fetch();
        return $country ?: null;
    }

    /**
     * Create country
     */
    public function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO countries (name, slug, flag_emoji, description, is_featured, sort_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['flag_emoji'] ?? null,
            $data['description'] ?? null,
            $data['is_featured'] ?? 0,
            $data['sort_order'] ?? 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Update country
     */
    public function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE countries 
            SET name = ?, slug = ?, flag_emoji = ?, description = ?, is_featured = ?, sort_order = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['flag_emoji'] ?? null,
            $data['description'] ?? null,
            $data['is_featured'] ?? 0,
            $data['sort_order'] ?? 0,
            $id,
        ]);
    }

    /**
     * Delete country
     */
    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM countries WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
