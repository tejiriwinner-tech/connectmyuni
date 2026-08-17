<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\UniversityService;
use ConnectMyUni\Services\MediaService;

AuthMiddleware::requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['csrf_token'], $_POST['id'])
    && Security::verifyCsrfToken($_POST['csrf_token'])) {
    $id = (int) $_POST['id'];
    try {
        $service = new UniversityService();
        $uni = $service->getById($id);
        if ($uni) {
            $service->delete($id);
            if (!empty($uni['logo_path'])) {
                (new MediaService())->delete($uni['logo_path']);
            }
        }
    } catch (\Throwable $e) {
        error_log('University delete failed: ' . $e->getMessage());
    }
}
header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/universities/');
exit;

