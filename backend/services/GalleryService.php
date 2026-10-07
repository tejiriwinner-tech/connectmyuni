<?php
declare(strict_types=1);

namespace ConnectMyUni\Services;

use ConnectMyUni\Repositories\GalleryRepository;
use PDO;

class GalleryService
{
    private GalleryRepository $galleryRepo;

    public function __construct()
    {
        $this->galleryRepo = new GalleryRepository();
    }

    public function getActive(int $limit = 20): array
    {
        return $this->galleryRepo->getActive($limit);
    }

    public function findById(int $id): ?array
    {
        return $this->galleryRepo->findById($id);
    }

    public function getAll(): array
    {
        return $this->galleryRepo->getAll();
    }

    public function countAll(): int
    {
        $pdo = \ConnectMyUni\Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images");
        return (int) $stmt->fetchColumn();
    }

    public function countActive(): int
    {
        $pdo = \ConnectMyUni\Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images WHERE is_active = TRUE");
        return (int) $stmt->fetchColumn();
    }

    public function countCategories(): array
    {
        $pdo = \ConnectMyUni\Database::getConnection();
        $stmt = $pdo->query("SELECT category, COUNT(*) AS cnt FROM gallery_images GROUP BY category ORDER BY category");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function storeImage(array $file, string $category = 'gallery'): array
    {
        $media = new \ConnectMyUni\Helpers\MediaService();
        return $media->store($file, $category);
    }

    public function createImage(array $data): int
    {
        return $this->galleryRepo->create($data);
    }

    public function updateMetadata(int $id, array $data): bool
    {
        $pdo = \ConnectMyUni\Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE gallery_images SET title = ?, alt_text = ?, category = ?, is_active = ? WHERE id = ?"
        );
        return $stmt->execute([
            $data['title'] ?? null,
            $data['alt_text'] ?? null,
            $data['category'] ?? 'general',
            ($data['is_active'] ?? 0) === 1 ? 1 : 0,
            $id,
        ]);
    }

    public function deleteImage(int $id): array
    {
        $media = new \ConnectMyUni\Helpers\MediaService();
        $image = $this->galleryRepo->findById($id);
        $dbOk = $this->galleryRepo->delete($id);
        $fileRemoved = true;
        if ($image && $image['image_path']) {
            $fileRemoved = $media->delete($image['image_path']);
        }
        return ['deleted' => $dbOk && $fileRemoved, 'image' => $image];
    }

    public function getForPublic(int $limit = 20): array
    {
        $raw = $this->galleryRepo->getActive($limit);
        $resolved = [];
        foreach ($raw as $row) {
            $url = \ConnectMyUni\Helpers\MediaResolver::url($row['image_path'] ?? '');
            if ($url === '') { continue; }
            $resolved[] = [
                'id'        => $row['id'],
                'file'      => $url,
                'caption'   => $row['alt_text'] ?? $row['title'] ?? '',
                'category'  => $row['category'] ?? 'general',
                'label'     => ucfirst($row['category'] ?? 'General'),
            ];
        }
        return $resolved;
    }
}