<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;

/** @var Kooragoal\Services\Container $container */
$db = $container->get(Database::class);
$pageTitle = 'المباريات الجارية';

$statuses = ['1H','2H','ET','P','BT','LIVE'];
$placeholders = implode(',', array_fill(0, count($statuses), '?'));
$fixtures = $db->fetchAll(
    "SELECT f.*, th.name AS home_name, ta.name AS away_name, l.name AS league_name
     FROM fixtures f
     JOIN teams th ON th.id = f.home_team_id
     JOIN teams ta ON ta.id = f.away_team_id
     JOIN leagues l ON l.id = f.league_id
     WHERE f.status_short IN ($placeholders)
     ORDER BY f.timestamp DESC",
    $statuses
);

include __DIR__ . '/includes/site-header.php';
?>
<h1 class="mb-4">المباريات الجارية الآن</h1>
<div class="row g-3">
    <?php foreach ($fixtures as $fixture): ?>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?= htmlspecialchars($fixture['league_name']) ?></h5>
                    <p class="card-text d-flex justify-content-between align-items-center">
                        <span><?= htmlspecialchars($fixture['home_name']) ?></span>
                        <strong><?= (int) $fixture['goals_home'] ?> - <?= (int) $fixture['goals_away'] ?></strong>
                        <span><?= htmlspecialchars($fixture['away_name']) ?></span>
                    </p>
                    <p class="text-muted mb-0">الحالة: <?= htmlspecialchars($fixture['status_long']) ?></p>
                    <a href="/match/<?= (int) $fixture['id'] ?>" class="btn btn-outline-primary btn-sm mt-3">تفاصيل المباراة</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$fixtures): ?>
        <div class="col-12">
            <div class="alert alert-info">لا توجد مباريات جارية في الوقت الحالي.</div>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/site-footer.php';
