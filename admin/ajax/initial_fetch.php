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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$csrf->validateToken($_POST['csrf_token'] ?? '')) {
    http_response_code(419);
    echo json_encode(['message' => 'رمز الحماية غير صالح']);
    exit;
}

$date = $_POST['date'] ?? date('Y-m-d');

try {
    $updateManager = new UpdateManager(
        $db,
        $container->get(ApiClient::class),
        $container->get(Logger::class)
    );
    $updateManager->updateDailyFixtures(new DateTimeImmutable($date));

    $fixtures = $db->fetchAll('SELECT COUNT(*) AS total FROM fixtures WHERE DATE(FROM_UNIXTIME(timestamp)) = :date', [
        'date' => $date,
    ]);
    $count = (int) ($fixtures[0]['total'] ?? 0);

    echo json_encode([
        'message' => 'تم سحب مباريات اليوم بنجاح',
        'date' => $date,
        'count' => $count,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
