<?php
declare(strict_types=1);

namespace ConnectMyUni\Services;

use ConnectMyUni\Repositories\ContactMessageRepository;

class ContactMessageService
{
    private ContactMessageRepository $messageRepo;

    public function __construct()
    {
        $this->messageRepo = new ContactMessageRepository();
    }

    /**--------------------------------------------------------------
     * Retrieval
     *--------------------------------------------------------------*/

    /**--------------------------------------------------------------
     * Get all enquiries ordered newest-first.
     *--------------------------------------------------------------*/
    public function getAll(int $limit = 100, int $offset = 0): array
    {
        return $this->messageRepo->getAll($limit, $offset);
    }

    /**--------------------------------------------------------------
     * Get a single enquiry by ID.
     *--------------------------------------------------------------*/
    public function findById(int $id): ?array
    {
        return $this->messageRepo->findById($id);
    }

    /**--------------------------------------------------------------
     * Total count of all enquiries.
     *--------------------------------------------------------------*/
    public function count(): int
    {
        return $this->messageRepo->count();
    }

    /**--------------------------------------------------------------
     * Count enquiries by status ('new', 'read', 'responded').
     *--------------------------------------------------------------*/
    public function countByStatus(string $status): int
    {
        return $this->messageRepo->countByStatus($status);
    }

    /**--------------------------------------------------------------
     * Enquiry workflow
     *--------------------------------------------------------------*/

    /**--------------------------------------------------------------
     * Persist a new enquiry from the public form.
     *--------------------------------------------------------------*/
    public function create(array $data): int
    {
        return $this->messageRepo->create($data);
    }

    /**--------------------------------------------------------------
     * Update the status of an enquiry.
     * Only known statuses are accepted.
     *--------------------------------------------------------------*/
    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['new', 'read', 'replied', 'closed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        return $this->messageRepo->updateStatus($id, $status);
    }

    /**--------------------------------------------------------------
     * Delete an enquiry.
     *--------------------------------------------------------------*/
    public function delete(int $id): bool
    {
        return $this->messageRepo->delete($id);
    }
}