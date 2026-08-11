<?php

/**
 * Connect MyUni — Hero Slide Service
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Services;

use ConnectMyUni\Repositories\HeroSlideRepository;

/**
 * Class HeroSlideService
 */
class HeroSlideService
{
    private HeroSlideRepository $slideRepo;

    public function __construct()
    {
        $this->slideRepo = new HeroSlideRepository();
    }

    /**
     * Get active slides for public display
     */
    public function getActiveSlides(int $limit = 5): array
    {
        return $this->slideRepo->getActive($limit);
    }

    /**
     * Get all slides (admin)
     */
    public function getAll(): array
    {
        return $this->slideRepo->getAll();
    }

    /**
     * Get slide by ID
     */
    public function getById(int $id): ?array
    {
        return $this->slideRepo->findById($id);
    }

    /**
     * Create new slide
     */
    public function create(array $data): int
    {
        return $this->slideRepo->create($data);
    }

    /**
     * Update slide
     */
    public function update(int $id, array $data): bool
    {
        return $this->slideRepo->update($id, $data);
    }

    /**
     * Delete slide
     */
    public function delete(int $id): bool
    {
        return $this->slideRepo->delete($id);
    }

    /**
     * Reorder slides
     */
    public function reorder(array $orderedIds): bool
    {
        return $this->slideRepo->reorder($orderedIds);
    }
}
