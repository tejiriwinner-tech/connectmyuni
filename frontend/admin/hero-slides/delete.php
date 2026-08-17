<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\HeroSlideService;
use ConnectMyUni\Services\MediaService;

AuthMiddleware::requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['csrf_token'], $_POST['id'])
    && Security::verifyCsrfToken($_POST['csrf_token'])) {
    $id = (int) $_POST['id'];
    try {
        $service = new HeroSlideService();
        $slide   = $service->getById($id);
        if ($slide) {
            $service->delete($id);
            if (!empty($slide['image_path']))        (new MediaService())->delete($slide['image_path']);
            if (!empty($slide['mobile_image_path'])) (new MediaService())->delete($slide['mobile_image_path']);
        }
    } catch (\Throwable $e) {
        error_log('Hero slide delete failed: ' . $e->getMessage());
    }
}
header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/hero-slides/');
exit;

