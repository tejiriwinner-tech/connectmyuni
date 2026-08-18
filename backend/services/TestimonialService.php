<?php
declare(strict_types=1);

namespace ConnectMyUni\Services;

use ConnectMyUni\Repositories\TestimonialRepository;

class TestimonialService
{
    private TestimonialRepository $testimonialRepo;

    public function __construct()
    {
        $this->testimonialRepo = new TestimonialRepository();
    }

    /**--------------------------------------------------------------
     * Retrieval
     *--------------------------------------------------------------*/

    /**--------------------------------------------------------------
     * Get all active (featured) testimonials, limited.
     *--------------------------------------------------------------*/
    public function getActive(int $limit = 10): array
    {
        return $this->testimonialRepo->getActive($limit);
    }

    /**--------------------------------------------------------------
     * Get a single testimonial by ID.
     *--------------------------------------------------------------*/
    public function findById(int $id): ?array
    {
        return $this->testimonialRepo->findById($id);
    }

    /**--------------------------------------------------------------
     * Get all testimonials (active and inactive) for admin view.
     *--------------------------------------------------------------*/
    public function getAll(): array
    {
        return $this->testimonialRepo->getAll();
    }

    /**--------------------------------------------------------------
     * Count all testimonials.
     *--------------------------------------------------------------*/
    public function countAll(): int
    {
        $pdo = \ConnectMyUni\Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM testimonials");
        return (int) $stmt->fetchColumn();
    }

    /**--------------------------------------------------------------
     * Count only active (featured) testimonials.
     *--------------------------------------------------------------*/
    public function countActive(): int
    {
        $pdo = \ConnectMyUni\Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_featured = TRUE");
        return (int) $stmt->fetchColumn();
    }

    /**--------------------------------------------------------------
     * Count testimonials grouped by rating.
     *--------------------------------------------------------------*/
    public function countByRating(): array
    {
        $pdo = \ConnectMyUni\Database::getConnection();
        $stmt = $pdo->query("SELECT rating, COUNT(*) AS cnt FROM testimonials GROUP BY rating ORDER BY rating");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**--------------------------------------------------------------
     * Testimonial creation / upload.
     *--------------------------------------------------------------*/

    /**--------------------------------------------------------------
     * Store a new testimonial.
     * Relies on the repository for DB insertion; optional image
     * handling is managed via MediaService if an avatar image is
     * provided.
     *--------------------------------------------------------------*/
    public function create(array $data): int
    {
        return $this->testimonialRepo->create($data);
    }

    /**--------------------------------------------------------------
     * Update a testimonial's metadata.
     *--------------------------------------------------------------*/
    public function update(int $id, array $data): bool
    {
        return $this->testimonialRepo->update($id, $data);
    }

    /**--------------------------------------------------------------
     * Delete a testimonial.
     *--------------------------------------------------------------*/
    public function delete(int $id): array
    {
        $repo = new TestimonialRepository();
        $testimonial = $repo->findById($id);
        $dbOk = $repo->delete($id);
        $fileRemoved = true;
        if ($testimonial && $testimonial['image_path']) {
            $media = new \ConnectMyUni\Helpers\MediaService();
            $fileRemoved = $media->delete($testimonial['image_path']);
        }
        return ['deleted' => $dbOk && $fileRemoved, 'testimonial' => $testimonial];
    }

    /**--------------------------------------------------------------
     * Get testimonials specifically for public rendering.
     * Returns only featured/testimonials with resolved media URLs,
     * resepcted ordering, and safe fallbacks.
     *--------------------------------------------------------------*/
    public function getForPublic(int $limit = 10): array
    {
        $raw = $this->testimonialRepo->getActive($limit);
        $resolved = [];

        foreach ($raw as $row) {
            $imageUrl = \ConnectMyUni\Helpers\MediaResolver::url($row['image_path'] ?? '');
            $resolved[] = [
                'id'        => $row['id'],
                'name'      => htmlspecialchars($row['student_name'] ?? ''),
                'university' => htmlspecialchars($row['student_university'] ?? ''),
                'country'   => htmlspecialchars($row['student_country'] ?? ''),
                'text'      => htmlspecialchars($row['testimonial_text'] ?? ''),
                'rating'    => (int) ($row['rating'] ?? 5),
                'is_featured' => $row['is_featured'] ?? false,
                'image_url' => $imageUrl === '' ? null : $imageUrl,
            ];
        }

        return $resolved;
    }
}