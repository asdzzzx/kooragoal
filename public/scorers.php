<?php
require_once __DIR__ . '/includes/bootstrap.php';

use Kooragoal\Services\Database;

if (!function_exists('kooragoalRenderScorersRows')) {
    function kooragoalRenderScorersRows(array $scorers): void
    {
        if (!$scorers) {
            echo '<tr><td colspan="4">لا توجد بيانات متاحة.</td></tr>';
            return;
        }

        foreach ($scorers as $row) {
            $playerName = htmlspecialchars($row['player_name'] ?? '');
            $teamName = htmlspecialchars($row['team_name'] ?? '');
            $goals = (int) ($row['goals'] ?? 0);
            $assists = (int) ($row['assists'] ?? 0);

            echo '<tr>';
            echo '    <td>' . $playerName . '</td>';
            echo '    <td>' . $teamName . '</td>';
            echo '    <td>' . $goals . '</td>';
            echo '    <td>' . $assists . '</td>';
            echo '</tr>';
        }
    }
}

/** @var Kooragoal\Services\Container $container */
$db = $container->get(Database::class);
$leagueId = (int) ($_GET['league'] ?? 0);
$season = (int) ($_GET['season'] ?? date('Y'));

$scorers = $db->fetchAll(
    'SELECT s.*, p.name as player_name, t.name as team_name FROM scorers s
    JOIN players p ON p.id = s.player_id
    JOIN teams t ON t.id = s.team_id
    WHERE s.league_id = :league AND s.season = :season ORDER BY s.goals DESC',
    [
        'league' => $leagueId,
        'season' => $season,
    ]
);

if (isset($_GET['partial'])) {
    header('Content-Type: text/html; charset=UTF-8');
    kooragoalRenderScorersRows($scorers);
    return;
}

$pageTitle = 'قائمة الهدافين';
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
        <tbody id="scorersTableBody">
            <?php kooragoalRenderScorersRows($scorers); ?>
        </tbody>
    </table>
</div>

<script>
$(function(){
    const REFRESH_INTERVAL = 15000;
    const $tableBody = $('#scorersTableBody');
    const refreshUrl = (function(){
        const url = new URL(window.location.href);
        url.searchParams.set('partial', '1');
        return url.pathname + '?' + url.searchParams.toString();
    })();

    function refreshScorers(){
        $.get(refreshUrl)
            .done(function(html){
                $tableBody.html(html);
            })
            .fail(function(){
                $tableBody.html('<tr><td colspan="4">تعذر تحديث بيانات الهدافين.</td></tr>');
            });
    }

    refreshScorers();
    setInterval(refreshScorers, REFRESH_INTERVAL);
});
</script>
<?php include __DIR__ . '/includes/site-footer.php';
