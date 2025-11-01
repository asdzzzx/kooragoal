<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

use Kooragoal\Services\ApiClient;
use Kooragoal\Services\Database;
use Kooragoal\Services\Logger;
use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Updaters\UpdateManager;

header('Content-Type: application/json');

/** @var AuthManager $auth */
/** @var Database $db */
$db = $container->get(Database::class);

if (!$auth->check()) {
    http_response_code(401);
    echo json_encode(['message' => 'يجب تسجيل الدخول']);
    exit;
}

try {
    $updateManager = new UpdateManager(
        $db,
        $container->get(ApiClient::class),
        $container->get(Logger::class)
    );
    $updateManager->updateLiveFixtures();

    echo json_encode(['message' => 'تم تحديث المباريات الجارية بنجاح']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
