<?php

/**
 * Connect MyUni — Testimonial Repository
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Repositories;

use ConnectMyUni\Database;
use PDO;

/**
 * Class TestimonialRepository
 */
class TestimonialRepository
{
    /**
     * Get all active testimonials
     */
    public function getActive(int $limit = 10): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM testimonials 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get all testimonials
     */
    public function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Find testimonial by ID
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        $testimonial = $stmt->fetch();
        return $testimonial ?: null;
    }

    /**
     * Create testimonial
     */
    public function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO testimonials (student_name, university, country, testimonial_text, avatar_path, rating, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['student_name'],
            $data['university'] ?? null,
            $data['country'] ?? null,
            $data['testimonial_text'],
            $data['avatar_path'] ?? null,
            $data['rating'] ?? 5,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Update testimonial
     */
    public function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE testimonials 
            SET student_name = ?, university = ?, country = ?, testimonial_text = ?, 
                avatar_path = ?, rating = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['student_name'],
            $data['university'] ?? null,
            $data['country'] ?? null,
            $data['testimonial_text'],
            $data['avatar_path'] ?? null,
            $data['rating'] ?? 5,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            $id,
        ]);
    }

    /**
     * Delete testimonial
     */
    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
