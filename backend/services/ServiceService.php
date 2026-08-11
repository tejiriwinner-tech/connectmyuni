<?php

/**
 * Connect MyUni — Service Service
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Services;

use ConnectMyUni\Repositories\ServiceRepository;

/**
 * Class ServiceService
 */
class ServiceService
{
    private ServiceRepository $serviceRepo;

    public function __construct()
    {
        $this->serviceRepo = new ServiceRepository();
    }

    /**
     * Get all active services
     */
    public function getAllActive(): array
    {
        return $this->serviceRepo->getAllActive();
    }

    /**
     * Get all services (admin)
     */
    public function getAll(): array
    {
        return $this->serviceRepo->getAll();
    }

    /**
     * Get service by ID
     */
    public function getById(int $id): ?array
    {
        return $this->serviceRepo->findById($id);
    }

    /**
     * Get service by slug
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->serviceRepo->findBySlug($slug);
    }

    /**
     * Create new service
     */
    public function create(array $data): int
    {
        if (empty($data['slug'])) {
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
        }
        
        return $this->serviceRepo->create($data);
    }

    /**
     * Update service
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data['slug'])) {
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
        }
        
        return $this->serviceRepo->update($id, $data);
    }

    /**
     * Delete service
     */
    public function delete(int $id): bool
    {
        return $this->serviceRepo->delete($id);
    }
}
