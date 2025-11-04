<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/fixtures-functions.php';

use Kooragoal\Services\Database;

/** @var Kooragoal\Services\Container $container */
$db = $container->get(Database::class);
$leagueId = (int) ($_GET['id'] ?? 0);
$league = $db->fetch('SELECT * FROM leagues WHERE id = :id', ['id' => $leagueId]);

if (!$league) {
    http_response_code(404);
    echo 'الدوري غير موجود';
    exit;
}

$fixtures = kooragoalFetchLeagueFixtures($db, $leagueId);

$pageTitle = 'دوري ' . $league['name'];
include __DIR__ . '/includes/site-header.php';
?>
<h1 class="mb-3">دوري <?= htmlspecialchars($league['name']) ?></h1>
<div class="list-group" id="leagueFixturesContainer">
    <?php kooragoalRenderLeagueFixturesList($fixtures); ?>
</div>

<script>
$(function(){
    const REFRESH_INTERVAL = 15000;
    const $container = $('#leagueFixturesContainer');
    const refreshUrl = '/partials/league-fixtures.php?league=<?= (int) $leagueId ?>';

    function refreshFixtures(){
        $.get(refreshUrl)
            .done(function(html){
                $container.html(html);
            })
            .fail(function(){
                $container.html('<div class="list-group-item list-group-item-danger">تعذر تحديث المباريات الخاصة بالدوري.</div>');
            });
    }

    refreshFixtures();
    setInterval(refreshFixtures, REFRESH_INTERVAL);
});
</script>
<?php include __DIR__ . '/includes/site-footer.php';
