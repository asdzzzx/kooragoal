<?php
require_once __DIR__ . '/includes/bootstrap.php';

use Kooragoal\Services\Database;

/** @var Kooragoal\Services\Container $container */
$db = $container->get(Database::class);
$fixtureId = (int) ($_GET['id'] ?? 0);

$fixture = $db->fetch('SELECT f.*, l.name as league_name, th.name as home_name, ta.name as away_name FROM fixtures f
    JOIN leagues l ON l.id = f.league_id
    JOIN teams th ON th.id = f.home_team_id
    JOIN teams ta ON ta.id = f.away_team_id
    WHERE f.id = :id', ['id' => $fixtureId]);

if (!$fixture) {
    http_response_code(404);
    echo 'المباراة غير موجودة';
    exit;
}

$pageTitle = 'مباراة ' . $fixture['home_name'] . ' ضد ' . $fixture['away_name'];
include __DIR__ . '/includes/site-header.php';
?>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h4 mb-3"><?= htmlspecialchars($fixture['league_name']) ?></h2>
                <div class="d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($fixture['home_name']) ?></span>
                    <strong class="fs-3"><?= (int) $fixture['goals_home'] ?> - <?= (int) $fixture['goals_away'] ?></strong>
                    <span><?= htmlspecialchars($fixture['away_name']) ?></span>
                </div>
                <p class="text-muted mt-3">الحالة: <?= htmlspecialchars($fixture['status_long']) ?> | التاريخ: <?= htmlspecialchars($fixture['date']) ?></p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">الإحصائيات</div>
            <div class="card-body" id="statsContainer">جار التحميل...</div>
        </div>

        <div class="card mb-3">
            <div class="card-header">الأحداث</div>
            <div class="card-body" id="eventsContainer">جار التحميل...</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">التشكيلة</div>
            <div class="card-body" id="lineupsContainer">جار التحميل...</div>
        </div>
    </div>
</div>

<script>
$(function(){
    function loadSection(url, container){
        $.get(url, function(html){
            $(container).html(html);
        }).fail(function(){
            $(container).html('<div class="alert alert-danger">تعذر تحميل البيانات</div>');
        });
    }

    function scheduleRefresh(){
        loadSection('/stats/<?= (int) $fixture['id'] ?>', '#statsContainer');
        loadSection('/events/<?= (int) $fixture['id'] ?>', '#eventsContainer');
        loadSection('/lineups.php?fixture=<?= (int) $fixture['id'] ?>', '#lineupsContainer');
    }

    scheduleRefresh();
    setInterval(scheduleRefresh, 15000);
});
</script>
<?php include __DIR__ . '/includes/site-footer.php';
