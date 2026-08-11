<?php

/**
 * Connect MyUni — Gallery Image Repository
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Repositories;

use ConnectMyUni\Database;
use PDO;

/**
 * Class GalleryRepository
 */
class GalleryRepository
{
    /**
     * Get all active gallery images
     */
    public function getActive(int $limit = 20): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM gallery_images 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get all gallery images
     */
    public function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM gallery_images ORDER BY sort_order ASC, created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Find gallery image by ID
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetch();
        return $image ?: null;
    }

    /**
     * Create gallery image
     */
    public function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO gallery_images (title, image_path, alt_text, category, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'] ?? null,
            $data['image_path'],
            $data['alt_text'] ?? null,
            $data['category'] ?? 'general',
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Update gallery image
     */
    public function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE gallery_images 
            SET title = ?, image_path = ?, alt_text = ?, category = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'] ?? null,
            $data['image_path'],
            $data['alt_text'] ?? null,
            $data['category'] ?? 'general',
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            $id,
        ]);
    }

    /**
     * Delete gallery image
     */
    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
