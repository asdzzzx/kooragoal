<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Security\CsrfTokenManager;

header('Content-Type: application/json');

/** @var AuthManager $auth */
$csrf = $container->get(CsrfTokenManager::class);
/** @var Database $db */
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
if ($fixtureId <= 0) {
    http_response_code(422);
    echo json_encode(['message' => 'معرف المباراة غير صالح']);
    exit;
}

$db->execute('DELETE FROM fixtures WHERE id = :id', ['id' => $fixtureId]);

echo json_encode(['message' => 'تم حذف المباراة بنجاح']);
