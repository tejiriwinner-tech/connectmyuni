<?php

/**
 * Connect MyUni — Event Service
 *
 * Business logic layer for events.
 * Coordinates between controllers and repositories.
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Services;

use ConnectMyUni\Repositories\EventRepository;
use ConnectMyUni\Helpers\MediaResolver;

/**
 * Class EventService
 */
class EventService
{
    private EventRepository $eventRepo;

    public function __construct()
    {
        $this->eventRepo = new EventRepository();
    }

    /**
     * Get all published events sorted by date
     */
    public function getAllEvents(): array
    {
        return $this->eventRepo->getAllPublished();
    }

    /**
     * Get all events (admin - includes drafts, latest first)
     */
    public function getAll(): array
    {
        return $this->eventRepo->getAll();
    }

    /**
     * Get single event by ID or slug
     */
    public function getEvent(string $identifier): ?array
    {
        // Try by ID first
        if (is_numeric($identifier)) {
            return $this->eventRepo->findById((int) $identifier);
        }

        // Try by slug
        return $this->eventRepo->findBySlug($identifier);
    }

    /**
     * Get upcoming events
     */
    public function getUpcomingEvents(int $limit = 6): array
    {
        return $this->eventRepo->getUpcoming($limit);
    }

    /**
     * Map a repository row to the keys the public templates expect.
     * (Repository: image_path / event_date  ->  Template: image / date)
     */
    public function present(array $event): array
    {
        $imageKey = $event['image_path'] ?? $event['image'] ?? '';
        return [
            'id'          => $event['id'] ?? null,
            'title'       => $event['title'] ?? '',
            'slug'        => $event['slug'] ?? '',
            'category'    => $event['category'] ?? 'announcement',
            'date'        => $event['event_date'] ?? ($event['date'] ?? ''),
            'event_time'  => $event['event_time'] ?? '',
            'description' => $event['description'] ?? '',
            'details'     => $event['details'] ?? null,
            'image'       => MediaResolver::url((string) $imageKey),
            'image_path'  => $event['image_path'] ?? null,
            'registration_link' => $event['registration_link'] ?? null,
            'location'    => $event['location'] ?? null,
            'duration'    => $event['duration'] ?? null,
            'capacity'    => $event['capacity'] ?? null,
            'requirements' => $event['requirements'] ?? null,
            'contact_info' => $event['contact_info'] ?? null,
            'status'      => $event['status'] ?? 'published',
        ];
    }

    /**
     * Published events mapped to public template keys.
     */
    public function getAllForPublic(): array
    {
        return array_map([$this, 'present'], $this->eventRepo->getAllPublished());
    }

    /**
     * A single event mapped to public template keys.
     */
    public function getForPublic(string $identifier): ?array
    {
        $event = $this->getEvent($identifier);
        return $event ? $this->present($event) : null;
    }

    /**
     * Get events by category
     */
    public function getEventsByCategory(string $category): array
    {
        return $this->eventRepo->getByCategory($category);
    }

    /**
     * Create new event
     */
    public function createEvent(array $data): int
    {
        // Validate required fields
        $this->validateEventData($data);

        // Generate slug from title
        $slug = $this->generateSlug($data['title']);

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while ($this->eventRepo->findBySlug($slug) !== null) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $this->eventRepo->create([
            'title' => $data['title'],
            'slug' => $slug,
            'category' => $data['category'],
            'event_date' => $data['date'],
            'event_time' => $data['event_time'] ?? null,
            'description' => $data['description'],
            'details' => $data['details'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'registration_link' => $data['registration_link'] ?? null,
            'location' => $data['location'] ?? null,
            'duration' => $data['duration'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'contact_info' => $data['contact_info'] ?? null,
            'status' => 'published',
        ]);
    }

    /**
     * Update event
     */
    public function updateEvent(int $id, array $data): bool
    {
        $this->validateEventData($data, $id);

        $event = $this->eventRepo->findById($id);
        if (!$event) {
            throw new \InvalidArgumentException("Event not found");
        }

        // Preserve the existing image unless a new path was supplied.
        if (!array_key_exists('image_path', $data)) {
            $data['image_path'] = $event['image_path'] ?? null;
        }

        // Update slug if title changed
        if ($data['title'] !== $event['title']) {
            $slug = $this->generateSlug($data['title']);
            $originalSlug = $slug;
            $counter = 1;
            while ($this->eventRepo->findBySlug($slug) !== null && $this->eventRepo->findBySlug($slug)['id'] !== $id) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        } else {
            $data['slug'] = $event['slug'];
        }

        // Normalise template keys to repository keys before persisting.
        $data['event_date'] = $data['date'] ?? null;
        $data['status']     = $data['status'] ?? $event['status'] ?? 'published';

        return $this->eventRepo->update($id, $data);
    }

    /**
     * Delete event
     */
    public function deleteEvent(int $id): bool
    {
        $event = $this->eventRepo->findById($id);
        if (!$event) {
            throw new \InvalidArgumentException("Event not found");
        }

        return $this->eventRepo->delete($id);
    }

    /**
     * Record event view
     */
    public function recordView(int $id): void
    {
        $this->eventRepo->incrementViews($id);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'total' => $this->eventRepo->countAll(),
            'webinars' => $this->eventRepo->countByCategory('webinar'),
            'workshops' => $this->eventRepo->countByCategory('workshop'),
            'videos' => $this->eventRepo->countByCategory('video'),
            'announcements' => $this->eventRepo->countByCategory('announcement'),
            'recent' => $this->eventRepo->getRecent(5),
        ];
    }

    /**
     * Validate event data
     */
    private function validateEventData(array $data, ?int $existingId = null): void
    {
        $required = ['title', 'category', 'date', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Field '$field' is required");
            }
        }

        $validCategories = ['webinar', 'workshop', 'announcement', 'video'];
        if (!in_array($data['category'], $validCategories)) {
            throw new \InvalidArgumentException("Invalid category");
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
            throw new \InvalidArgumentException("Invalid date format. Use YYYY-MM-DD");
        }
    }

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('/-+/', '-', $slug);

        return $slug . '-' . date('Y-m-d-His');
    }
}
