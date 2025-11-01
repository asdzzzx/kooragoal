<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

use Kooragoal\Services\ApiClient;
use Kooragoal\Services\Database;
use Kooragoal\Services\Logger;
use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Security\CsrfTokenManager;
use Kooragoal\Services\Updaters\UpdateManager;

header('Content-Type: application/json');

/** @var AuthManager $auth */
/** @var Database $db */
$csrf = $container->get(CsrfTokenManager::class);
$db = $container->get(Database::class);

if (!$auth->check()) {
    http_response_code(401);
    echo json_encode(['message' => 'يجب تسجيل الدخول']);
    exit;
}

if (!$csrf->validateToken($_POST['csrf_token'] ?? '')) {
    http_response_code(419);
    echo json_encode(['message' => 'رمز الحماية غير صالح']);
    exit;
}

$fixtureId = (int) ($_POST['fixture_id'] ?? 0);
$withLineups = (bool) ($_POST['lineups'] ?? false);

try {
    if ($fixtureId <= 0) {
        throw new InvalidArgumentException('معرف المباراة غير صالح');
    }
    $updateManager = new UpdateManager(
        $db,
        $container->get(ApiClient::class),
        $container->get(Logger::class)
    );
    $updateManager->updateFixtureDetails($fixtureId);
    if ($withLineups) {
        $updateManager->updateLineups($fixtureId);
    }

    echo json_encode(['message' => 'تم تحديث المباراة بنجاح']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
