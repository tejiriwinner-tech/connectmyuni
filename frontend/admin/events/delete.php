<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\EventService;
use ConnectMyUni\Services\MediaService;

AuthMiddleware::requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['csrf_token'], $_POST['id'])
    && Security::verifyCsrfToken($_POST['csrf_token'])) {
    $id = (int) $_POST['id'];
    try {
        $service = new EventService();
        $event   = $service->getEvent((string) $id);
        if ($event) {
            $service->deleteEvent($id);
            if (!empty($event['image_path'])) {
                (new MediaService())->delete($event['image_path']);
            }
        }
    } catch (\Throwable $e) {
        error_log('Event delete failed: ' . $e->getMessage());
    }
}
header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/events/');
exit;

