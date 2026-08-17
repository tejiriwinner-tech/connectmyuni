<?php

/**
 * Connect MyUni — AI Content Request Repository
 *
 * Interacts with the ai_content_requests database table.
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Repositories;

use ConnectMyUni\Database;
use PDO;

class AiContentRequestRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        // Production path: the canonical connection singleton. Tests may inject
        // a throwaway PDO (e.g. in-memory SQLite) instead — the statements here
        // are standard SQL with no dialect-specific constructs.
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Create a new generation request record
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO ai_content_requests
                (admin_user_id, content_type, prompt, generated_content, status, provider, model)
                VALUES (:admin_user_id, :content_type, :prompt, :generated_content, :status, :provider, :model)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':admin_user_id'     => $data['admin_user_id'] ?? null,
            ':content_type'      => $data['content_type'],
            ':prompt'            => is_array($data['prompt']) ? json_encode($data['prompt']) : $data['prompt'],
            ':generated_content' => is_array($data['generated_content']) ? json_encode($data['generated_content']) : $data['generated_content'],
            ':status'           => $data['status'] ?? 'generated',
            ':provider'         => $data['provider'] ?? null,
            ':model'            => $data['model'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Get request by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM ai_content_requests WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
 * Update status and approved content
 */
public function updateApproval(int $id, string $status, ?array $approvedContent = null): bool
{
    $sql = "UPDATE ai_content_requests
            SET status = :status_set,
                approved_content = :approved_content,
                approved_at = CASE
                    WHEN :status_check = 'approved'
                    THEN CURRENT_TIMESTAMP
                    ELSE approved_at
                END
            WHERE id = :id";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        ':id'               => $id,
        ':status_set'       => $status,
        ':status_check'     => $status,
        ':approved_content' => $approvedContent !== null
            ? json_encode($approvedContent)
            : null,
    ]);
}

    /**
     * Get recent generation logs for admin.
     *
     * When an admin user id is supplied, only that administrator's own
     * requests are returned (private-by-owner history isolation).
     */
    public function getRecent(int $limit = 10, ?int $adminUserId = null): array
    {
        $sql = "SELECT r.*, u.username
                FROM ai_content_requests r
                LEFT JOIN admin_users u ON r.admin_user_id = u.id";
        if ($adminUserId !== null && $adminUserId > 0) {
            $sql .= " WHERE r.admin_user_id = :admin_user_id";
        }
        $sql .= " ORDER BY r.created_at DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if ($adminUserId !== null && $adminUserId > 0) {
            $stmt->bindValue(':admin_user_id', $adminUserId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
