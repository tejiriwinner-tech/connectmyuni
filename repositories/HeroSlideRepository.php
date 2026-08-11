<?php

/**
 * Connect MyUni — Hero Slide Repository
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Repositories;

use ConnectMyUni\Database;
use PDO;

/**
 * Class HeroSlideRepository
 */
class HeroSlideRepository
{
    /**
     * Get all active slides
     */
    public function getActive(int $limit = 5): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM hero_slides 
            WHERE is_active = 1 
            ORDER BY sort_order ASC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get all slides
     */
    public function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Find slide by ID
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM hero_slides WHERE id = ?");
        $stmt->execute([$id]);
        $slide = $stmt->fetch();
        return $slide ?: null;
    }

    /**
     * Create slide
     */
    public function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO hero_slides (title, subtitle, cta_text, cta_url, image_path, mobile_image_path, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['subtitle'] ?? null,
            $data['cta_text'] ?? null,
            $data['cta_url'] ?? null,
            $data['image_path'],
            $data['mobile_image_path'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Update slide
     */
    public function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE hero_slides 
            SET title = ?, subtitle = ?, cta_text = ?, cta_url = ?, 
                image_path = ?, mobile_image_path = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['subtitle'] ?? null,
            $data['cta_text'] ?? null,
            $data['cta_url'] ?? null,
            $data['image_path'],
            $data['mobile_image_path'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            $id,
        ]);
    }

    /**
     * Delete slide
     */
    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM hero_slides WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Reorder slides
     */
    public function reorder(array $orderedIds): bool
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        
        try {
            foreach ($orderedIds as $sortOrder => $id) {
                $stmt = $pdo->prepare("UPDATE hero_slides SET sort_order = ? WHERE id = ?");
                $stmt->execute([$sortOrder + 1, $id]);
            }
            
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
