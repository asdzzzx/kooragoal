<?php
require_once __DIR__ . '/includes/bootstrap.php';

use Kooragoal\Services\Database;

\$db = \$container->get(Database::class);
\$leagueId = (int) (\$_GET['league'] ?? 0);
\$season = (int) (\$_GET['season'] ?? date('Y'));
\$scorers = \$db->fetchAll('SELECT s.*, p.name as player_name, t.name as team_name FROM scorers s
    JOIN players p ON p.id = s.player_id
    JOIN teams t ON t.id = s.team_id
    WHERE s.league_id = :league AND s.season = :season ORDER BY s.goals DESC', [
    'league' => \$leagueId,
    'season' => \$season,
]);
\$pageTitle = 'قائمة الهدافين';
include __DIR__ . '/includes/site-header.php';
?>
<h1 class="mb-3">قائمة الهدافين</h1>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>اللاعب</th>
                <th>الفريق</th>
                <th>الأهداف</th>
                <th>التمريرات الحاسمة</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (\$scorers as \$row): ?>
            <tr>
                <td><?= htmlspecialchars(\$row['player_name']) ?></td>
                <td><?= htmlspecialchars(\$row['team_name']) ?></td>
                <td><?= (int) \$row['goals'] ?></td>
                <td><?= (int) \$row['assists'] ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!\$scorers): ?>
            <tr><td colspan="4">لا توجد بيانات متاحة.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/includes/site-footer.php';
