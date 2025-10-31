<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Scheduler;
use Kooragoal\Services\Database;
use Kooragoal\Services\Updaters\UpdateManager;
use Kooragoal\Services\Security\CsrfTokenManager;
use DateTimeImmutable;

header('Content-Type: application/json');

/** @var AuthManager \$auth */
if (!\$auth->check()) {
    http_response_code(401);
    echo json_encode(['message' => 'يجب تسجيل الدخول']);
    exit;
}

/** @var Scheduler \$scheduler */
/** @var Database \$db */
\$db = \$container->get(Database::class);
\$csrf = \$container->get(CsrfTokenManager::class);

if (!\$csrf->validateToken(\$_POST['csrf_token'] ?? '')) {
    http_response_code(419);
    echo json_encode(['message' => 'رمز الحماية غير صالح']);
    exit;
}

\$updateManager = new UpdateManager(\$db, \$container->get(Kooragoal\Services\ApiClient::class), \$container->get(Kooragoal\Services\Logger::class));

\$task = \$_POST['task'] ?? null;
\$context = \$_POST['context'] ?? null;
\$taskKey = \$task;

try {
    switch (\$task) {
        case 'fixtures_daily':
            \$updateManager->updateDailyFixtures(new DateTimeImmutable('now'));
            break;
        case 'fixtures_live':
            \$updateManager->updateLiveFixtures();
            break;
        case 'fixture_details':
            if (!\$context) {
                throw new InvalidArgumentException('يجب تحديد fixture=ID');
            }
            \$fixtureId = (int) filter_var(\$context, FILTER_SANITIZE_NUMBER_INT);
            \$updateManager->updateFixtureDetails(\$fixtureId);
            \$taskKey = sprintf('%s:%d', \$task, \$fixtureId);
            break;
        case 'fixture_lineups':
            if (!\$context) {
                throw new InvalidArgumentException('يجب تحديد fixture=ID');
            }
            \$fixtureId = (int) filter_var(\$context, FILTER_SANITIZE_NUMBER_INT);
            \$updateManager->updateLineups(\$fixtureId);
            \$taskKey = sprintf('%s:%d', \$task, \$fixtureId);
            break;
        case 'standings_scorers':
        case 'teams_players':
            if (!\$context || strpos(\$context, ':') === false) {
                throw new InvalidArgumentException('استخدم الصيغة leagueId:season');
            }
            [\$leagueId, \$season] = array_map('intval', explode(':', \$context));
            \$taskKey = sprintf('%s:%d-%d', \$task, \$leagueId, \$season);
            if (\$task === 'standings_scorers') {
                \$updateManager->updateStandingsAndScorers(\$leagueId, \$season);
            } else {
                \$updateManager->updateTeamsAndPlayers(\$leagueId, \$season);
            }
            break;
        default:
            throw new InvalidArgumentException('مهمة غير معروفة');
    }

    \$db->execute('INSERT INTO system_updates (task, last_run, status, message) VALUES (:task, NOW(), :status, :message) ON DUPLICATE KEY UPDATE last_run = NOW(), status = VALUES(status), message = VALUES(message)', [
        'task' => \$taskKey,
        'status' => 'success',
        'message' => 'Manual trigger',
    ]);

    echo json_encode(['message' => 'تم تنفيذ المهمة بنجاح']);
} catch (Throwable \$e) {
    http_response_code(422);
    echo json_encode(['message' => \$e->getMessage()]);
}
