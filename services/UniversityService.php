<?php

/**
 * Connect MyUni — University Service
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Services;

use ConnectMyUni\Repositories\UniversityRepository;

/**
 * Class UniversityService
 */
class UniversityService
{
    private UniversityRepository $universityRepo;

    public function __construct()
    {
        $this->universityRepo = new UniversityRepository();
    }

    /**
     * Get all universities
     */
    public function getAll(): array
    {
        return $this->universityRepo->getAll();
    }

    /**
     * Get featured universities
     */
    public function getFeatured(): array
    {
        return $this->universityRepo->getFeatured();
    }

    /**
     * Get university by ID
     */
    public function getById(int $id): ?array
    {
        return $this->universityRepo->findById($id);
    }

    /**
     * Get universities by country
     */
    public function getByCountry(int $countryId): array
    {
        return $this->universityRepo->getByCountry($countryId);
    }

    /**
     * Create new university
     */
    public function create(array $data): int
    {
        if (empty($data['slug'])) {
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        }
        
        return $this->universityRepo->create($data);
    }

    /**
     * Update university
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data['slug'])) {
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        }
        
        return $this->universityRepo->update($id, $data);
    }

    /**
     * Delete university
     */
    public function delete(int $id): bool
    {
        return $this->universityRepo->delete($id);
    }
}
