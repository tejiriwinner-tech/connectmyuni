<?php

/**
 * Connect MyUni — University Repository
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Repositories;

use ConnectMyUni\Database;
use PDO;

/**
 * Class UniversityRepository
 */
class UniversityRepository
{
    /**
     * Get all universities with country
     */
    public function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT u.*, c.name as country_name, c.flag_emoji 
            FROM universities u 
            LEFT JOIN countries c ON u.country_id = c.id 
            ORDER BY u.sort_order ASC, u.name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get featured universities
     */
    public function getFeatured(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, c.name as country_name, c.flag_emoji 
            FROM universities u 
            LEFT JOIN countries c ON u.country_id = c.id 
            WHERE u.is_featured = TRUE
            ORDER BY u.sort_order ASC, u.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find university by ID
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, c.name as country_name 
            FROM universities u 
            LEFT JOIN countries c ON u.country_id = c.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $uni = $stmt->fetch();
        return $uni ?: null;
    }

    /**
     * Get universities by country
     */
    public function getByCountry(int $countryId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM universities 
            WHERE country_id = ? 
            ORDER BY sort_order ASC, name ASC
        ");
        $stmt->execute([$countryId]);
        return $stmt->fetchAll();
    }

    /**
     * Create university
     */
    public function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO universities (country_id, name, slug, location, description, logo_path, website_url, is_featured, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['country_id'],
            $data['name'],
            $data['slug'],
            $data['location'] ?? null,
            $data['description'] ?? null,
            $data['logo_path'] ?? null,
            $data['website_url'] ?? null,
            $data['is_featured'] ?? 0,
            $data['sort_order'] ?? 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Update university
     */
    public function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE universities 
            SET country_id = ?, name = ?, slug = ?, location = ?, description = ?, 
                logo_path = ?, website_url = ?, is_featured = ?, sort_order = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['country_id'],
            $data['name'],
            $data['slug'],
            $data['location'] ?? null,
            $data['description'] ?? null,
            $data['logo_path'] ?? null,
            $data['website_url'] ?? null,
            $data['is_featured'] ?? 0,
            $data['sort_order'] ?? 0,
            $id,
        ]);
    }

    /**
     * Delete university
     */
    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM universities WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
