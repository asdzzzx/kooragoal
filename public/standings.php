<?php
require_once __DIR__ . '/includes/bootstrap.php';

use Kooragoal\Services\Database;

if (!function_exists('kooragoalRenderStandingsRows')) {
    function kooragoalRenderStandingsRows(array $standings): void
    {
        if (!$standings) {
            echo '<tr><td colspan="4">لا توجد بيانات متاحة.</td></tr>';
            return;
        }

        foreach ($standings as $row) {
            $rank = (int) ($row['rank_position'] ?? 0);
            $teamName = htmlspecialchars($row['team_name'] ?? '');
            $points = (int) ($row['points'] ?? 0);
            $goalDiff = (int) ($row['goals_diff'] ?? 0);

            echo '<tr>';
            echo '    <td>' . $rank . '</td>';
            echo '    <td>' . $teamName . '</td>';
            echo '    <td>' . $points . '</td>';
            echo '    <td>' . $goalDiff . '</td>';
            echo '</tr>';
        }
    }
}

/** @var Kooragoal\Services\Container $container */
$db = $container->get(Database::class);
$leagueId = (int) ($_GET['league'] ?? 0);
$season = (int) ($_GET['season'] ?? date('Y'));

$standings = $db->fetchAll(
    'SELECT s.*, t.name as team_name FROM standings s JOIN teams t ON t.id = s.team_id WHERE league_id = :league AND season = :season ORDER BY rank_position ASC',
    [
        'league' => $leagueId,
        'season' => $season,
    ]
);

if (isset($_GET['partial'])) {
    header('Content-Type: text/html; charset=UTF-8');
    kooragoalRenderStandingsRows($standings);
    return;
}

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
        <tbody id="standingsTableBody">
            <?php kooragoalRenderStandingsRows($standings); ?>
        </tbody>
    </table>
</div>

<script>
$(function(){
    const REFRESH_INTERVAL = 15000;
    const $tableBody = $('#standingsTableBody');
    const refreshUrl = (function(){
        const url = new URL(window.location.href);
        url.searchParams.set('partial', '1');
        return url.pathname + '?' + url.searchParams.toString();
    })();

    function refreshStandings(){
        $.get(refreshUrl)
            .done(function(html){
                $tableBody.html(html);
            })
            .fail(function(){
                $tableBody.html('<tr><td colspan="4">تعذر تحديث بيانات الترتيب.</td></tr>');
            });
    }

    refreshStandings();
    setInterval(refreshStandings, REFRESH_INTERVAL);
});
</script>
<?php include __DIR__ . '/includes/site-footer.php';
