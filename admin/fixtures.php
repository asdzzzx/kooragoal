<?php
require_once __DIR__ . '/../includes/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\Security\AuthManager;
use Kooragoal\Services\Security\CsrfTokenManager;

/** @var AuthManager $auth */
require __DIR__ . '/includes/check_auth.php';

$pageTitle = 'إدارة المباريات';
$activeMenu = 'fixtures';

/** @var Database $db */
$db = $container->get(Database::class);
/** @var CsrfTokenManager $csrf */
$csrf = $container->get(CsrfTokenManager::class);

$leagueFilter = (int) ($_GET['league'] ?? 0);
$dateFilter = $_GET['date'] ?? '';

$sql = 'SELECT f.*, l.name AS league_name, th.name AS home_name, ta.name AS away_name
        FROM fixtures f
        JOIN leagues l ON l.id = f.league_id
        JOIN teams th ON th.id = f.home_team_id
        JOIN teams ta ON ta.id = f.away_team_id';
$params = [];
$conditions = [];

if ($leagueFilter > 0) {
    $conditions[] = 'f.league_id = :league';
    $params['league'] = $leagueFilter;
}
if ($dateFilter) {
    $conditions[] = 'DATE(FROM_UNIXTIME(f.timestamp)) = :date';
    $params['date'] = $dateFilter;
}
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY f.timestamp DESC LIMIT 100';

$fixtures = $db->fetchAll($sql, $params);
$leagues = $db->fetchAll('SELECT DISTINCT id, name FROM leagues ORDER BY name ASC');
$token = $csrf->getToken();

include __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';
echo '<div class="row">';
echo renderAdminSidebar($activeMenu);
?>
<div class="col-xl-10 col-lg-9">
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">إدارة المباريات</h1>
        <div>
            <button class="btn btn-outline-primary me-2" id="refreshLive">تحديث الجارية</button>
            <a class="btn btn-primary" href="/admin/updates.php">خيارات التحديث</a>
        </div>
    </div>

    <form class="card card-body shadow-sm mb-4" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">الدوري</label>
                <select class="form-select" name="league">
                    <option value="0">جميع الدوريات</option>
                    <?php foreach ($leagues as $league): ?>
                        <option value="<?= (int) $league['id'] ?>" <?= $leagueFilter === (int) $league['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($league['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">التاريخ</label>
                <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">تطبيق</button>
                <a href="/admin/fixtures.php" class="btn btn-light ms-2">إعادة تعيين</a>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0" id="fixturesTable">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الدوري</th>
                        <th>المباراة</th>
                        <th>النتيجة</th>
                        <th>الحالة</th>
                        <th>آخر تحديث</th>
                        <th>إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($fixtures as $fixture): ?>
                        <tr data-fixture="<?= (int) $fixture['id'] ?>">
                            <td><?= (int) $fixture['id'] ?></td>
                            <td><?= htmlspecialchars($fixture['league_name']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($fixture['home_name']) ?></strong>
                                <span class="text-muted">vs</span>
                                <strong><?= htmlspecialchars($fixture['away_name']) ?></strong>
                            </td>
                            <td><?= (int) $fixture['goals_home'] ?> - <?= (int) $fixture['goals_away'] ?></td>
                            <td><?= htmlspecialchars($fixture['status_long']) ?></td>
                            <td><?= htmlspecialchars($fixture['date']) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-primary me-1 update-fixture" data-lineups="0">تحديث</button>
                                <button class="btn btn-sm btn-outline-secondary me-1 update-fixture" data-lineups="1">+تشكيل</button>
                                <form class="d-inline" method="post" onsubmit="return confirm('هل تريد حذف المباراة؟');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
                                    <input type="hidden" name="fixture_id" value="<?= (int) $fixture['id'] ?>">
                                    <button type="submit" formaction="/admin/ajax/delete_match.php" class="btn btn-sm btn-outline-danger delete-fixture">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$fixtures): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">لا توجد مباريات مطابقة للمعايير المحددة.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<script>
(function($){
    $('#refreshLive').on('click', function(){
        const btn = $(this).prop('disabled', true);
        $.post('/admin/ajax/update_live.php', function(resp){
            alert(resp.message || 'تم التحديث');
            location.reload();
        }).fail(function(xhr){
            alert(xhr.responseJSON?.message || 'حدث خطأ أثناء التحديث');
        }).always(function(){
            btn.prop('disabled', false);
        });
    });

    $('.update-fixture').on('click', function(){
        const row = $(this).closest('tr');
        const fixtureId = row.data('fixture');
        const includeLineups = $(this).data('lineups');
        const data = {
            fixture_id: fixtureId,
            lineups: includeLineups,
            csrf_token: '<?= htmlspecialchars($token) ?>'
        };
        $.post('/admin/ajax/update_match.php', data, function(resp){
            alert(resp.message || 'تم تحديث المباراة');
        }).fail(function(xhr){
            alert(xhr.responseJSON?.message || 'حدث خطأ في التحديث');
        });
    });

    $('.delete-fixture').on('click', function(e){
        e.preventDefault();
        const form = $(this).closest('form');
        $.post(form.attr('formaction'), form.serialize(), function(resp){
            alert(resp.message || 'تم الحذف');
            form.closest('tr').fadeOut(function(){
                $(this).remove();
            });
        }).fail(function(xhr){
            alert(xhr.responseJSON?.message || 'حدث خطأ في الحذف');
        });
    });
})(jQuery);
</script>
<?php include __DIR__ . '/includes/footer.php';
