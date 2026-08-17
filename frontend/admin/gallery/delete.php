<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\GalleryRepository;
use ConnectMyUni\Services\MediaService;

AuthMiddleware::requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['csrf_token'], $_POST['id'])
    && Security::verifyCsrfToken($_POST['csrf_token'])) {
    $id = (int) $_POST['id'];
    try {
        $repo = new GalleryRepository();
        $img  = $repo->findById($id);
        if ($img) {
            $repo->delete($id);
            if (!empty($img['image_path'])) {
                (new MediaService())->delete($img['image_path']);
            }
        }
    } catch (\Throwable $e) {
        error_log('Gallery delete failed: ' . $e->getMessage());
    }
}
header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/gallery/');
exit;