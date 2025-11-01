<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;

$db = $container->get(Database::class);
$leagueId = (int) ($_GET['league'] ?? 0);
$season = (int) ($_GET['season'] ?? date('Y'));
$standings = $db->fetchAll('SELECT s.*, t.name as team_name FROM standings s JOIN teams t ON t.id = s.team_id WHERE league_id = :league AND season = :season ORDER BY rank_position ASC', [
    'league' => $leagueId,
    'season' => $season,
]);
$pageTitle = 'جدول الترتيب';
include __DIR__ . '/includes/site-header.php';
?>
<h1 class="mb-3">جدول الترتيب</h1>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>المركز</th>
                <th>الفريق</th>
                <th>النقاط</th>
                <th>فارق الأهداف</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($standings as $row): ?>
            <tr>
                <td><?= (int) $row['rank_position'] ?></td>
                <td><?= htmlspecialchars($row['team_name']) ?></td>
                <td><?= (int) $row['points'] ?></td>
                <td><?= (int) $row['goals_diff'] ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$standings): ?>
            <tr><td colspan="4">لا توجد بيانات متاحة.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/includes/site-footer.php';
