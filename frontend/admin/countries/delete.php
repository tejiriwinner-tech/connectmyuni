<?php
require_once __DIR__ . '/../../../backend/bootstrap.php';

use ConnectMyUni\Config\Security;
use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Services\CountryService;

AuthMiddleware::requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['csrf_token'], $_POST['id'])
    && Security::verifyCsrfToken($_POST['csrf_token'])) {
    $id = (int) $_POST['id'];
    try {
        $service = new CountryService();
        $country = $service->getById($id);
        if ($country) {
            // universities.country_id is ON DELETE CASCADE, so never delete a
            // country that is still referenced by universities.
            $uniCount = $service->countUniversities($id);
            if ($uniCount > 0) {
                $_SESSION['admin_flash'] = 'Cannot delete "' . $country['name'] . '": it still has '
                    . $uniCount . ' university record(s) referencing it.';
            } else {
                $service->delete($id);
            }
        }
    } catch (\Throwable $e) {
        error_log('Country delete failed: ' . $e->getMessage());
    }
}
header('Location: ' . CONNECTMYUNI_BASE_URL . 'frontend/admin/countries/');
exit;

