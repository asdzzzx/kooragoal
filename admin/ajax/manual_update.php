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

$task = $_POST['task'] ?? '';
$context = trim($_POST['context'] ?? '');

$updateManager = new UpdateManager(
    $db,
    $container->get(ApiClient::class),
    $container->get(Logger::class)
);

try {
    $taskKey = $task;
    switch ($task) {
        case 'fixtures_daily':
            $updateManager->updateDailyFixtures(new DateTimeImmutable('now'));
            break;
        case 'fixtures_live':
            $updateManager->updateLiveFixtures();
            break;
        case 'fixture_details':
            if ($context === '') {
                throw new InvalidArgumentException('يرجى تحديد معرف المباراة');
            }
            $fixtureId = (int) filter_var($context, FILTER_SANITIZE_NUMBER_INT);
            $updateManager->updateFixtureDetails($fixtureId);
            $taskKey = sprintf('%s:%d', $task, $fixtureId);
            break;
        case 'fixture_lineups':
            if ($context === '') {
                throw new InvalidArgumentException('يرجى تحديد معرف المباراة');
            }
            $fixtureId = (int) filter_var($context, FILTER_SANITIZE_NUMBER_INT);
            $updateManager->updateLineups($fixtureId);
            $taskKey = sprintf('%s:%d', $task, $fixtureId);
            break;
        case 'standings_scorers':
        case 'teams_players':
            if (strpos($context, ':') === false) {
                throw new InvalidArgumentException('استخدم الصيغة leagueId:season');
            }
            [$leagueId, $season] = array_map('intval', explode(':', $context));
            $taskKey = sprintf('%s:%d-%d', $task, $leagueId, $season);
            if ($task === 'standings_scorers') {
                $updateManager->updateStandingsAndScorers($leagueId, $season);
            } else {
                $updateManager->updateTeamsAndPlayers($leagueId, $season);
            }
            break;
        default:
            throw new InvalidArgumentException('مهمة غير معروفة');
    }

    $db->execute(
        'INSERT INTO system_updates (task, last_run, status, message) VALUES (:task, NOW(), :status, :message)
         ON DUPLICATE KEY UPDATE last_run = NOW(), status = VALUES(status), message = VALUES(message)',
        [
            'task' => $taskKey,
            'status' => 'success',
            'message' => 'manual trigger',
        ]
    );

    echo json_encode(['message' => 'تم تنفيذ المهمة بنجاح']);
} catch (Throwable $e) {
    $db->execute(
        'INSERT INTO system_updates (task, last_run, status, message) VALUES (:task, NOW(), :status, :message)
         ON DUPLICATE KEY UPDATE last_run = NOW(), status = VALUES(status), message = VALUES(message)',
        [
            'task' => $task ?: 'manual',
            'status' => 'failed',
            'message' => $e->getMessage(),
        ]
    );
    http_response_code(422);
    echo json_encode(['message' => $e->getMessage()]);
}
