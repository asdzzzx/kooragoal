<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'المباريات الجارية';
$activeMenu = 'live';

/** @var Database $db */
$db = $container->get(Database::class);

$liveStatuses = ['1H','2H','ET','P','BT','LIVE'];
$placeholders = implode(',', array_fill(0, count($liveStatuses), '?'));
$sql = "SELECT f.*, l.name AS league_name, th.name AS home_name, ta.name AS away_name
        FROM fixtures f
        JOIN leagues l ON l.id = f.league_id
        JOIN teams th ON th.id = f.home_team_id
        JOIN teams ta ON ta.id = f.away_team_id
        WHERE f.status_short IN ($placeholders)
        ORDER BY f.timestamp DESC";
$fixtures = $db->fetchAll($sql, $liveStatuses);

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">المباريات الجارية الآن</h1>
        <button class="btn btn-outline-primary" id="refreshLive">تحديث الآن</button>
    </div>

    <div class="row g-3">
        <?php foreach ($fixtures as $fixture): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2"><?= htmlspecialchars($fixture['league_name']) ?></h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($fixture['home_name']) ?></span>
                            <strong><?= (int) $fixture['goals_home'] ?> - <?= (int) $fixture['goals_away'] ?></strong>
                            <span><?= htmlspecialchars($fixture['away_name']) ?></span>
                        </div>
                        <p class="text-muted mt-2 mb-0">الحالة: <?= htmlspecialchars($fixture['status_long']) ?></p>
                        <a href="/match/<?= (int) $fixture['id'] ?>" class="btn btn-sm btn-outline-secondary mt-3" target="_blank">فتح في الموقع</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$fixtures): ?>
            <div class="col-12">
                <div class="alert alert-info">لا توجد مباريات مباشرة حالياً.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<script>
$('#refreshLive').on('click', function(){
    const btn = $(this).prop('disabled', true);
    $.post('/admin/ajax/update_live.php', function(resp){
        alert(resp.message || 'تم تحديث المباريات');
        location.reload();
    }).fail(function(xhr){
        alert(xhr.responseJSON?.message || 'تعذر التحديث');
    }).always(function(){ btn.prop('disabled', false); });
});
</script>
<?php include __DIR__ . '/includes/footer.php';
