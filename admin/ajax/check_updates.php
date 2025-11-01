<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

header('Content-Type: application/json');

/** @var AuthManager $auth */
/** @var Database $db */
$db = $container->get(Database::class);

if (!$auth->check()) {
    http_response_code(401);
    echo json_encode(['message' => 'يجب تسجيل الدخول']);
    exit;
}

$updates = $db->fetchAll('SELECT task, status, last_run, message FROM system_updates ORDER BY last_run DESC LIMIT 50');

echo json_encode(['updates' => $updates]);
