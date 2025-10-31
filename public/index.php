<?php
require_once __DIR__ . '/includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Updaters\UpdateManager;
use DateTimeImmutable;

\$db = \$container->get(Database::class);
\$pageTitle = 'مباريات اليوم';

\$fixtures = \$db->fetchAll('SELECT f.*, th.name as home_name, ta.name as away_name, l.name as league_name FROM fixtures f
    JOIN teams th ON th.id = f.home_team_id
    JOIN teams ta ON ta.id = f.away_team_id
    JOIN leagues l ON l.id = f.league_id
    WHERE DATE(FROM_UNIXTIME(f.timestamp)) = CURDATE()
    ORDER BY f.timestamp ASC');

\$lastDaily = \$db->fetch('SELECT last_run FROM system_updates WHERE task = :task', ['task' => 'fixtures_daily']);
\$needsRefresh = !\$fixtures || !\$lastDaily || (time() - strtotime(\$lastDaily['last_run'])) > 86400;

if (\$needsRefresh) {
    \$updateManager = new UpdateManager(\$db, \$container->get(Kooragoal\Services\ApiClient::class), \$container->get(Kooragoal\Services\Logger::class));
    \$updateManager->updateDailyFixtures(new DateTimeImmutable('now'));
    \$fixtures = \$db->fetchAll('SELECT f.*, th.name as home_name, ta.name as away_name, l.name as league_name FROM fixtures f
        JOIN teams th ON th.id = f.home_team_id
        JOIN teams ta ON ta.id = f.away_team_id
        JOIN leagues l ON l.id = f.league_id
        WHERE DATE(FROM_UNIXTIME(f.timestamp)) = CURDATE()
        ORDER BY f.timestamp ASC');
}

include __DIR__ . '/includes/site-header.php';
?>
<h1 class="mb-4">مباريات اليوم</h1>
<div class="row g-3">
<?php foreach (\$fixtures as \$fixture): ?>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars(\$fixture['league_name']) ?></h5>
                <p class="card-text">
                    <?= htmlspecialchars(\$fixture['home_name']) ?>
                    <strong class="mx-2"><?= (int) \$fixture['goals_home'] ?> - <?= (int) \$fixture['goals_away'] ?></strong>
                    <?= htmlspecialchars(\$fixture['away_name']) ?>
                </p>
                <p class="card-text text-muted">الحالة: <?= htmlspecialchars(\$fixture['status_long']) ?></p>
                <a href="/match/<?= (int) \$fixture['id'] ?>" class="btn btn-outline-primary btn-sm">التفاصيل</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php if (!\$fixtures): ?>
    <div class="col-12">
        <div class="alert alert-info">لا توجد مباريات مجدولة لليوم.</div>
    </div>
<?php endif; ?>
</div>
<?php include __DIR__ . '/includes/site-footer.php';
