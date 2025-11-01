<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
if (!$auth->check()) {
    http_response_code(401);
    exit('غير مصرح');
}

/** @var Database $db */
$db = $container->get(Database::class);
$fixtureId = (int) ($_GET['fixture'] ?? 0);
$events = [];
if ($fixtureId > 0) {
    $events = $db->fetchAll(
        'SELECT e.*, t.name AS team_name
         FROM events e JOIN teams t ON t.id = e.team_id
         WHERE e.fixture_id = :fixture ORDER BY e.time_elapsed ASC',
        ['fixture' => $fixtureId]
    );
}
?>
<div class="timeline">
    <?php foreach ($events as $event): ?>
        <div class="border-bottom py-2">
            <strong class="me-2"><?= htmlspecialchars($event['time_elapsed']) ?>'</strong>
            <span class="badge bg-secondary me-2"><?= htmlspecialchars($event['type']) ?></span>
            <span><?= htmlspecialchars($event['detail']) ?></span>
            <small class="text-muted d-block">الفريق: <?= htmlspecialchars($event['team_name']) ?></small>
            <?php if (!empty($event['comments'])): ?>
                <small class="text-muted">ملاحظات: <?= htmlspecialchars($event['comments']) ?></small>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (!$events): ?>
        <div class="text-muted text-center">لا توجد أحداث مسجلة.</div>
    <?php endif; ?>
</div>
