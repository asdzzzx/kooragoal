<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'تفاصيل المباراة';
$activeMenu = 'match_details';

/** @var Database $db */
$db = $container->get(Database::class);

$fixtureId = (int) ($_GET['id'] ?? 0);
$fixture = null;
if ($fixtureId > 0) {
    $fixture = $db->fetch(
        'SELECT f.*, l.name AS league_name, th.name AS home_name, ta.name AS away_name
         FROM fixtures f
         JOIN leagues l ON l.id = f.league_id
         JOIN teams th ON th.id = f.home_team_id
         JOIN teams ta ON ta.id = f.away_team_id
         WHERE f.id = :id',
        ['id' => $fixtureId]
    );
}

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <h1 class="h3 mb-4">تفاصيل المباراة</h1>
    <form class="card card-body shadow-sm mb-4" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">معرف المباراة</label>
                <input type="number" name="id" class="form-control" value="<?= $fixtureId ?: '' ?>" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100">عرض التفاصيل</button>
            </div>
        </div>
    </form>

    <?php if ($fixture): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><?= htmlspecialchars($fixture['league_name']) ?></h5>
                <p class="mb-2"><strong><?= htmlspecialchars($fixture['home_name']) ?></strong> ضد <strong><?= htmlspecialchars($fixture['away_name']) ?></strong></p>
                <p class="mb-2">التاريخ: <?= htmlspecialchars($fixture['date']) ?> | الحكم: <?= htmlspecialchars($fixture['referee'] ?? '-') ?></p>
                <p class="mb-0">الحالة: <?= htmlspecialchars($fixture['status_long']) ?></p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">الإحصائيات</div>
                    <div class="card-body" id="statsContainer">
                        <div class="text-center text-muted">جارِ التحميل...</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">الأحداث</div>
                    <div class="card-body" id="eventsContainer">
                        <div class="text-center text-muted">جارِ التحميل...</div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($fixtureId): ?>
        <div class="alert alert-danger">تعذر العثور على مباراة بهذا المعرف.</div>
    <?php endif; ?>
</div>
</div>

<?php if ($fixture): ?>
<script>
(function($){
    function load(target, url){
        $(target).load(url, function(response, status){
            if(status !== 'success'){
                $(target).html('<div class="alert alert-danger">تعذر تحميل البيانات</div>');
            }
        });
    }
    load('#statsContainer', '/admin/match_stats.php?fixture=<?= (int) $fixture['id'] ?>');
    load('#eventsContainer', '/admin/match_events.php?fixture=<?= (int) $fixture['id'] ?>');
})(jQuery);
</script>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php';
