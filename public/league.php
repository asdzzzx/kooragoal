<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;

$db = $container->get(Database::class);
$leagueId = (int) ($_GET['id'] ?? 0);
$league = $db->fetch('SELECT * FROM leagues WHERE id = :id', ['id' => $leagueId]);

if (!$league) {
    http_response_code(404);
    echo 'الدوري غير موجود';
    exit;
}

$fixtures = $db->fetchAll('SELECT f.*, th.name as home_name, ta.name as away_name FROM fixtures f
    JOIN teams th ON th.id = f.home_team_id
    JOIN teams ta ON ta.id = f.away_team_id
    WHERE f.league_id = :league ORDER BY f.timestamp DESC LIMIT 50', ['league' => $leagueId]);

$pageTitle = 'دوري ' . $league['name'];
include __DIR__ . '/includes/site-header.php';
?>
<h1 class="mb-3">دوري <?= htmlspecialchars($league['name']) ?></h1>
<div class="list-group">
<?php foreach ($fixtures as $fixture): ?>
    <a class="list-group-item list-group-item-action" href="/match/<?= (int) $fixture['id'] ?>">
        <?= htmlspecialchars($fixture['home_name']) ?> ضد <?= htmlspecialchars($fixture['away_name']) ?>
        <span class="badge bg-secondary"><?= htmlspecialchars($fixture['status_short']) ?></span>
    </a>
<?php endforeach; ?>
<?php if (!$fixtures): ?>
    <div class="list-group-item">لا توجد مباريات متاحة.</div>
<?php endif; ?>
</div>
<?php include __DIR__ . '/includes/site-footer.php';
