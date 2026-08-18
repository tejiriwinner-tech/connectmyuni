<?php

/**
 * Connect MyUni — Country Service
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Services;

use ConnectMyUni\Repositories\CountryRepository;

/**
 * Class CountryService
 */
class CountryService
{
    private CountryRepository $countryRepo;

    public function __construct()
    {
        $this->countryRepo = new CountryRepository();
    }

    /**
     * Get all countries
     */
    public function getAll(): array
    {
        return $this->countryRepo->getAll();
    }

    /**
     * Get all countries with a count of universities per country
     */
    public function getAllWithUniversityCounts(): array
    {
        return $this->countryRepo->getAllWithUniversityCounts();
    }

    /**
     * Count universities that reference a country
     */
    public function countUniversities(int $countryId): int
    {
        return $this->countryRepo->countUniversities($countryId);
    }

    /**
     * Convert an ISO 3166-1 alpha-2 country code into a regional-indicator flag emoji.
     */
    public static function flagEmoji(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        if (strlen($code) !== 2 || !ctype_alpha($code)) {
            return '🌍';
        }
        return mb_chr(0x1F1E6 + ord($code[0]) - 0x41, 'UTF-8')
             . mb_chr(0x1F1E6 + ord($code[1]) - 0x41, 'UTF-8');
    }

    /**
     * Get featured countries
     */
    public function getFeatured(): array
    {
        return $this->countryRepo->getFeatured();
    }

    /**
     * Get country by ID
     */
    public function getById(int $id): ?array
    {
        return $this->countryRepo->findById($id);
    }

    /**
     * Get country by slug
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->countryRepo->findBySlug($slug);
    }

    /**
     * Create new country
     */
    public function create(array $data): int
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        }
        
        return $this->countryRepo->create($data);
    }

    /**
     * Update country
     */
    public function update(int $id, array $data): bool
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        }
        
        return $this->countryRepo->update($id, $data);
    }

    /**
     * Delete country
     */
    public function delete(int $id): bool
    {
        return $this->countryRepo->delete($id);
    }
}
